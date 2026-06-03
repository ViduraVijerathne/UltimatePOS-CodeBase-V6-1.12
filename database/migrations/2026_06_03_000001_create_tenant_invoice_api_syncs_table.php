<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('tenant_invoice_api_syncs', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('transaction_id')->unique();
            $table->string('idempotency_key', 64)->nullable();
            $table->enum('status', ['pending', 'sent', 'failed'])->default('pending');
            $table->unsignedInteger('attempts')->default(0);
            $table->dateTime('last_attempt_at')->nullable();
            $table->unsignedSmallInteger('last_http_status')->nullable();
            $table->longText('last_response_body')->nullable();
            $table->longText('last_error')->nullable();
            $table->json('payload')->nullable();
            $table->dateTime('sent_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'sent_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('tenant_invoice_api_syncs');
    }
};

