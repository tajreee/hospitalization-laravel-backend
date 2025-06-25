<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class Facility extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'facility';
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = Str::uuid();
            }
        });
    }

    protected $fillable = [
        'id',
        'name',
        'fee',
    ];

    protected $casts = [
        'id' => 'string',
        'fee' => 'decimal:2',
    ];

    /**
     * The reservations that belong to the Facility
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function reservations(): BelongsToMany
    {
        return $this->belongsToMany(Reservation::class, 'reservations_facilities', 'facility_id', 'reservation_id');
    }
}
