<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Reservation extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'reservation';
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;

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
        return $this->belongsTo(Patient::class, 'patient_id', 'user_id');
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
     * The facilities that belong to the Reservation
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function facilities(): BelongsToMany
    {
        return $this->belongsToMany(Facility::class, 'reservations_facilities', 'reservation_id', 'facility_id');
    }
    
    /**
     * Calculate total fee based on room price per day, number of days, and facilities
     *
     * @param array $facilityIds Array of facility IDs (optional, if not provided will use existing facilities)
     * @return float
     */
    public function calculateTotalFee($facilityIds = null): float
    {
        // Calculate number of days
        $dateIn = $this->date_in instanceof \DateTime ? $this->date_in : new \DateTime($this->date_in);
        $dateOut = $this->date_out instanceof \DateTime ? $this->date_out : new \DateTime($this->date_out);
        $numberOfDays = max(1, $dateIn->diff($dateOut)->days); // Minimum 1 day
        
        // Get room price per day
        $roomPricePerDay = $this->room ? $this->room->price_per_day : 0;
        $roomTotalCost = $roomPricePerDay * $numberOfDays;
        
        // Calculate facilities cost
        $facilitiesCost = 0;
        
        if ($facilityIds) {
            // Use provided facility IDs (for new reservations)
            $facilities = Facility::whereIn('id', $facilityIds)->get();
            $facilitiesCost = $facilities->sum('fee');
        } else {
            // Use existing facilities (for existing reservations)
            $facilitiesCost = $this->facilities->sum('fee');
        }
        
        return $roomTotalCost + $facilitiesCost;
    }
    
    /**
     * Update the total fee and save the model
     *
     * @param array $facilityIds Array of facility IDs (optional)
     * @return bool
     */
    public function updateTotalFee($facilityIds = null): bool
    {
        $this->total_fee = $this->calculateTotalFee($facilityIds);
        return $this->save();
    }
    
    /**
     * Get the number of days for this reservation
     *
     * @return int
     */
    public function getNumberOfDaysAttribute(): int
    {
        if (!$this->date_in || !$this->date_out) {
            return 0;
        }
        
        $dateIn = $this->date_in instanceof \DateTime ? $this->date_in : new \DateTime($this->date_in);
        $dateOut = $this->date_out instanceof \DateTime ? $this->date_out : new \DateTime($this->date_out);
        
        return max(1, $dateIn->diff($dateOut)->days);
    }
    
    /**
     * Get the room cost (price per day * number of days)
     *
     * @return float
     */
    public function getRoomCostAttribute(): float
    {
        $roomPricePerDay = $this->room ? $this->room->price_per_day : 0;
        return $roomPricePerDay * $this->number_of_days;
    }
    
    /**
     * Get the total facilities cost
     *
     * @return float
     */
    public function getFacilitiesCostAttribute(): float
    {
        return $this->facilities->sum('fee');
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

    /**
     * Scope to find reservations that overlap with given date range
     */
    public function scopeOverlappingDates($query, $dateIn, $dateOut)
    {
        return $query->where('date_in', '<', $dateOut)
                     ->where('date_out', '>', $dateIn);
    }

    /**
     * Scope to find reservations within given date range
     */
    public function scopeWithinDates($query, $dateIn, $dateOut)
    {
        return $query->where('date_in', '>=', $dateIn)
                     ->where('date_out', '<=', $dateOut);
    }

    /**
     * Scope to find reservations for specific room
     */
    public function scopeForRoom($query, $roomId)
    {
        return $query->where('room_id', $roomId);
    }
}
