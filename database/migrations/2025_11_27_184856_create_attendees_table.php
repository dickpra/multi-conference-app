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
        Schema::create('attendees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('conference_id')->constrained()->cascadeOnDelete();

            // Status Pembayaran (Mirip Submission)
            // pending (baru daftar), paid (lunas)
            $table->string('status')->default('pending'); 

            // Administrasi
            $table->string('invoice_number')->nullable();
            $table->string('invoice_path')->nullable();
            $table->string('payment_proof_path')->nullable();
            $table->string('certificate_path')->nullable(); // Sertifikat Partisipan

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendees');
    }
};
