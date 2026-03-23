<?php

namespace App\Models;

use App\Enums\TicketStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'event_id',
    'user_email',
    'status',
    'unique_code',
    'qr_code_path',
    'payment_token',
    'hold_expires_at',
    'payment_id',
    'price_paid',
    'checked_in_at',
    'paid_at',
])]
class Ticket extends Model
{
    /**
     * @return BelongsTo<Event, $this>
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => TicketStatus::class,
            'hold_expires_at' => 'datetime',
            'checked_in_at' => 'datetime',
            'paid_at' => 'datetime',
            'price_paid' => 'decimal:2',
        ];
    }
}
