<?php

namespace App\Models;

use App\Enums\TicketStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = [
        'registration_id',
        'event_id',
        'ticket_type_id',
        'ticket_code',
        'status',
        'checked_in_at',
        'checked_in_by',
    ];

    protected function casts(): array
    {
        return [
            'status' => TicketStatus::class,
            'checked_in_at' => 'datetime',
        ];
    }

    public function registration(): BelongsTo
    {
        return $this->belongsTo(Registration::class);
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function ticketType(): BelongsTo
    {
        return $this->belongsTo(TicketType::class);
    }

    public function checkedInBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'checked_in_by');
    }

    public function isActive(): bool
    {
        return $this->status === TicketStatus::Active;
    }

    public function isCheckedIn(): bool
    {
        return $this->status === TicketStatus::CheckedIn;
    }

    public function isCancelled(): bool
    {
        return $this->status === TicketStatus::Cancelled;
    }
}
