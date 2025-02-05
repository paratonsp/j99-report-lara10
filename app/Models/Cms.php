<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Cms extends Model
{
    public function scopeGetAddress($query)
    {
        $query = DB::table("v2_address As address")
            ->select('address.title','address.detail','address.phone')
            ->where('address.status', 1)
            ->orderBy('address.order')
            ->get();

        return $query;
    }
}
