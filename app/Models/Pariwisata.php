<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class Pariwisata extends Model
{

    public function scopeGetTarget($query, $param)
    {
        $query = DB::table('report_target_pariwisata');
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
        $monthYear = sprintf('%04d-%02d', $param['year'], $param['month']);

        return DB::table('ops_roadwarrant as rw')
            ->join('v2_bus as bus', 'rw.bus_uuid', '=', 'bus.uuid')
            ->whereIn('rw.bus_uuid', function ($q) use ($param) {
                $q->select('bb.bus_uuid')
                  ->from('v2_book_bus as bb')
                  ->join('v2_book as book', 'bb.book_uuid', '=', 'book.uuid')
                  ->whereMonth('book.start_date', $param['month'])
                  ->whereYear('book.start_date', $param['year']);
            })
            ->whereRaw('SUBSTRING(rw.departure_date, 1, 7) = ?', [$monthYear])
            ->groupBy('bus.uuid', 'bus.name')
            ->orderByDesc(DB::raw('COUNT(rw.uuid)'))
            ->select(DB::raw('bus.name as bus, COUNT(rw.uuid) as total'))
            ->get();
    }

    public function scopeGetIncomeDailyRange($query, $dateStart, $dateEnd)
    {
        return DB::table('v2_book')
            ->whereDate('start_date', '>=', $dateStart)
            ->whereDate('start_date', '<=', $dateEnd)
            ->select(DB::raw('SUM(total_price) as total'))
            ->get();
    }

    public function scopeGetDailyBooksByDate($query, $dateStart, $dateEnd)
    {
        return DB::table('v2_book')
            ->whereDate('start_date', '>=', $dateStart)
            ->whereDate('start_date', '<=', $dateEnd)
            ->groupBy(DB::raw('DATE(start_date)'))
            ->orderBy(DB::raw('DATE(start_date)'))
            ->select(DB::raw('DATE(start_date) as date, COUNT(*) as total'))
            ->get();
    }

    public function scopeGetDailyBusLakuByDate($query, $dateStart, $dateEnd)
    {
        return DB::table('v2_book as book')
            ->join('v2_book_bus as book_bus', 'book.uuid', '=', 'book_bus.book_uuid')
            ->whereDate('book.start_date', '>=', $dateStart)
            ->whereDate('book.start_date', '<=', $dateEnd)
            ->groupBy(DB::raw('DATE(book.start_date)'))
            ->orderBy(DB::raw('DATE(book.start_date)'))
            ->select(DB::raw('DATE(book.start_date) as date, COUNT(DISTINCT book_bus.bus_uuid) as total'))
            ->get();
    }

    public function scopeGetDailyBusDetailByDate($query, $dateStart, $dateEnd)
    {
        return DB::table('v2_book as book')
            ->join('v2_book_bus as book_bus', 'book.uuid', '=', 'book_bus.book_uuid')
            ->join('v2_bus as bus', 'book_bus.bus_uuid', '=', 'bus.uuid')
            ->whereDate('book.start_date', '>=', $dateStart)
            ->whereDate('book.start_date', '<=', $dateEnd)
            ->groupBy(DB::raw('DATE(book.start_date)'), 'bus.uuid', 'bus.name')
            ->orderBy(DB::raw('DATE(book.start_date)'))
            ->select(DB::raw('DATE(book.start_date) as date, bus.name as bus_name, COUNT(*) as total'))
            ->get();
    }

    /**
     * Jumlah hari bus beroperasi. Satu booking bisa lebih dari satu hari
     * (start_date s/d finish_date), jadi setiap booking dihitung per hari
     * dan dipotong sesuai rentang tanggal yang dipilih.
     */
    public function scopeGetBusRunningDaysByDate($query, $dateStart, $dateEnd)
    {
        $rangeStart = Carbon::parse($dateStart)->startOfDay();
        $rangeEnd   = Carbon::parse($dateEnd)->startOfDay();

        $books = DB::table('v2_book as book')
            ->join('v2_book_bus as book_bus', 'book.uuid', '=', 'book_bus.book_uuid')
            ->join('v2_bus as bus', 'book_bus.bus_uuid', '=', 'bus.uuid')
            ->whereDate('book.start_date', '<=', $dateEnd)
            ->whereDate(DB::raw('COALESCE(book.finish_date, book.start_date)'), '>=', $dateStart)
            ->select(
                'bus.uuid as bus_uuid',
                'bus.name as bus_name',
                'book.start_date',
                'book.finish_date'
            )
            ->get();

        return $books->groupBy('bus_uuid')
            ->map(function ($rows) use ($rangeStart, $rangeEnd) {
                $days = [];

                foreach ($rows as $row) {
                    $from = Carbon::parse($row->start_date)->startOfDay()->max($rangeStart);
                    $to   = Carbon::parse($row->finish_date ?? $row->start_date)->startOfDay()->min($rangeEnd);

                    foreach (CarbonPeriod::create($from, $to) as $day) {
                        $days[$day->format('Y-m-d')] = true;
                    }
                }

                return (object) [
                    'bus_name'      => $rows->first()->bus_name,
                    'total_days'    => count($days),
                    'total_booking' => $rows->count(),
                ];
            })
            ->sortByDesc('total_days')
            ->values();
    }

    public function scopeGetDailyBusListByMonth($query, $param)
    {
        return DB::table('v2_book as book')
            ->join('v2_book_bus as book_bus', 'book.uuid', '=', 'book_bus.book_uuid')
            ->join('v2_bus as bus', 'book_bus.bus_uuid', '=', 'bus.uuid')
            ->whereMonth('book.start_date', $param['month'])
            ->whereYear('book.start_date', $param['year'])
            ->groupBy(DB::raw('DATE(book.start_date)'), 'bus.uuid', 'bus.name')
            ->orderBy(DB::raw('DATE(book.start_date)'))
            ->select(DB::raw('DATE(book.start_date) as date, bus.name as bus_name, COUNT(*) as total'))
            ->get();
    }
}
