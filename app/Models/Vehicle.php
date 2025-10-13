<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use App\Observers\VehicleObserver;

#[ObservedBy(VehicleObserver::class)]
class Vehicle extends Model
{
    use HasFactory;
    use SoftDeletes;
 
    protected $fillable = ['user_id', 'plate_number', 'description'];

    protected static function booted(): void
    {
        static::addGlobalScope('user', function (Builder $builder) {
            $builder->where('user_id', auth()->id());
        });
    }
}
