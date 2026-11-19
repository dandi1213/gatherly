<?php

namespace App\Models;

use App\Enums\RegistrationStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Registration extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'user_id',
        'ticket_type_id',
        'registration_number',
        'attendee_name',
        'attendee_email',
        'attendee_phone',
        'status',
        'cancelled_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => RegistrationStatus::class,
            'cancelled_at' => 'datetime',
        ];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function ticketType(): BelongsTo
    {
        return $this->belongsTo(TicketType::class);
    }

    public function ticket(): HasOne
    {
        return $this->hasOne(Ticket::class);
    }

    public function isRegistered(): bool
    {
        return $this->status === RegistrationStatus::Registered;
    }
}
