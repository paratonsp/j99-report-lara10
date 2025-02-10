@extends('layouts.main', ['title' => $title ])

@section('content')
<?php
$date = (isset($_GET['date'])) ? new DateTime(date("Y-m", strtotime($_GET['date']))) : new DateTime(date("Y-m"));
$trip = (isset($_GET['trip'])) ? $_GET['trip'] : null;

$month = (isset($_GET['month'])) ? $_GET['month'] : date("n");
$year = (isset($_GET['year'])) ? $_GET['year'] : date("Y");
$startYear = date('Y') - 1;
$endYear = date('Y') + 1;

// $interval = DateInterval::createFromDateString('1 month');
// $period  = new DatePeriod($start, $interval, $end);
// $formatter = new IntlDateFormatter('en_US', IntlDateFormatter::LONG, IntlDateFormatter::SHORT);
// $monthFormat = $formatter->setPattern('MMMM yyyy');
// $yearFormat = $formatter->setPattern('MMMM yyyy');
?>

<div class="row mb-2">
    <div class="col-12">
        <div class="row">
            <div class="col-md-6 col-12 mb-3">
                <select name="routeGroup" id="routeGroup" class="custom-select">
                    <option value="">Semua Rute</option>
                    <?php foreach ($route_group as $rg)
                        if ($trip == ($rg->start_point . 'N' . $rg->end_point)) {
                            echo "<option value= " . $rg->start_point . 'N' .  $rg->end_point . " selected>" . $rg->name . "</option>";
                        } else {
                            echo "<option value= " . $rg->start_point . 'N' .  $rg->end_point . ">" . $rg->name . "</option>";
                        }
                    ?>
                </select>
            </div>
            <div class="col-md-3 col-6">
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
            <div class="col-md-3 col-6">
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
        &nbsp;
        <div class="incomeSection">
            <p>Total Pendapatan: <strong>{{ $income }}</strong></p>
        </div>
        &nbsp;
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