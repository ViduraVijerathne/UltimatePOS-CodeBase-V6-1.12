<?php

return [
    'enabled' => (bool) env('TENANT_INVOICE_API_ENABLED', false),

    // Example (UAT): http://tenantinvoiceapi.softlogic.lk:8052/api/InvoiceDetail/
    // Example (LIVE): http://tenantinvoiceapilive.softlogic.lk:8053/api/InvoiceDetail/
    'base_url' => env('TENANT_INVOICE_API_BASE_URL'),

    // Header name: X-APIKey
    'api_key' => env('TENANT_INVOICE_API_KEY'),

    // If not set, `location_id` is used as POSNo.
    'pos_no' => env('TENANT_INVOICE_API_POS_NO'),

    // HTTP timeout seconds
    'timeout' => (int) env('TENANT_INVOICE_API_TIMEOUT', 15),
];

