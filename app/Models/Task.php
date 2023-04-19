<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property Carbon $done_at
 */
class Task extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'expires_at' => 'datetime',
        'done_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
