@extends('layouts.main', ['title' => $title ])

@section('content')

<?php
$dateStart = (isset($_GET['dateStart'])) ? new DateTime(date("Y-m-d", strtotime($_GET['dateStart']))) : new DateTime(date("Y-m-d"));
$dateEnd = (isset($_GET['dateEnd'])) ? new DateTime(date("Y-m-d", strtotime($_GET['dateEnd']))) : new DateTime(date("Y-m-d"));
$dateStart = $dateStart->format('Y-m-d');
$dateEnd = $dateEnd->format('Y-m-d');
?>

<div class="row mb-5">
    <div class="col-12 mb-3">
        <div class="input-group">
            <div class="input-group-prepend">
                <span class="input-group-text">
                    <i class="far fa-calendar-alt"></i>
                </span>
            </div>
            <input value="<?php echo ($dateStart . ' - ' . $dateEnd) ?>" type="text" class="form-control float-right" id="reservation">
        </div>
    </div>
    <div class="row col-12 mb-3">
        <div class="col-12 m-1">
            <h3>Total Pendapatan Semua Rute</h3>
        </div>
        <div class="col-12 incomeSection m-1">
            <p>Total Tiket Berangkat:</p>
            <p><strong>{{ $mainIncome }}</strong></p>
        </div>
        <div class="col-12 incomeSection m-1">
            <p>Total Penjualan Tiket:</p>
            <p><strong>{{ $mainSelling }}</strong></p>
        </div>
    </div>
    <div class="row col-12 mb-3">
        <div class="col-12 m-1">
            <h3>Total Pendapatan Setiap Rute</h3>
        </div>
        @foreach ($routeIncome as $value)
        <div class="col-lg-4 col-12 incomeSection m-1">
            <p>{{ $value->name }}:</p>
            <p><strong>{{ $value->income }}</strong></p>
        </div>
        @endforeach
    </div>
</div>

@endsection



@section('script')
<script type="text/javascript">
    $(document).ready(function() {
        $('#reservation').daterangepicker({
            locale: {
                format: 'YYYY-MM-DD'
            }
        }, function(start, end, label) {
            console.log("A new date selection was made: " + start.format('YYYY-MM-DD') + ' to ' + end.format('YYYY-MM-DD'));
            var dateStart = start.format('YYYY-MM-DD');
            var dateEnd = end.format('YYYY-MM-DD');
            var currentUrl = location.href;
            var url = new URL(currentUrl);
            url.searchParams.set("dateStart", dateStart);
            url.searchParams.set("dateEnd", dateEnd);
            var newUrl = url.href;
            window.location.href = newUrl;
        });
    });
</script>
@endsection