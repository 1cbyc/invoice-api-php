<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->decimal('amount_paid', 18, 2)->default(0)->after('total_amount');
            $table->decimal('balance_due', 18, 2)->default(0)->after('amount_paid');
            $table->date('paid_date')->nullable()->after('balance_due');
            $table->string('payment_method', 50)->nullable()->after('paid_date');
            $table->string('payment_reference', 100)->nullable()->after('payment_method');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['amount_paid', 'balance_due', 'paid_date', 'payment_method', 'payment_reference']);
        });
    }
};
