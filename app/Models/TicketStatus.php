<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Ticket;

class TicketStatus extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug', 'color_class'];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($status) {
            if (empty($status->slug)) {
                $status->slug = Str::slug($status->name);
            }
        });
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class, 'status_id');
    }
}