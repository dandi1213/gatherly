<?php

namespace App\Models;

use App\Enums\EventStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'organizer_id',
        'category_id',
        'title',
        'slug',
        'description',
        'poster_path',
        'format',
        'location',
        'online_url',
        'start_at',
        'end_at',
        'registration_start_at',
        'registration_end_at',
        'status',
        'is_disabled',
        'published_at',
        'cancelled_at',
    ];

    protected $appends = [
        'poster_url',
    ];

    protected function casts(): array
    {
        return [
            'start_at' => 'datetime',
            'end_at' => 'datetime',
            'registration_start_at' => 'datetime',
            'registration_end_at' => 'datetime',
            'published_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'status' => EventStatus::class,
            'is_disabled' => 'boolean',
        ];
    }

    /**
     * URL poster publik. Null bila belum ada poster.
     */
    protected function posterUrl(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (! $this->poster_path) {
                    return null;
                }

                $disk = config('filesystems.disks.supabase.key') ? 'supabase' : 'public';

                return Storage::disk($disk)->url($this->poster_path);
            },
        );
    }

    // ---------- Relationships ----------

    public function organizer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'organizer_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function ticketTypes(): HasMany
    {
        return $this->hasMany(TicketType::class);
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(Registration::class);
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    public function staff(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'event_staff')
            ->withPivot('assigned_by')
            ->withTimestamps();
    }

    // ---------- Scopes ----------

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', EventStatus::Published->value)
            ->where('is_disabled', false);
    }

    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->where('start_at', '>=', now());
    }

    // ---------- Helpers / State ----------

    public function isDraft(): bool
    {
        return $this->status === EventStatus::Draft;
    }

    public function isPublished(): bool
    {
        return $this->status === EventStatus::Published;
    }

    public function isCancelled(): bool
    {
        return $this->status === EventStatus::Cancelled;
    }

    /**
     * "Completed" dihitung dari waktu (C6), bukan disimpan.
     */
    public function isCompleted(): bool
    {
        return $this->isPublished() && $this->end_at->isPast();
    }

    /**
     * Kapasitas event = jumlah kuota semua ticket type aktif (C5).
     */
    public function capacity(): int
    {
        return (int) $this->ticketTypes()->where('is_active', true)->sum('quota');
    }

    /**
     * Apakah registrasi sedang dibuka saat ini.
     */
    public function isRegistrationOpen(): bool
    {
        return $this->isPublished()
            && ! $this->is_disabled
            && now()->between($this->registration_start_at, $this->registration_end_at);
    }

    /**
     * Awal jendela check-in (beberapa jam sebelum start_at, C7).
     */
    public function checkInWindowStart(): Carbon
    {
        return $this->start_at->copy()->subHours(
            (int) config('gatherly.checkin_window_hours', 2)
        );
    }

    /**
     * Jendela check-in: N jam sebelum start_at sampai end_at (C7).
     */
    public function isCheckInWindowOpen(): bool
    {
        if (! $this->isPublished() || $this->is_disabled) {
            return false;
        }

        return now()->between($this->checkInWindowStart(), $this->end_at);
    }

    public function isManagedBy(User $user): bool
    {
        return $user->isAdmin() || $this->organizer_id === $user->id;
    }

    /**
     * Apakah user ditugaskan sebagai staff pada event ini (D4).
     */
    public function isStaffedBy(User $user): bool
    {
        return $this->staff()->whereKey($user->id)->exists();
    }

    /**
     * Hanya organizer pemilik dan staff yang ditugaskan yang boleh check-in.
     * Admin tidak otomatis mendapat akses check-in (matriks akses 3.2).
     */
    public function canBeCheckedInBy(User $user): bool
    {
        return $this->organizer_id === $user->id || $this->isStaffedBy($user);
    }
}
