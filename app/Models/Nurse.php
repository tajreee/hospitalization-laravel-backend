<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Nurse extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'nurse';

    protected $fillable = [
        'user_id'
    ];

    protected $casts = [
        'user_id' => 'string'
    ];

    /**
     * Get the user that owns the Nurse
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
