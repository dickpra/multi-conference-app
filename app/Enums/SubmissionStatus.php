<?php

namespace App\Enums;

enum SubmissionStatus: string
{
    case Pending = 'pending';
    case Submitted = 'submitted';
    case UnderReview = 'under_review';
    case RevisionRequired = 'revision_required'; // <-- Tambahkan ini
    case RevisionSubmitted = 'revision_submitted'; // <-- Tambahkan ini
    
    case Accepted = 'accepted';
    case Rejected = 'rejected';

    case PaymentSubmitted = 'payment_submitted'; // Bukti bayar diupload, menunggu verifikasi
    case Paid = 'paid'; // Lunas, LoA dikirim
}