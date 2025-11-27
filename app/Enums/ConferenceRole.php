<?php

namespace App\Enums;

enum ConferenceRole: string
{
    case Chair = 'chair';
    case Reviewer = 'reviewer';
    // case Author = 'author';
    case Participant = 'participant'; // Pendengar/Peserta Biasa
}