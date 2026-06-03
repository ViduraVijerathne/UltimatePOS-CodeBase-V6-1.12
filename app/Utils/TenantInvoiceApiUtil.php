<?php

namespace App\Utils;

use App\Transaction;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TenantInvoiceApiUtil
{
    public function isEnabled(): bool
    {
        return (bool) config('tenant_invoice_api.enabled');
    }

    public function getEndpointUrl(): ?string
    {
        $baseUrl = (string) config('tenant_invoice_api.base_url');
        $baseUrl = trim($baseUrl);
        if ($baseUrl === '') {
            return null;
        }

        return rtrim($baseUrl, '/') . '/';
    }

    public function buildInvoiceDetailPayload(Transaction $transaction): array
    {
        $posNo = (string) (config('tenant_invoice_api.pos_no') ?? $transaction->location_id);

        $netAmount = (float) ($transaction->final_total ?? 0);
        $discountAmount = (float) ($transaction->discount_amount ?? 0);
        $grossAmount = $netAmount + $discountAmount;

        $invoiceCreateDate = null;
        if (!empty($transaction->transaction_date)) {
            try {
                $invoiceCreateDate = \Carbon\Carbon::parse($transaction->transaction_date)->toIso8601String();
            } catch (\Throwable $e) {
                $invoiceCreateDate = null;
            }
        }

        $items = [];
        foreach (($transaction->sell_lines ?? []) as $sellLine) {
            if (!empty($sellLine->parent_sell_line_id)) {
                continue;
            }

            $qty = (float) ($sellLine->quantity ?? 0);
            $unitMrp = (float) ($sellLine->unit_price_before_discount ?? $sellLine->unit_price ?? 0);
            $discountPerUnit = (float) (method_exists($sellLine, 'get_discount_amount') ? $sellLine->get_discount_amount() : 0);
            $discountTotal = $discountPerUnit * $qty;

            $itemCode = $sellLine->variations->sub_sku ?? $sellLine->product->sku ?? null;
            $itemDescription = $sellLine->variations->full_name ?? $sellLine->product->name ?? null;
            $uom = $sellLine->sub_unit->short_name ?? $sellLine->product->unit->short_name ?? null;

            $items[] = [
                'ItemCode' => (string) ($itemCode ?? ''),
                'ItemDescription' => (string) ($itemDescription ?? ''),
                'UOM' => (string) ($uom ?? ''),
                'Qty' => $qty,
                'MRP' => $unitMrp,
                'Discount' => $discountTotal,
                'NetTotal' => ($unitMrp * $qty) - $discountTotal,
            ];
        }

        return [
            'InvoiceNo' => (string) ($transaction->invoice_no ?? ''),
            'POSNo' => $posNo,
            'GrossAmount' => $grossAmount,
            'DiscountAmount' => $discountAmount,
            'NetAmount' => $netAmount,
            'InvoiceCreateDate' => $invoiceCreateDate,
            'invoiceItems' => $items,
        ];
    }

    /**
     * @return array{ok: bool, http_status: int|null, response_body: string|null, error: string|null}
     */
    public function postInvoiceDetail(array $payload, ?string $idempotencyKey = null): array
    {
        $url = $this->getEndpointUrl();
        $apiKey = (string) config('tenant_invoice_api.api_key');
        $timeout = (int) config('tenant_invoice_api.timeout', 15);

        if (empty($url) || empty($apiKey)) {
            return [
                'ok' => false,
                'http_status' => null,
                'response_body' => null,
                'error' => 'Tenant invoice API is not configured (TENANT_INVOICE_API_BASE_URL / TENANT_INVOICE_API_KEY).',
            ];
        }

        $headers = [
            'X-APIKey' => $apiKey,
            'Accept' => 'application/json',
        ];
        if (!empty($idempotencyKey)) {
            $headers['Idempotency-Key'] = $idempotencyKey;
        }

        $startedAt = now();
        Log::channel('tenant_invoice_api')->info('tenant_invoice_api.request', [
            'time' => $startedAt->toIso8601String(),
            'method' => 'POST',
            'url' => $url,
            'headers' => array_diff_key($headers, ['X-APIKey' => true]),
            'payload' => $payload,
        ]);

        try {
            $response = Http::timeout($timeout)
                ->withHeaders($headers)
                ->asJson()
                ->post($url, $payload);

            $body = $response->body();
            $status = $response->status();

            Log::channel('tenant_invoice_api')->info('tenant_invoice_api.response', [
                'time' => now()->toIso8601String(),
                'elapsed_ms' => $startedAt->diffInMilliseconds(now()),
                'http_status' => $status,
                'response' => $body,
            ]);

            return [
                'ok' => $response->successful(),
                'http_status' => $status,
                'response_body' => $body,
                'error' => $response->successful() ? null : 'Non-2xx response',
            ];
        } catch (\Throwable $e) {
            Log::channel('tenant_invoice_api')->error('tenant_invoice_api.exception', [
                'time' => now()->toIso8601String(),
                'elapsed_ms' => $startedAt->diffInMilliseconds(now()),
                'error' => $e->getMessage(),
            ]);

            return [
                'ok' => false,
                'http_status' => null,
                'response_body' => null,
                'error' => $e->getMessage(),
            ];
        }
    }
}
