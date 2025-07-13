<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Room extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'room';
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;

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

    /**
     * Get reservations that overlap with given date range
     */
    public function getOverlappingReservations($dateIn, $dateOut)
    {
        return $this->reservations()
            ->where('date_in', '<', $dateOut)
            ->where('date_out', '>', $dateIn)
            ->count();
    }

    /**
     * Check if room is available for given date range
     */
    public function isAvailable($dateIn, $dateOut)
    {
        $overlappingCount = $this->getOverlappingReservations($dateIn, $dateOut);
        return $overlappingCount < $this->max_capacity;
    }


}
