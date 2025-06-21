<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'reservation';
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    protected $incrementing = false;

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            // Get the count of reservations for 4-digit suffix
            $count = static::withTrashed()->count() + 1;
            $countFormatted = str_pad($count, 4, '0', STR_PAD_LEFT);
            
            // Get days difference between date_in and date_out (last 2 digits)
            $dateIn = new \DateTime($model->date_in);
            $dateOut = new \DateTime($model->date_out);
            $daysDiff = $dateIn->diff($dateOut)->days;
            $daysDiffFormatted = str_pad($daysDiff % 100, 2, '0', STR_PAD_LEFT);
            
            // Get first 3 characters of day name
            $dayName = strtoupper(substr($dateIn->format('l'), 0, 3));
            
            // Get last 4 digits of NIK
            $patient = Patient::where('user_id', $model->patient_id)->first();
            $nikLastDigits = substr($patient->nik, -4);
            
            // Combine all parts
            $model->id = 'RES' . $daysDiffFormatted . $dayName . $nikLastDigits . $countFormatted;
        });
    }

    protected $fillable = [
        'id',
        'date_in',
        'date_out',
        'total_fee',
        'patient_id',
        'nurse_id',
        'room_id'
    ];

    protected $casts = [
        'id' => 'string',
        'date_in' => 'datetime',
        'date_out' => 'datetime',
        'total_fee' => 'decimal:2',
    ];

    /**
     * Get the patient that owns the Reservation
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }

    /**
     * Get the nurse that owns the Reservation
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function nurse(): BelongsTo
    {
        return $this->belongsTo(Nurse::class, 'nurse_id');
    }
    
    /**
     * Get the room that owns the Reservation
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class, 'room_id');
    }
    
    /**
     * Get the formatted total fee
     *
     * @return string
     */
    public function getFormattedTotalFeeAttribute(): string
    {
        return number_format($this->total_fee, 2, ',', '.');
    }
    
    /**
     * Get the formatted date_in
     *
     * @return string
     */
    public function getFormattedDateInAttribute(): string
    {
        return $this->date_in ? $this->date_in->format('d-m-Y H:i') : '';
    }
    
    /**
     * Get the formatted date_out
     *
     * @return string
     */
    public function getFormattedDateOutAttribute(): string
    {
        return $this->date_out ? $this->date_out->format('d-m-Y H:i') : '';
    }
    
    /**
     * Get the formatted ID
     *
     * @return string
     */
    public function getFormattedIdAttribute(): string
    {
        return $this->id ? strtoupper($this->id) : '';
    }
}
