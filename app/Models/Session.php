<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Session extends Model
{
    use HasFactory;
    
    protected $fillable = ['qr_code', 'expire_at'];
    
    protected $casts = [
        'expire_at' => 'datetime',
    ];
    
    // Add this method to generate QR code before saving
    protected static function booted()
    {
        static::creating(function ($session) {
            $session->qr_code = Str::uuid()->toString();
            $session->expire_at = now()->addMinutes(5);
        });
    }
    
    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }
    
    public function generateQrCode()
    {
        $this->qr_code = Str::uuid()->toString();
        $this->expire_at = now()->addMinutes(5);
        $this->save();
        
        return $this->qr_code;
    }
    
    public function isExpired()
    {
        return now()->gt($this->expire_at);
    }
}

