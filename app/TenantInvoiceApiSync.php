<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TenantInvoiceApiSync extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'payload' => 'array',
        'last_attempt_at' => 'datetime',
        'sent_at' => 'datetime',
    ];

    public function transaction()
    {
        return $this->belongsTo(\App\Transaction::class, 'transaction_id');
    }
}

