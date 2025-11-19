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
        // 1. Update Tabel Conferences (Info Bank)
        Schema::table('conferences', function (Blueprint $table) {
            if (!Schema::hasColumn('conferences', 'bank_name')) {
                $table->string('bank_name')->nullable();
                $table->string('bank_account_number')->nullable();
                $table->string('bank_account_holder')->nullable();
                $table->decimal('registration_fee', 10, 2)->nullable();
            }
        });

        // 2. Update Tabel Submissions (LoA & Payment Proof)
        Schema::table('submissions', function (Blueprint $table) {
            
            // FIX: Cek dulu apakah loa_path ada. Jika TIDAK ada, kita buat sekarang.
            if (!Schema::hasColumn('submissions', 'loa_path')) {
                // Kita taruh setelah status atau revised_paper_path
                $table->string('loa_path')->nullable()->after('status'); 
            }

            // Sekarang aman menambahkan payment_proof_path
            if (!Schema::hasColumn('submissions', 'payment_proof_path')) {
                $table->string('payment_proof_path')->nullable()->after('loa_path');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('conferences', function (Blueprint $table) {
            $table->dropColumn(['bank_name', 'bank_account_number', 'bank_account_holder', 'registration_fee']);
        });

        Schema::table('submissions', function (Blueprint $table) {
            $table->dropColumn(['payment_proof_path', 'loa_path']);
        });
    }
};