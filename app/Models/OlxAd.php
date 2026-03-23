<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['event_id', 'olx_external_id', 'olx_ad_id', 'status', 'link', 'last_sync_at', 'advert_data'])]
class OlxAd extends Model
{
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'advert_data' => 'array',
            'last_sync_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Event, $this>
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }
}
