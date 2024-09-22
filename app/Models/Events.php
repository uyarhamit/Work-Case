<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Events extends Model
{
    use HasFactory;

    protected $fillable = [
        'created_user_id',
        'title',
        'start_date',
        'end_date',
        'duration',
        'attendance_limit',
        'is_expired'
    ];


    protected function casts(): array
    {
        return [
            'start_date' => 'datetime',
            'end_date' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }


    public function createdUser()
    {
        return $this->hasOne(User::class, 'id', 'created_user_id');
    }

    public function eventAttendees()
    {
        return $this->hasMany(EventAttendees::class, 'events_id', 'id');
    }
}
