<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EntriesCountResource extends JsonResource
{
    /**
     * The maximum number of entries allowed per user.
     */
    private const ENTRY_LIMIT = 50;

    /**
     * Transform the resource into an array.
     *
     * Returns the user's current entry count along with calculated
     * metadata about remaining entries and limit status.
     *
     * @param Request $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Get the current count, defaulting to 0 if no record exists
        $currentCount = $this->resource ? (int) $this->resource->count : 0;

        return [
            'count' => $currentCount,
            'limit' => self::ENTRY_LIMIT,
            'remaining' => max(0, self::ENTRY_LIMIT - $currentCount),
            'is_at_limit' => $currentCount >= self::ENTRY_LIMIT,
        ];
    }
}