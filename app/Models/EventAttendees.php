<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventAttendees extends Model
{
    use HasFactory;

    protected $fillable = [
        'events_id',
        'users_id',
        'speaker'
    ];

    public function event()
    {
        return $this->belongsTo(Events::class, 'events_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'users_id');
    }
}
