<?php

namespace App\Console\Commands;

use App\Jobs\SyncTenantInvoiceDetail;
use App\TenantInvoiceApiSync;
use App\Transaction;
use Illuminate\Console\Command;

class TenantInvoiceApiSyncPending extends Command
{
    protected $signature = 'tenant-invoice-api:sync-pending
        {--limit=200 : Max jobs to dispatch}
        {--only-failed : Only sync previously failed records}
        {--no-missing : Do not include transactions without a sync record}
        {--transaction_id= : Sync a single transaction id}';

    protected $description = 'Dispatch tenant invoice API sync jobs for pending/failed sales invoices.';

    public function handle()
    {
        if (!config('tenant_invoice_api.enabled')) {
            $this->warn('TENANT_INVOICE_API_ENABLED is false. Nothing to do.');
            return self::SUCCESS;
        }

        $limit = (int) $this->option('limit');
        if ($limit <= 0) {
            $this->error('--limit must be > 0');
            return self::FAILURE;
        }

        $transactionId = $this->option('transaction_id');
        if (!empty($transactionId)) {
            SyncTenantInvoiceDetail::dispatch((int) $transactionId);
            $this->info('Dispatched sync job for transaction_id=' . (int) $transactionId);
            return self::SUCCESS;
        }

        $statuses = $this->option('only-failed') ? ['failed'] : ['failed', 'pending'];

        $dispatched = 0;
        $syncs = TenantInvoiceApiSync::whereIn('status', $statuses)
            ->whereNull('sent_at')
            ->orderBy('updated_at')
            ->limit($limit)
            ->get();

        foreach ($syncs as $sync) {
            SyncTenantInvoiceDetail::dispatch((int) $sync->transaction_id);
            $dispatched++;
        }

        if (!$this->option('no-missing') && $dispatched < $limit) {
            $remaining = $limit - $dispatched;
            $transactionIds = Transaction::where('type', 'sell')
                ->where('status', 'final')
                ->whereNotNull('invoice_no')
                ->whereDoesntHave('tenant_invoice_api_sync')
                ->orderBy('id')
                ->limit($remaining)
                ->pluck('id');

            foreach ($transactionIds as $id) {
                SyncTenantInvoiceDetail::dispatch((int) $id);
                $dispatched++;
            }
        }

        $this->info("Dispatched {$dispatched} sync job(s).");

        return self::SUCCESS;
    }
}

