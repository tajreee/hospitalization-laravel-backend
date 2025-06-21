<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'room';
    protected $primaryKey = 'id';
    protected $keyType = 'string';

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $count = static::withTrashed()->count() + 1;
            $model->id = 'RM-' . str_pad($count, 4, '0', STR_PAD_LEFT);
        });
    }

    protected $fillable = ['id', 'name', 'description', 'max_capacity', 'price_per_day'];

    protected $casts = [
        'id' => 'string',
        'max_capacity' => 'integer',
        'price_per_day' => 'decimal:2',
    ];

    /**
     * Get all of the reservations for the Room
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }


}
