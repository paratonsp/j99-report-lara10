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
        <div class="incomeSection mb-5">
            <p>Total Pendapatan: <strong>{{ $income }}</strong></p>
        </div>



        <div class="row">
            <div class="col-md-6 col-12 mb-3">
                <h3>Jadwal Buka Sementara</h3>
                <table id="datatable" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Kendala</th>
                            <th>Awal</th>
                            <th>Akhir</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($trip_assign_open as $key => $item)
                        <tr>
                            <td width="20" class="text-center">{{ intval($key) + 1 }}</td>
                            <td>{{ $item->causes }}</td>
                            <td>{{ $item->date }}</td>
                            <td>{{ $item->date_finish }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="col-md-6 col-12 mb-3">
                <h3>Jadwal Tutup Sementara</h3>
                <table id="datatable" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Kendala</th>
                            <th>Awal</th>
                            <th>Akhir</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($trip_assign_close as $key => $item)
                        <tr>
                            <td width="20" class="text-center">{{ intval($key) + 1 }}</td>
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
        var currentDate = $('#yearPicker').find(':selected').val()
        var currentDate = $('#monthPicker').find(':selected').val()
        var currentTrip = $('#routeGroup').find(":selected").val();

        $('#yearPicker').change(function() {
            currentDate = $(this).find(':selected').val()
            var currentUrl = location.href;
            var url = new URL(currentUrl);
            url.searchParams.set("year", currentDate);
            var newUrl = url.href;
            window.location.href = newUrl;
        });

        $('#monthPicker').change(function() {
            currentDate = $(this).find(':selected').val()
            var currentUrl = location.href;
            var url = new URL(currentUrl);
            url.searchParams.set("month", currentDate);
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
    });
</script>
@endsection