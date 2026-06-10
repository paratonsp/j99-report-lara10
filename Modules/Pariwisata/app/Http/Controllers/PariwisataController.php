<?php

namespace Modules\Pariwisata\app\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Carbon\Carbon;
use App\Models\Pariwisata;
use Illuminate\Support\Number;
use IcehouseVentures\LaravelChartjs\Facades\Chartjs;

class PariwisataController extends Controller
{
    public function index(Request $request)
    {

        $month = ($request->has('month')) ? $request->input('month') : date('n');
        $year = ($request->has('year')) ? $request->input('year') : date('Y');

        $total_days = Carbon::now()->month($month)->daysInMonth;

        $param = [
            'month' => $month,
            'year' => $year,
        ];

        $target = Pariwisata::getTarget($param);
        if ($target->isEmpty()) {
            $target = "Target belum disetting";
        } else {
            $target = Number::currency($target[0]->target, 'IDR');
        }

        $data['monthly_income'] = Number::currency($this->incomeMonthly($param), 'IDR');
        $data['penjualan_by_kota_departure_bar_chart'] = $this->penjualanBerdasarkanKota($param)['departure_bar_chart'];
        $data['penjualan_by_kota_arrival_bar_chart'] = $this->penjualanBerdasarkanKota($param)['arrival_bar_chart'];
        $data['penjualan_by_kota_departure_pie_chart'] = $this->penjualanBerdasarkanKota($param)['departure_pie_chart'];
        $data['penjualan_by_kota_arrival_pie_chart'] = $this->penjualanBerdasarkanKota($param)['arrival_pie_chart'];
        $data['penjualan_by_kelas_bar_chart'] = $this->penjualanBerdasarkanKelas($param)['bar_chart'];
        $data['penjualan_by_kelas_pie_chart'] = $this->penjualanBerdasarkanKelas($param)['pie_chart'];
        $data['penjualan_by_unit_bar_chart'] = $this->penjualanBerdasarkanUnit($param)['bar_chart'];
        $data['penjualan_by_unit_pie_chart'] = $this->penjualanBerdasarkanUnit($param)['pie_chart'];

        $data['target'] = $target;
        $data['title'] = 'REPORT PARIWISATA';
        return view('pariwisata::index', $data);
    }

    public function incomeMonthly($param)
    {
        $data = Pariwisata::getIncomeMonthly($param);

        if ($data[0]->total == null) {
            $data = 0;
        } else {
            $data = $data[0]->total;
        }
        return $data;
    }

    public function penjualanBerdasarkanKota($param)
    {
        //KEBERANGKATAN
        $departure = Pariwisata::getBookDeparture($param);

        $departure_label = $departure->pluck('city')->toArray();
        $departure_value = $departure->pluck('total')->toArray();
        $departure_color = array();

        for ($x = 0; $x <= count($departure); $x++) {
            array_push($departure_color, generateColor($x));
        }

        $data['departure_bar_chart'] = Chartjs::build()
            ->name("departurePanjualanByKotaBarChart")
            ->type("bar")
            ->size(["width" => 400, "height" => 200])
            ->labels($departure_label)
            ->datasets([
                [
                    "label" => "Penggunaan Unit",
                    "data" => $departure_value,
                    'backgroundColor' => generateColor(1),
                ]
            ]);

        $data['departure_pie_chart'] = Chartjs::build()
            ->name("departurePanjualanByKotaPieChart")
            ->type("pie")
            ->size(["width" => 400, "height" => 400])
            ->labels($departure_label)
            ->datasets([
                [
                    "label" => "Penggunaan Unit",
                    "data" => $departure_value,
                    'backgroundColor' => $departure_color,
                ]
            ]);

        //KEDATANGAN
        $arrival = Pariwisata::getBookArrival($param);

        $arrival_label = $arrival->pluck('city')->toArray();
        $arrival_value = $arrival->pluck('total')->toArray();
        $arrival_color = array();

        for ($x = 0; $x <= count($arrival); $x++) {
            array_push($arrival_color, generateColor($x));
        }

        $data['arrival_bar_chart'] = Chartjs::build()
            ->name("arrivalPanjualanByKotaBarChart")
            ->type("bar")
            ->size(["width" => 400, "height" => 200])
            ->labels($arrival_label)
            ->datasets([
                [
                    "label" => "Penggunaan Unit",
                    "data" => $arrival_value,
                    'backgroundColor' => generateColor(1),
                ]
            ]);

        $data['arrival_pie_chart'] = Chartjs::build()
            ->name("arrivalPanjualanByKotaPieChart")
            ->type("pie")
            ->size(["width" => 400, "height" => 400])
            ->labels($arrival_label)
            ->datasets([
                [
                    "label" => "Penggunaan Unit",
                    "data" => $arrival_value,
                    'backgroundColor' => $arrival_color,
                ]
            ]);


        return $data;
    }

    public function penjualanBerdasarkanKelas($param)
    {
        $book = Pariwisata::getBookClass($param);

        $book_label = $book->pluck('class')->toArray();
        $book_value = $book->pluck('total')->toArray();
        $book_color = array();

        for ($x = 0; $x <= count($book); $x++) {
            array_push($book_color, generateColor($x));
        }

        $data['bar_chart'] = Chartjs::build()
            ->name("PenjualanBerdasarkanKelasBarChart")
            ->type("horizontalBar")
            ->size(["width" => 400, "height" => 200])
            ->labels($book_label)
            ->datasets([
                [
                    "label" => "Penggunaan Unit",
                    "data" => $book_value,
                    'backgroundColor' => generateColor(1),
                ]
            ]);

        $data['pie_chart'] = Chartjs::build()
            ->name("PenjualanBerdasarkanKelasPieChart")
            ->type("pie")
            ->size(["width" => 400, "height" => 400])
            ->labels($book_label)
            ->datasets([
                [
                    "label" => "Penggunaan Unit",
                    "data" => $book_value,
                    'backgroundColor' => $book_color,
                ]
            ]);

        return $data;
    }

    public function daily(Request $request)
    {
        $dateStart = $request->input('dateStart', date('Y-m-d'));
        $dateEnd   = $request->input('dateEnd',   date('Y-m-d'));

        $income = Pariwisata::getIncomeDailyRange($dateStart, $dateEnd);
        $incomeTotal = $income[0]->total ?? 0;

        $labels = $this->buildDateLabels($dateStart, $dateEnd);

        return view('pariwisata::daily', [
            'title'                  => 'REPORT PARIWISATA HARIAN',
            'dateStart'              => $dateStart,
            'dateEnd'                => $dateEnd,
            'income_total'           => Number::currency($incomeTotal ?: 0, 'IDR'),
            'penjualan_harian_chart' => $this->chartPenjualanHarian($dateStart, $dateEnd, $labels),
            'bus_laku_harian_chart'  => $this->chartBusLakuHarian($dateStart, $dateEnd, $labels),
        ]);
    }

    private function buildDateLabels(string $dateStart, string $dateEnd): array
    {
        return collect(\Carbon\CarbonPeriod::create($dateStart, $dateEnd))
            ->map(fn($d) => $d->format('Y-m-d'))
            ->toArray();
    }

    private function chartPenjualanHarian(string $dateStart, string $dateEnd, array $labels)
    {
        $rows = Pariwisata::getDailyBooksByDate($dateStart, $dateEnd);
        $map  = $rows->pluck('total', 'date')->toArray();
        $displayLabels = array_map(fn($d) => date('d/m', strtotime($d)), $labels);
        $values = array_map(fn($d) => $map[$d] ?? 0, $labels);

        return Chartjs::build()
            ->name("PenjualanHarianLineChart")
            ->type("line")
            ->size(["width" => 400, "height" => 200])
            ->labels($displayLabels)
            ->datasets([
                [
                    "label" => "Penjualan",
                    "data" => $values,
                    'borderColor' => '#ff0000',
                    'fill' => false,
                    'pointBorderWidth' => 4,
                ]
            ]);
    }

    private function chartBusLakuHarian(string $dateStart, string $dateEnd, array $labels)
    {
        $rows = Pariwisata::getDailyBusLakuByDate($dateStart, $dateEnd);
        $map  = $rows->pluck('total', 'date')->toArray();
        $displayLabels = array_map(fn($d) => date('d/m', strtotime($d)), $labels);
        $values = array_map(fn($d) => $map[$d] ?? 0, $labels);

        return Chartjs::build()
            ->name("BusLakuHarianBarChart")
            ->type("bar")
            ->size(["width" => 400, "height" => 200])
            ->labels($displayLabels)
            ->datasets([
                [
                    "label" => "Bus Laku",
                    "data" => $values,
                    'backgroundColor' => generateColor(1),
                ]
            ]);
    }

    public function penjualanBerdasarkanUnit($param)
    {
        $book = Pariwisata::getBookBus($param);

        $book_label = $book->pluck('bus')->toArray();
        $book_value = $book->pluck('total')->toArray();
        $book_color = array();

        for ($x = 0; $x <= count($book); $x++) {
            array_push($book_color, generateColor($x));
        }

        $data['bar_chart'] = Chartjs::build()
            ->name("PenjualanBerdasarkanUnitBarChart")
            ->type("horizontalBar")
            ->size(["width" => 400, "height" => 200])
            ->labels($book_label)
            ->datasets([
                [
                    "label" => "Penggunaan Unit",
                    "data" => $book_value,
                    'backgroundColor' => generateColor(1),
                ]
            ]);

        $data['pie_chart'] = Chartjs::build()
            ->name("PenjualanBerdasarkanUnitPieChart")
            ->type("pie")
            ->size(["width" => 400, "height" => 400])
            ->labels($book_label)
            ->datasets([
                [
                    "label" => "Penggunaan Unit",
                    "data" => $book_value,
                    'backgroundColor' => $book_color,
                ]
            ]);

        return $data;
    }
}
