@extends('layouts.main', ['title' => $title ])

@section('content')
<?php
$date = (isset($_GET['date'])) ? new DateTime(date("Y-m", strtotime($_GET['date']))) : new DateTime(date("Y-m"));
$month = (isset($_GET['month'])) ? $_GET['month'] : date("n");
$year = (isset($_GET['year'])) ? $_GET['year'] : date("Y");
$startYear = date('Y') - 2;
$endYear = date('Y') + 1;
?>

<div class="row mb-2">
    <div class="col-12">
        <div class="row">
            <div class="col-6 mb-3">
                <select name="monthPicker" id="monthPicker" class="custom-select">
                    <?php
                    for ($mnth = 1; $mnth <= 12; $mnth++) {
                        $mnthName = date("F", mktime(0, 0, 0, $mnth, 1));
                        if ($mnth == $month) {
                            echo "<option value='$mnth' selected>$mnthName</option>";
                        } else {
                            echo "<option value='$mnth'>$mnthName</option>";
                        }
                    }
                    ?>
                </select>
            </div>
            <div class="col-6 mb-3">
                <select name="yearPicker" id="yearPicker" class="custom-select">
                    <?php
                    foreach (range($startYear, $endYear) as $x) {
                        if ($x == $year) {
                            echo "<option value='$x' selected>$x</option>";
                        } else {
                            echo "<option value='$x'>$x</option>";
                        }
                    }
                    ?>
                </select>
            </div>
        </div>

        <div class="row">
            <div class="col-12 incomeSection mb-5 mt-3">
                <p>Total Pendapatan:</p>
                <p><strong>{{ $monthly_income }}</strong></p>
                <br>
                <p style="font-size: 1em;">Target: <strong>{{ $target }}</strong></p>
            </div>
        </div>

        <div class="mb-5">
            <div class="col-12 incomeSection">
                <p>Penjualan Berdasarkan Kota</p>
            </div>
            <div class="col-12 mt-3">
                <p class="subtitle">Keberangkatan</p>
            </div>
            <div class="row col-12">
                <div class="col-12 col-lg-8">
                    <x-chartjs-component :chart="$penjualan_by_kota_departure_bar_chart" />
                </div>
                <div class="row col-12 col-lg-4 justify-content-center">
                    <x-chartjs-component :chart="$penjualan_by_kota_departure_pie_chart" />
                </div>
            </div>
            <hr class="dashed">
            <div class="col-12 mt-3">
                <p class="subtitle">Tujuan</p>
            </div>
            <div class=" row col-12">
                <div class="col-12 col-lg-8">
                    <x-chartjs-component :chart="$penjualan_by_kota_arrival_bar_chart" />
                </div>
                <div class="row col-12 col-lg-4 justify-content-center">
                    <x-chartjs-component :chart="$penjualan_by_kota_arrival_pie_chart" />
                </div>
            </div>
        </div>

        <div class="row mb-5">
            <div class="col-12 incomeSection">
                <p>Penjualan Berdasarkan Kelas</p>
            </div>
            <div class="row col-12">
                <div class="col-12 col-lg-8">
                    <x-chartjs-component :chart="$penjualan_by_kelas_bar_chart" />
                </div>
                <div class="row col-12 col-lg-4 justify-content-center">
                    <x-chartjs-component :chart="$penjualan_by_kelas_pie_chart" />
                </div>
            </div>
        </div>

        <div class="row mb-5">
            <div class="col-12 incomeSection">
                <p>Penjualan Berdasarkan Unit</p>
            </div>
            <div class="row col-12">
                <div class="col-12 col-lg-8">
                    <x-chartjs-component :chart="$penjualan_by_unit_bar_chart" />
                </div>
                <div class="row col-12 col-lg-4 justify-content-center">
                    <x-chartjs-component :chart="$penjualan_by_unit_pie_chart" />
                </div>
            </div>
        </div>

        <div class="row mb-5">
            <div class="col-12 incomeSection">
                <p>Bus Berangkat Per Tanggal</p>
            </div>
            <div class="col-12">
                @php $busByDate = $bus_by_date->groupBy('date'); @endphp
                @if($busByDate->isEmpty())
                    <p class="text-muted">Tidak ada data.</p>
                @else
                <table class="table table-bordered table-sm">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Bus</th>
                            <th>Jumlah Booking</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($busByDate as $date => $buses)
                            @foreach($buses as $bus)
                                <tr>
                                    @if($loop->first)
                                        <td rowspan="{{ $buses->count() }}">{{ date('d/m/Y', strtotime($date)) }}</td>
                                    @endif
                                    <td>{{ $bus->bus_name }}</td>
                                    <td class="text-center">{{ $bus->total }}</td>
                                </tr>
                            @endforeach
                        @endforeach
                    </tbody>
                </table>
                @endif
            </div>
        </div>

    </div>
</div>

@endsection

@section('script')
<script type="text/javascript">
    $(document).ready(function() {
        var currentYear = $('#yearPicker').find(':selected').val()
        var currentMonth = $('#monthPicker').find(':selected').val()

        $('#yearPicker').change(function() {
            currentYear = $(this).find(':selected').val()
            var currentUrl = location.href;
            var url = new URL(currentUrl);
            url.searchParams.set("year", currentYear);
            var newUrl = url.href;
            window.location.href = newUrl;
        });

        $('#monthPicker').change(function() {
            currentMonth = $(this).find(':selected').val()
            var currentUrl = location.href;
            var url = new URL(currentUrl);
            url.searchParams.set("month", currentMonth);
            var newUrl = url.href;
            window.location.href = newUrl;
        });
    });
</script>
@endsection