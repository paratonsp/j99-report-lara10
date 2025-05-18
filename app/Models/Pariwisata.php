<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class Pariwisata extends Model
{

    public function scopeGetTarget($query, $param)
    {
        $query = DB::table('report_target_akap');
        $query = $query->where('month', $param['month'])->where('year', $param['year']);;
        $query = $query->select('*')->get();

        return $query;
    }

    public function scopeGetIncomeMonthly($query, $param)
    {
        $query = DB::table('v2_book as book');
        $query = $query->whereMonth('book.start_date', $param['month']);
        $query = $query->whereYear('book.start_date', $param['year']);
        $query = $query->select(
            DB::raw('SUM(book.total_price) as total')
        )->get();

        return $query;
    }

    public function scopeGetBookDeparture($query, $param)
    {
        $query = DB::table('v2_book as book');
        $query = $query->join('v2_area_city as city', 'book.departure_city_uuid', '=', 'city.uuid');
        $query = $query->join('v2_book_bus as book_bus', 'book.uuid', '=', 'book_bus.book_uuid');
        $query = $query->whereMonth('book.start_date', $param['month']);
        $query = $query->whereYear('book.start_date', $param['year']);
        $query = $query->groupBy('book.departure_city_uuid')
            ->select(
                DB::raw('city.name as city, count(*) as total')
            )
            ->get();

        return $query;
    }

    public function scopeGetBookArrival($query, $param)
    {
        $query = DB::table('v2_book as book');
        $query = $query->join('v2_area_city as city', 'book.destination_city_uuid', '=', 'city.uuid');
        $query = $query->join('v2_book_bus as book_bus', 'book.uuid', '=', 'book_bus.book_uuid');
        $query = $query->whereMonth('book.start_date', $param['month']);
        $query = $query->whereYear('book.start_date', $param['year']);
        $query = $query->groupBy('book.destination_city_uuid')
            ->select(
                DB::raw('city.name as city, count(*) as total')
            )
            ->get();

        return $query;
    }

    public function scopeGetBookClass($query, $param)
    {
        $query = DB::table('v2_book as book');
        $query = $query->join('v2_book_bus as book_bus', 'book.uuid', '=', 'book_bus.book_uuid');
        $query = $query->join('v2_bus as bus', 'book_bus.bus_uuid', '=', 'bus.uuid');
        $query = $query->join('v2_class as class', 'bus.class_uuid', '=', 'class.uuid');
        $query = $query->whereMonth('book.start_date', $param['month']);
        $query = $query->whereYear('book.start_date', $param['year']);
        $query = $query->groupBy('class.name')
            ->select(
                DB::raw('class.name as class, count(*) as total')
            )
            ->get();

        return $query;
    }

    public function scopeGetBookBus($query, $param)
    {
        $query = DB::table('v2_book as book');
        $query = $query->join('v2_book_bus as book_bus', 'book.uuid', '=', 'book_bus.book_uuid');
        $query = $query->join('v2_bus as bus', 'book_bus.bus_uuid', '=', 'bus.uuid');
        $query = $query->whereMonth('book.start_date', $param['month']);
        $query = $query->whereYear('book.start_date', $param['year']);
        $query = $query->groupBy('bus.name')
            ->select(
                DB::raw('bus.name as bus, count(*) as total')
            )
            ->get();

        return $query;
    }
}
