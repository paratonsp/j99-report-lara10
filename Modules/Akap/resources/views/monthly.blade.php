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
            <div class="col-lg-8 col-12 incomeSection mb-5 mt-3">
                <p>Total Pendapatan:</p>
                <p><strong>{{ $income }}</strong></p>
                <br>
                <p style="font-size: 1em;">Target: <strong>{{ $target }}</strong></p>
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


        <div class="row mb-5">
            <div class="col-12 incomeSection">
                <p>Daily Passengger</p>
            </div>
            <div class="col-12">
                <x-chartjs-component :chart="$daily_passengger" />
            </div>
        </div>

        <div class="row mb-5" <?php if (isset($trip)) echo "hidden" ?>>
            <div class="col-12 incomeSection">
                <p>Occupancy By Route</p>
            </div>
            <div class="col-12 mb-3">
                <x-chartjs-component :chart="$occupancy_by_route_bar" />
            </div>

            <div class="row col-12 justify-content-center">
                @foreach ($occupancy_by_route_doughnut as $key => $item)
                <div class="col-4 col-md-3 col-lg-2 mb-3" style="justify-items: center;">
                    <x-chartjs-component :chart="$item['chart']" />
                    <p class="mb-0"><strong>{{$item['percentage']}}</strong></p>
                    <p class="mb-0">{{$item['label']}}</p>
                </div>
                @endforeach
            </div>
        </div>

        <div class="row mb-5">
            <div class="col-12 incomeSection">
                <p>Occupancy By Bus</p>
            </div>
            <div class="col-12 mb-3">
                <x-chartjs-component :chart="$occupancy_by_bus_bar" />
            </div>

            <div class="row col-12 justify-content-center">
                @foreach ($occupancy_by_bus_doughnut as $key => $item)
                <div class="col-4 col-md-3 col-lg-2 mb-3" style="justify-items: center;">
                    <x-chartjs-component :chart="$item['chart']" />
                    <p class="mb-0"><strong>{{$item['percentage']}}</strong></p>
                    <p class="mb-0">{{$item['label']}}</p>
                </div>
                @endforeach
            </div>
        </div>

        <div class="row mb-5">
            <div class="col-12 incomeSection">
                <p>Occupancy By Class</p>
            </div>
            <div class="col-12 mb-3">
                <x-chartjs-component :chart="$occupancy_by_class_bar" />
            </div>

            <div class="row col-12 justify-content-center">
                @foreach ($occupancy_by_class_doughnut as $key => $item)
                <div class="col-4 col-md-3 col-lg-2 mb-3" style="justify-items: center;">
                    <x-chartjs-component :chart="$item['chart']" />
                    <p class="mb-0"><strong>{{$item['percentage']}}</strong></p>
                    <p class="mb-0">{{$item['label']}}</p>
                </div>
                @endforeach
            </div>
        </div>

        <div class="row mb-5">
            <div class="col-12 incomeSection">
                <p>Occupancy Rate</p>
            </div>
            <div class="col-12 mt-2">
                <table id="occupancyRateTable" class="table table-bordered table-striped occupancyRateTable nowrap">
                    <thead>
                        <tr id="occupacyRateDate">
                            <th rowspan="2" colspan="1">
                                Armada
                            </th>
                            <th rowspan="2" colspan="1">
                                Trip
                            </th>
                        </tr>
                        <tr id="occupacyRateDetail">

                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($occupancy_rate as $key => $item)
                        <tr>
                            <td>{{ $item['bus'] }}</td>
                            <td>{{ $item['trip'] }}</td>
                            @foreach ($item['data'] as $key => $itemData)
                            <td>{{ $itemData['occupancy'] }}</td>
                            <td>{{ $itemData['seat_sale'] }}</td>
                            <td>{{ $itemData['max_seat'] }}</td>
                            @endforeach
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mb-5">
            <div class="col-12 incomeSection">
                <p>Perbandingan Titik Naik</p>
            </div>
            <div class="col-12 mt-3">
                <p class="subtitle">Keberangkatan</p>
            </div>
            <div class="row col-12">
                <div class="col-12 col-lg-6">
                    <x-chartjs-component :chart="$perbandingan_titik_naik_departure_bar_chart" />
                </div>
                <div class="row col-12 col-lg-6 justify-content-center">
                    @foreach ($perbandingan_titik_naik_departure_doughnut_chart as $key => $item)
                    <div class="col-4 col-md-3 mb-3" style="justify-items: center;">
                        <x-chartjs-component :chart="$item['chart']" />
                        <p class="mb-0"><strong>{{$item['percentage']}}</strong></p>
                        <p class="mb-0">{{$item['label']}}</p>
                    </div>
                    @endforeach
                </div>
            </div>
            <hr class="dashed">
            <div class="col-12 mt-3">
                <p class="subtitle">Kedatangan</p>
            </div>
            <div class=" row col-12">
                <div class="col-12 col-lg-6">
                    <x-chartjs-component :chart="$perbandingan_titik_naik_arrival_bar_chart" />
                </div>
                <div class="row col-12 col-lg-6 justify-content-center">
                    @foreach ($perbandingan_titik_naik_arrival_doughnut_chart as $key => $item)
                    <div class="col-4 col-md-3 mb-3" style="justify-items: center;">
                        <x-chartjs-component :chart="$item['chart']" />
                        <p class="mb-0"><strong>{{$item['percentage']}}</strong></p>
                        <p class="mb-0">{{$item['label']}}</p>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="row mb-5">
            <div class="col-12 incomeSection">
                <p>Ticketing Support</p>
            </div>
            <div class="col-12">
                <x-chartjs-component :chart="$ticketing_support_bar" />
            </div>

            <div class="row col-12 justify-content-center">
                @foreach ($ticketing_support_doughnut as $key => $item)
                <div class="col-3" style="justify-items: center;">
                    <x-chartjs-component :chart="$item['chart']" />
                    <p class="mb-0"><strong>{{$item['percentage']}}</strong></p>
                    <p class="mb-0">{{$item['label']}}</p>
                </div>
                @endforeach
            </div>
        </div>

        <div class="row mb-5">
            <div class="col-12 incomeSection">
                <p>Perbandingan Bulan Lalu</p>
            </div>
            <div class="col-12">
                <x-chartjs-component :chart="$perbandingan_bulan_lalu_chart" />
            </div>
            <div class="row col-12 mt-3">
                <div class="col-6">
                    <p class="perbandingan-bulan-lalu">Total Keterisian Seat {{$perbandingan_bulan_lalu_last_month['month']}} : {{$perbandingan_bulan_lalu_last_month['seat']}}</p>
                </div>
                <div class="col-6">
                    <p class="perbandingan-bulan-lalu">Total Pendapatan {{$perbandingan_bulan_lalu_last_month['month']}} : {{$perbandingan_bulan_lalu_last_month['income']}}</p>
                </div>
            </div>
            <div class="row col-12 mt-3">
                <div class="col-6">
                    <p class="perbandingan-bulan-lalu">Total Keterisian Seat {{$perbandingan_bulan_lalu_current_month['month']}} : {{$perbandingan_bulan_lalu_current_month['seat']}}</p>
                </div>
                <div class="col-6">
                    <p class="perbandingan-bulan-lalu">Total Pendapatan {{$perbandingan_bulan_lalu_current_month['month']}} : {{$perbandingan_bulan_lalu_current_month['income']}}</p>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 col-12 mb-3">
                <div class="col-12 incomeSection">
                    <p>Jadwal Buka Sementara</p>
                </div>
                <table id="datatable" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Trip</th>
                            <th>Kendala</th>
                            <th>Awal</th>
                            <th>Akhir</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($trip_assign_open as $key => $item)
                        <tr>
                            <td width="20" class="text-center">{{ intval($key) + 1 }}</td>
                            <td>{{ $item->reg_no }}</td>
                            <td>{{ $item->causes }}</td>
                            <td>{{ $item->date }}</td>
                            <td>{{ $item->date_finish }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="col-md-6 col-12 mb-3">
                <div class="col-12 incomeSection">
                    <p>Jadwal Tutup Sementara</p>
                </div>
                <table id="datatable" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Trip</th>
                            <th>Kendala</th>
                            <th>Awal</th>
                            <th>Akhir</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($trip_assign_close as $key => $item)
                        <tr>
                            <td width="20" class="text-center">{{ intval($key) + 1 }}</td>
                            <td>{{ $item->reg_no }}</td>
                            <td>{{ $item->causes }}</td>
                            <td>{{ $item->date }}</td>
                            <td>{{ $item->date_finish }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
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
        var currentTrip = $('#routeGroup').find(":selected").val();

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

        $('#routeGroup').change(function() {
            currentTrip = $(this).find(':selected').val()

            var currentUrl = location.href;
            var url = new URL(currentUrl);
            if (currentTrip == "") {
                url.searchParams.delete("trip");
            } else {
                url.searchParams.set("trip", currentTrip);
            }
            var newUrl = url.href;
            window.location.href = newUrl;
        });

        const getDays = (year, month) => new Date(year, month, 0).getDate()

        const days = getDays(currentYear, currentMonth)

        var occupacyRateDate = "";
        for (var j = 0; j < days; j++) {
            var d = j + 1;
            occupacyRateDate += '<th rowspan="1" colspan="3">' + d + '</th>';
        }

        var occupacyRateDetail = "";
        for (var j = 0; j < days; j++) {
            var d = j + 1;
            occupacyRateDetail += '<th>% Occup</th>';
            occupacyRateDetail += '<th>Ticket Sold</th>';
            occupacyRateDetail += '<th>Max Seat</th>';
        }

        $("#occupacyRateDate").append(occupacyRateDate);
        $("#occupacyRateDetail").append(occupacyRateDetail);

        $('#occupancyRateTable').DataTable({
            "scrollX": true,
            "scrollY": '70vh',
            "responsive": false,
            "paging": false,
            "ordering": true,
            "searching": true,
            "fixedColumns": {
                "leftColumns": 2
            },
            "scrollCollapse": true,
            "columnDefs": [{
                    "className": "dt-center",
                    "targets": "_all"
                },
                {
                    "targets": 1,
                    "width": 1
                }
            ],
            "buttons": ["copy", "csv", "excel", "pdf", "print"]
        }).buttons().container().appendTo('#occupancyRateTable_wrapper .col-md-6:eq(0)');
    });
</script>
@endsection