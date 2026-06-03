<?php

namespace App\Jobs;

use App\TenantInvoiceApiSync;
use App\Transaction;
use App\Utils\TenantInvoiceApiUtil;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

class SyncTenantInvoiceDetail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 10;

    public function backoff()
    {
        return [60, 300, 900, 3600];
    }

    public function __construct(public int $transactionId)
    {
    }

    public function handle(TenantInvoiceApiUtil $tenantInvoiceApiUtil)
    {
        if (!$tenantInvoiceApiUtil->isEnabled()) {
            return;
        }

        $transaction = Transaction::with([
            'sell_lines.product.unit',
            'sell_lines.sub_unit',
            'sell_lines.variations.product',
            'sell_lines.variations.product_variation',
        ])->find($this->transactionId);

        if (empty($transaction)) {
            return;
        }

        if (($transaction->type ?? null) !== 'sell') {
            return;
        }

        if (($transaction->status ?? null) !== 'final') {
            return;
        }

        if (!empty($transaction->is_suspend)) {
            return;
        }

        if (empty($transaction->invoice_no)) {
            return;
        }

        $sync = TenantInvoiceApiSync::firstOrNew(['transaction_id' => $transaction->id]);
        if (!empty($sync->sent_at)) {
            return;
        }

        if (empty($sync->idempotency_key)) {
            $sync->idempotency_key = (string) Str::uuid();
        }

        $payload = $tenantInvoiceApiUtil->buildInvoiceDetailPayload($transaction);
        $sync->payload = $payload;
        $sync->status = 'pending';
        $sync->attempts = (int) ($sync->attempts ?? 0) + 1;
        $sync->last_attempt_at = now();
        $sync->save();

        $result = $tenantInvoiceApiUtil->postInvoiceDetail($payload, $sync->idempotency_key);
        $sync->last_http_status = $result['http_status'];
        $sync->last_response_body = $result['response_body'];

        if ($result['ok']) {
            $sync->status = 'sent';
            $sync->sent_at = now();
            $sync->last_error = null;
            $sync->save();
            return;
        }

        $sync->status = 'failed';
        $sync->last_error = $result['error'];
        $sync->save();

        throw new \RuntimeException('Tenant invoice API sync failed: ' . ($result['error'] ?? 'unknown error'));
    }
}

