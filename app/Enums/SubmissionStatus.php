<?php

namespace App\Enums;

enum SubmissionStatus: string
{
    case Submitted = 'submitted';
    case UnderReview = 'under_review';
    case RevisionRequired = 'revision_required'; // <-- Tambahkan ini
    case RevisionSubmitted = 'revision_submitted'; // <-- Tambahkan ini
    case Accepted = 'accepted';
    case Rejected = 'rejected';
}