<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Event extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'date',
        'location',
        'description',
        'max_attendees',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'datetime',
        ];
    }

    public function registrations(){
        return $this->hasMany(Registration::class);
    }

    public function users(){
        return $this->belongsToMany(User::class, 'registrations')
            ->withPivot('status', 'registered_at', 'deleted_at')
            ->withTimeStamps();
    }
}
