<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TicketType extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'name',
        'description',
        'quota',
        'available_from',
        'available_until',
        'is_active',
    ];

    protected $appends = [
        'issued_count',
        'remaining_quota',
        'is_available_now',
    ];

    protected function casts(): array
    {
        return [
            'quota' => 'integer',
            'available_from' => 'datetime',
            'available_until' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(Registration::class);
    }

    /**
     * Jumlah tiket terbit (Active + Checked-in). Cancelled tidak dihitung (kuota kembali).
     */
    protected function issuedCount(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->tickets()
                ->whereIn('status', ['active', 'checked_in'])
                ->count(),
        );
    }

    protected function remainingQuota(): Attribute
    {
        return Attribute::make(
            get: fn () => max(0, $this->quota - $this->issued_count),
        );
    }

    protected function isAvailableNow(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->computeIsAvailableNow(),
        );
    }

    public function isCurrentlyAvailable(): bool
    {
        return $this->computeIsAvailableNow();
    }

    private function computeIsAvailableNow(): bool
    {
        if (! $this->is_active) {
            return false;
        }

        $now = now();

        if ($this->available_from && $now->lt($this->available_from)) {
            return false;
        }

        if ($this->available_until && $now->gt($this->available_until)) {
            return false;
        }

        return true;
    }
}
