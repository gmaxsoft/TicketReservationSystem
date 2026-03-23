<?php

namespace App\Models;

use App\Enums\TicketStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['title', 'slug', 'description', 'event_date', 'total_seats', 'price', 'poster_path', 'is_active'])]
class Event extends Model
{
    /**
     * @return HasMany<Ticket, $this>
     */
    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    /**
     * @return HasMany<OlxAd, $this>
     */
    public function olxAds(): HasMany
    {
        return $this->hasMany(OlxAd::class);
    }

    /**
     * Dostępność miejsc w MySQL: wolne miejsca względem biletów opłaconych lub zrealizowanych (used).
     * Uwzględnia „used”, żeby po check-inie nie zwalniać miejsca.
     */
    public function hasAvailableSeats(): bool
    {
        $soldOrUsed = $this->tickets()
            ->whereIn('status', [TicketStatus::Paid, TicketStatus::Used])
            ->count();

        return $this->total_seats > $soldOrUsed;
    }

    public function takenSeatsCount(): int
    {
        return $this->tickets()
            ->where(function ($query) {
                $query->whereIn('status', [TicketStatus::Paid, TicketStatus::Used])
                    ->orWhere(function ($q) {
                        $q->where('status', TicketStatus::Pending)
                            ->where('hold_expires_at', '>', now());
                    });
            })
            ->count();
    }

    public function availableSeatsCount(): int
    {
        return max(0, $this->total_seats - $this->takenSeatsCount());
    }

    public function ticketsSoldCount(): int
    {
        return $this->tickets()
            ->where('status', TicketStatus::Paid)
            ->count();
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'event_date' => 'datetime',
            'price' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }
}
