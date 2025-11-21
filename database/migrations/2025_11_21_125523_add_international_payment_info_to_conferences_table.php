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
        Schema::table('conferences', function (Blueprint $table) {
            // Info Perusahaan/Organisasi
            $table->string('vat_number')->nullable()->after('registration_fee');
            $table->text('postal_address')->nullable()->after('vat_number');

            // Detail Tambahan Bank
            $table->text('bank_account_address')->nullable()->after('bank_account_holder'); // Alamat pemilik rekening
            $table->string('bank_city')->nullable()->after('bank_account_address');
            $table->string('swift_code')->nullable()->after('bank_city');
        });
    }

    public function down(): void
    {
        Schema::table('conferences', function (Blueprint $table) {
            $table->dropColumn(['vat_number', 'postal_address', 'bank_account_address', 'bank_city', 'swift_code']);
        });
    }
};
