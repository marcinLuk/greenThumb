<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EntriesCount extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'entries_count';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'user_id';

    /**
     * Indicates if the model's ID is auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'count',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
            'count' => 'integer',
        ];
    }

    /**
     * Set default attribute values.
     *
     * @return array<string, mixed>
     */
    protected function attributes(): array
    {
        return [
            'count' => 0,
        ];
    }

    /**
     * Get the user that owns the entries count.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
