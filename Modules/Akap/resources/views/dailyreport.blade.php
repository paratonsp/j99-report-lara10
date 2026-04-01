@extends('layouts.main', ['title' => $title ])

@section('content')

<div id="fetchLoadingOverlay" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.45); z-index:9999; align-items:center; justify-content:center; flex-direction:column; gap:12px;">
    <div class="spinner-border text-light" style="width:3rem; height:3rem;" role="status"></div>
    <span style="color:#fff; font-size:1rem; font-weight:600;">Memuat data...</span>
</div>

<div class="row mb-5">
    <div class="col-12 mb-3">
        <div class="input-group">
            <div class="input-group-prepend">
                <span class="input-group-text">
                    <i class="far fa-calendar-alt"></i>
                </span>
            </div>
            <input value="{{ $dateStart . ' - ' . $dateEnd }}" type="text" class="form-control float-right" id="reservation">
        </div>
    </div>
    <div class="row col-12 mb-3">
        <div class="col-12 m-1">
            <h3>Total Pendapatan Semua Rute</h3>
        </div>
        <div class="col-12 incomeSection m-1">
            <p>Total Tiket Berangkat:</p>
            <p><strong id="main-income-value">-</strong></p>
        </div>
        <div class="col-12 incomeSection m-1">
            <p>Total Penjualan Tiket:</p>
            <p><strong id="main-selling-value">-</strong></p>
        </div>
    </div>
    <div class="row col-12 mb-3" id="routeIncomeContainer">
        <div class="col-12 m-1">
            <h3>Total Pendapatan Setiap Rute</h3>
        </div>
        @foreach ($trip_route_grouped as $rg)
        <div class="col-lg-4 col-12 incomeSection m-1" id="route-income-{{ $rg->id }}">
            <p>{{ $rg->name_x }}:</p>
            <p><strong class="route-income-value">-</strong></p>
        </div>
        @endforeach
    </div>
</div>

@endsection

@section('script')
<script type="text/javascript">
    var reportData = {
        trip_route_grouped: @json($trip_route_grouped),
        dateStart: @json($dateStart),
        dateEnd: @json($dateEnd),
        trip: @json($trip),
        getTicket: [],
        getBuy: [],
    };

    var formatIDR = function (value) {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0,
        }).format(value);
    };

    var calcPrice = function (ticket) {
        return ticket.tp_price > 0 ? ticket.tp_price : (ticket.tb_price / Math.max(ticket.passenger_count_tb, 1));
    };

    var renderData = function () {
        // Build route ID -> income/selling maps
        var incomeByRoute  = {};
        var sellingByRoute = {};

        reportData.getTicket.forEach(function (t) {
            incomeByRoute[t.trip_route_id] = (incomeByRoute[t.trip_route_id] || 0) + calcPrice(t);
        });
        reportData.getBuy.forEach(function (t) {
            sellingByRoute[t.trip_route_id] = (sellingByRoute[t.trip_route_id] || 0) + calcPrice(t);
        });

        var mainIncome  = 0;
        var mainSelling = 0;

        reportData.trip_route_grouped.forEach(function (rg) {
            var routeIds = rg.route.map(Number);
            var income  = routeIds.reduce(function (s, id) { return s + (incomeByRoute[id]  || 0); }, 0);
            var selling = routeIds.reduce(function (s, id) { return s + (sellingByRoute[id] || 0); }, 0);
            mainIncome  += income;
            mainSelling += selling;

            var el = document.querySelector('#route-income-' + rg.id + ' .route-income-value');
            if (el) el.textContent = formatIDR(income);
        });

        document.getElementById('main-income-value').textContent  = formatIDR(mainIncome);
        document.getElementById('main-selling-value').textContent = formatIDR(mainSelling);
    };

    var qs = '?dateStart=' + reportData.dateStart + '&dateEnd=' + reportData.dateEnd + (reportData.trip ? '&trip=' + reportData.trip : '');

    var loadingOverlay = document.getElementById('fetchLoadingOverlay');
    loadingOverlay.style.display = 'flex';

    Promise.all([
        fetch('/akap/harian/tickets' + qs).then(function (r) { return r.json(); }),
        fetch('/akap/harian/buy'     + qs).then(function (r) { return r.json(); }),
    ]).then(function (results) {
        reportData.getTicket = results[0];
        reportData.getBuy    = results[1];
        renderData();
        loadingOverlay.style.display = 'none';
    }).catch(function (err) {
        loadingOverlay.style.display = 'none';
        console.error('Failed to load ticket data', err);
    });

    $(document).ready(function() {
        $('#reservation').daterangepicker({
            locale: { format: 'YYYY-MM-DD' },
            startDate: '{{ $dateStart }}',
            endDate:   '{{ $dateEnd }}',
        }, function(start, end) {
            var url = new URL(location.href);
            url.searchParams.set("dateStart", start.format('YYYY-MM-DD'));
            url.searchParams.set("dateEnd",   end.format('YYYY-MM-DD'));
            window.location.href = url.href;
        });
    });
</script>
@endsection