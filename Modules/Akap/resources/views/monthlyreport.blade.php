@extends('layouts.main', ['title' => $title ])

@section('content')
<?php
$date = (isset($_GET['date'])) ? new DateTime(date("Y-m", strtotime($_GET['date']))) : new DateTime(date("Y-m"));
$trip = (isset($_GET['trip'])) ? $_GET['trip'] : null;
$month = (isset($_GET['month'])) ? $_GET['month'] : date("n");
$year = (isset($_GET['year'])) ? $_GET['year'] : date("Y");
$startYear = date('Y') - 2;
$endYear = date('Y') + 1;
?>

<div class="row mb-2">
    <div class="col-12">
        <div class="row">
            <div class="col-md-6 col-12 mb-3">
                <select name="routeGroup" id="routeGroup" class="custom-select">
                    <option value="">Semua Rute</option>
                    <?php foreach ($route_group as $rg)
                        if ($trip == ($rg->id)) {
                            echo "<option value= " . $rg->id . " selected>" . $rg->name_x . "</option>";
                        } else {
                            echo "<option value= " . $rg->id . ">" . $rg->name_x . "</option>";
                        }
                    ?>
                </select>
            </div>
            <div class="col-md-3 col-6 mb-3">
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
            <div class="col-md-3 col-6 mb-3">
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
            <div class="col-lg-8 col-12 incomeSection mb-3 mt-3" style="position: relative;">
                <button type="button" class="btn btn-sm btn-outline-light btn-detail-monthly" style="position: absolute; top: 1rem; right: 1rem; border-width: 2px; color: #ffffff; font-weight: 700;" data-toggle="modal" data-target="#monthlyPriceModal">
                    Detail
                </button>
                <style>
                    .btn-detail-monthly:hover { color: #ff0000 !important; }
                </style>
                <p>Total Tiket Berangkat:</p>
                <p><strong>{{ $income }}</strong></p>
                <br>
                <p style="font-size: 1em;">Target: <strong>{{ $target }}</strong></p>
            </div>

            <!-- Monthly Price Modal -->
            <div class="modal fade" id="monthlyPriceModal" tabindex="-1" role="dialog" aria-labelledby="monthlyPriceModalLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="monthlyPriceModalLabel">Pendapatan Per Bulan Pembelian</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <table class="table table-bordered table-sm">
                                <thead>
                                    <tr>
                                        <th>Bulan</th>
                                        <th class="text-right">Total Pendapatan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($monthly_price as $year_month => $price)
                                    @php [$yr, $mn] = explode('-', $year_month) @endphp
                                    <tr>
                                        <td>{{ date("F", mktime(0, 0, 0, $mn, 1)) }} {{ $yr }}</td>
                                        <td class="text-right">{{ Number::currency($price, 'IDR') }}</td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="2" class="text-center">Tidak ada data</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-8 col-12 incomeSection mb-5" style="position: relative;">
                <button type="button" class="btn btn-sm btn-outline-light btn-detail-monthly" style="position: absolute; top: 1rem; right: 1rem; border-width: 2px; color: #ffffff; font-weight: 700;" data-toggle="modal" data-target="#sellingMonthlyModal">
                    Detail
                </button>
                <p>Total Penjualan Tiket:</p>
                <p><strong>{{ $selling }}</strong></p>
                <br>
            </div>

            <!-- Selling Monthly Modal -->
            <div class="modal fade" id="sellingMonthlyModal" tabindex="-1" role="dialog" aria-labelledby="sellingMonthlyModalLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="sellingMonthlyModalLabel">Penjualan Per Bulan Pembelian</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <table class="table table-bordered table-sm">
                                <thead>
                                    <tr>
                                        <th>Bulan</th>
                                        <th class="text-right">Total Penjualan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($selling_monthly as $year_month => $price)
                                    @php [$yr, $mn] = explode('-', $year_month) @endphp
                                    <tr>
                                        <td>{{ date("F", mktime(0, 0, 0, $mn, 1)) }} {{ $yr }}</td>
                                        <td class="text-right">{{ Number::currency($price, 'IDR') }}</td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="2" class="text-center">Tidak ada data</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-12 mb-5 mt-3 align-content-center">
                <div class="row col-12">
                    <div class="col-6 align-content-center m-0 p-0">
                        <x-chartjs-component :chart="$total_keterisian_kursi['chart']" />
                    </div>
                    <div class="col-6 align-content-center">
                        <p class="mb-0">Total Keterisian Seat:</p>
                        <p class="mb-0"><strong>{{ $total_keterisian_kursi['percentage'] }}</strong></p>
                        <p class="mb-0"><strong>{{ $total_keterisian_kursi['description'] }}</strong></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>

@endsection

@section('script')

@endsection