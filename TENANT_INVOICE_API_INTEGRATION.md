# Tenant Invoice Detail Insert API Integration

This document explains the integration work added to sync sales invoices from UltimatePOS to the tenant invoice API, the files that were changed, how the flow works, and what still needs attention.

## What This Integration Does

When a sale is saved as a final invoice, the application now builds the tenant API payload and sends it to the external endpoint defined in `.env`.

The payload follows the API document from `Tenant Invoice Detail Insert - API.docx`:

- `InvoiceNo`
- `POSNo`
- `GrossAmount`
- `DiscountAmount`
- `NetAmount`
- `InvoiceCreateDate`
- `invoiceItems[]`

Each line item includes:

- `ItemCode`
- `ItemDescription`
- `UOM`
- `Qty`
- `MRP`
- `Discount`
- `NetTotal`

## Files Added Or Updated

- [config/tenant_invoice_api.php](/Users/vidura/Documents/industry-projects/aisha/UltimatePOS-CodeBase-V6-1.12/config/tenant_invoice_api.php)
- [app/Utils/TenantInvoiceApiUtil.php](/Users/vidura/Documents/industry-projects/aisha/UltimatePOS-CodeBase-V6-1.12/app/Utils/TenantInvoiceApiUtil.php)
- [config/logging.php](/Users/vidura/Documents/industry-projects/aisha/UltimatePOS-CodeBase-V6-1.12/config/logging.php)
- [database/migrations/2026_06_03_000001_create_tenant_invoice_api_syncs_table.php](/Users/vidura/Documents/industry-projects/aisha/UltimatePOS-CodeBase-V6-1.12/database/migrations/2026_06_03_000001_create_tenant_invoice_api_syncs_table.php)
- [app/TenantInvoiceApiSync.php](/Users/vidura/Documents/industry-projects/aisha/UltimatePOS-CodeBase-V6-1.12/app/TenantInvoiceApiSync.php)
- [app/Jobs/SyncTenantInvoiceDetail.php](/Users/vidura/Documents/industry-projects/aisha/UltimatePOS-CodeBase-V6-1.12/app/Jobs/SyncTenantInvoiceDetail.php)
- [app/Listeners/QueueTenantInvoiceApiSync.php](/Users/vidura/Documents/industry-projects/aisha/UltimatePOS-CodeBase-V6-1.12/app/Listeners/QueueTenantInvoiceApiSync.php)
- [app/Providers/EventServiceProvider.php](/Users/vidura/Documents/industry-projects/aisha/UltimatePOS-CodeBase-V6-1.12/app/Providers/EventServiceProvider.php)
- [app/Transaction.php](/Users/vidura/Documents/industry-projects/aisha/UltimatePOS-CodeBase-V6-1.12/app/Transaction.php)
- [app/Console/Commands/TenantInvoiceApiSyncPending.php](/Users/vidura/Documents/industry-projects/aisha/UltimatePOS-CodeBase-V6-1.12/app/Console/Commands/TenantInvoiceApiSyncPending.php)
- [.env.example](/Users/vidura/Documents/industry-projects/aisha/UltimatePOS-CodeBase-V6-1.12/.env.example)

## Runtime Flow

1. A sale is created in `SellPosController`.
2. After the DB transaction commits, `SellCreatedOrModified` is dispatched.
3. `QueueTenantInvoiceApiSync` listens for that event.
4. If the transaction is a final sale, a job is dispatched.
5. `SyncTenantInvoiceDetail` loads the transaction, builds the API payload, and sends the POST request.
6. The sync record is saved in `tenant_invoice_api_syncs` so we can track sent/failed/pending states.

## Configuration

The integration uses these `.env` keys:

- `TENANT_INVOICE_API_ENABLED`
- `TENANT_INVOICE_API_BASE_URL`
- `TENANT_INVOICE_API_KEY`
- `TENANT_INVOICE_API_POS_NO`
- `TENANT_INVOICE_API_TIMEOUT`
- `TENANT_INVOICE_API_LOG_LEVEL`

Example:

```env
TENANT_INVOICE_API_ENABLED=true
TENANT_INVOICE_API_BASE_URL=http://tenantinvoiceapi.softlogic.lk:8052/api/InvoiceDetail/
TENANT_INVOICE_API_KEY=your-key-here
TENANT_INVOICE_API_POS_NO=456
TENANT_INVOICE_API_TIMEOUT=15
TENANT_INVOICE_API_LOG_LEVEL=info
```

## Logging

A dedicated log channel was added:

- file path: `storage/logs/tenant_invoice_api.log`
- channel name: `tenant_invoice_api`

The code writes:

- request time
- request URL
- request payload
- response time
- HTTP status
- response body
- exception message when the request fails

## Retry And Tracking

To avoid losing failed syncs, the integration stores state in `tenant_invoice_api_syncs`.

Important columns:

- `transaction_id`
- `status`
- `attempts`
- `last_attempt_at`
- `last_http_status`
- `last_response_body`
- `last_error`
- `payload`
- `sent_at`

There is also a console command:

```bash
php artisan tenant-invoice-api:sync-pending
```

Useful options:

- `--transaction_id=123`
- `--only-failed`
- `--limit=200`

## Current Problem To Know About

The user reported that the log file is still not being written. The most likely reasons to check first are:

1. `TENANT_INVOICE_API_ENABLED=false` in `.env`
2. Laravel config cache is still holding old values
3. Queue/job path is not being executed because the listener exits early
4. The sale is not ending up with `status = final`
5. The app process does not have write permission to `storage/logs`

## What The Next Developer Should Do

1. Confirm the tenant API must be enabled in the production `.env`.
2. Run the app cache clear commands after updating `.env`.
3. Verify the sale is final and not draft/suspended.
4. Check whether `storage/logs/tenant_invoice_api.log` is writable on the server.
5. If needed, trigger a manual sync with the Artisan command above.
6. If logging still does not appear, inspect whether the event listener is being hit and whether the dedicated log channel is active in the deployed environment.

## Notes For Future Work

- The sync currently uses the final sale transaction as the source of truth.
- The current implementation is intentionally non-blocking for the sale save flow.
- The retry table gives a foundation for later adding a dedicated retry scheduler or dashboard.
- If the API expects a different interpretation of `GrossAmount` or line item discount, that mapping should be confirmed with the API owner before changing production behavior.

## Verification Done In Code

The modified PHP files passed syntax checks with `php -l`.

## Short Version

The code now knows how to:

- read the tenant API URL and key from `.env`
- build the invoice payload from a final sale
- send the API request
- write request/response data to a separate log file
- keep a retry record for each invoice
- allow manual resync from the command line

The remaining issue reported by the user is operational, not structural: the integration needs the runtime env, config cache, and file permissions to be correct before logs will appear.

