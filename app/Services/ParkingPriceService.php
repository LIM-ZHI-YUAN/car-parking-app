<?php

namespace App\Services;

use App\Models\Zone;
use Carbon\Carbon;

class ParkingPriceService
{
    public static function calculatePrice(int $zone_id, string $start_time, string $stop_time=null): int
    {
        $start = new Carbon($start_time);
        $stop = $stop_time ? new Carbon($stop_time) : now();

        $totalTimeByMinutes = $start->diffInMinutes($stop);

        $priceByMinutes = Zone::find($zone_id)->price_per_hour / 60;

        return ceil($totalTimeByMinutes * $priceByMinutes);
    }
}