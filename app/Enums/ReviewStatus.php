<?php

namespace App\Enums;

enum ReviewStatus: string
{
    case Pending = 'pending';
    case Completed = 'completed';
}