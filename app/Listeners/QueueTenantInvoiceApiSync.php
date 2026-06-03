<?php

namespace App\Listeners;

use App\Events\SellCreatedOrModified;
use App\Jobs\SyncTenantInvoiceDetail;

class QueueTenantInvoiceApiSync
{
    public function handle(SellCreatedOrModified $event)
    {
        if (!config('tenant_invoice_api.enabled')) {
            return;
        }

        $transaction = $event->transaction;

        if (empty($transaction) || ($transaction->type ?? null) !== 'sell') {
            return;
        }

        if (($transaction->status ?? null) !== 'final') {
            return;
        }

        if (!empty($transaction->is_suspend)) {
            return;
        }

        try {
            SyncTenantInvoiceDetail::dispatch($transaction->id)->afterResponse();
        } catch (\Throwable $e) {
            // Never fail the sale flow because of the external sync.
            \Log::warning('Tenant invoice API dispatch failed: ' . $e->getMessage());
        }
    }
}

