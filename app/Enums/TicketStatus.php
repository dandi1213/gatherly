<?php

namespace App\Enums;

enum TicketStatus: string
{
    case Active = 'active';
    case CheckedIn = 'checked_in';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Active',
            self::CheckedIn => 'Checked-in',
            self::Cancelled => 'Cancelled',
        };
    }
}
