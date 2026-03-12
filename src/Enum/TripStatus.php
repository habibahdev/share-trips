<?php

namespace App\Enum;

enum TripStatus: string
{
    case Open = 'open';
    case Full = 'full';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
}
