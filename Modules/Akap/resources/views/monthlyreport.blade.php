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

<div id="fetchLoadingOverlay" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.45); z-index:9999; align-items:center; justify-content:center; flex-direction:column; gap:12px;">
    <div class="spinner-border text-light" style="width:3rem; height:3rem;" role="status"></div>
    <span style="color:#fff; font-size:1rem; font-weight:600;">Memuat data...</span>
</div>

<div class="row mb-2">
    <div class="col-12">
        {{-- section 1 --}}
        <div>
            <div class="row">
                <div class="col-md-6 col-12 mb-3">
                    <select name="routeGroup" id="routeGroup" class="custom-select">
                        <option value="">Semua Rute</option>
                        <?php foreach ($trip_route_grouped as $rg)
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
                    <p><strong id="income-value"></strong></p>
                    <br>
                    <p style="font-size: 1em;">Target: <strong id="target-value"></strong></p>
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
                                    <tbody id="monthlyPriceModalBody">
                                        <tr>
                                            <td colspan="2" class="text-center">Tidak ada data</td>
                                        </tr>
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
                    <p><strong id="selling-value"></strong></p>
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
                                    <tbody id="sellingMonthlyModalBody">
                                        <tr>
                                            <td colspan="2" class="text-center">Tidak ada data</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-12 mb-5 mt-3 align-content-center">
                    <div class="row col-12">
                        <div class="col-6 align-content-center m-0 p-0">
                            <canvas id="occupancyChart"></canvas>
                        </div>
                        <div class="col-6 align-content-center">
                            <p class="mb-0">Total Keterisian Seat:</p>
                            <p class="mb-0"><strong id="total-seat-percentage"></strong></p>
                            <p class="mb-0"><strong id="total-seat-description"></strong></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- section 2  --}}
        <div class="row mb-5">
            <div class="col-12 incomeSection">
                <p>Daily Passengger</p>
            </div>
            <div class="col-12">
                <canvas id="dailyPassenggerChart"></canvas>
            </div>
        </div>

        {{-- section 3 --}}
        <div class="row mb-5" <?php if (isset($trip)) echo "hidden" ?>>
            <div class="col-12 incomeSection">
                <p>Occupancy By Route</p>
            </div>
            <div class="col-12 mb-3">
                <canvas id="occupancyByRouteBarChart"></canvas>
            </div>
            <div class="row col-12 justify-content-center" id="occupancyByRouteDoughnutContainer"></div>
        </div>

        {{-- section 4 --}}
        
        {{-- section 5 --}}
        <div class="row mb-5">
            <div class="col-12 incomeSection">
                <p>Occupancy By Class</p>
            </div>
            <div class="col-12 mb-3">
                <canvas id="occupancyByClassBarChart"></canvas>
            </div>
            <div class="row col-12 justify-content-center" id="occupancyByClassDoughnutContainer"></div>
        </div>

        {{-- section 6 --}}
        <div class="row mb-5">
            <div class="col-12 incomeSection">
                <p>Occupancy Rate</p>
            </div>
            <div class="col-12 mt-2">
                <table id="occupancyRateTable" class="table table-bordered table-striped occupancyRateTable nowrap">
                    <thead>
                        <tr id="occupacyRateDate">
                            <th rowspan="2" colspan="1">Armada</th>
                            <th rowspan="2" colspan="1">Trip</th>
                        </tr>
                        <tr id="occupacyRateDetail"></tr>
                    </thead>
                    <tbody id="occupancyRateBody"></tbody>
                </table>
            </div>
            <div class="col-12 pt-1">
                <div class="d-flex align-items-center mb-2">
                    <span style="display:inline-block; width:32px; height:16px; background-color:#fffde7; border:1px solid #ccc; margin-right:6px;"></span>
                    <small>Hari Off</small>
                </div>
            </div>
        </div>

        {{-- section 7 --}}
        <div class="mb-5">
            <div class="col-12 incomeSection">
                <p>Perbandingan Titik Naik</p>
            </div>
            <div class="col-lg-12 col-12 mb-3">
                <div class="col-12 mt-3">
                    <p class="subtitle">Titik Naik (Departure)</p>
                </div>
                <canvas id="titikNaikChart"></canvas>
            </div>
            <hr class="dashed">
            <div class="col-lg-12 col-12 mb-3">
                <div class="col-12 mt-3">
                    <p class="subtitle">Titik Turun (Arrival)</p>
                </div>
                <canvas id="titikTurunChart"></canvas>
            </div>
        </div>

        {{-- section 8 --}}
        <div class="row mb-5">
            <div class="col-12 incomeSection">
                <p>Ticketing Support</p>
            </div>
            <br>
            <div class="col-lg-8 col-12">
                <x-chartjs-component :chart="$ticketing_support_bar" />
            </div>
            <div class="col-lg-4 col-12">
                <x-chartjs-component :chart="$ticketing_support_pie_chart" />
            </div>
        </div>

        {{-- section 9  --}}
        <div class="row mb-5">
            <div class="col-12 incomeSection">
                <p>Perbandingan Bulan Lalu</p>
            </div>
            <div class="col-12 mt-2">
                <canvas id="perbandinganBulanLaluChart"></canvas>
            </div>
            <div class="row col-12 mt-3">
                <div class="col-6">
                    <p class="perbandingan-bulan-lalu">Total Tiket Bulan Lalu: <strong id="prev-month-seat"></strong></p>
                </div>
                <div class="col-6">
                    <p class="perbandingan-bulan-lalu">Total Pendapatan Bulan Lalu: <strong id="prev-month-income"></strong></p>
                </div>
            </div>
            <div class="row col-12 mt-3">
                <div class="col-6">
                    <p class="perbandingan-bulan-lalu">Total Tiket Bulan Ini: <strong id="curr-month-seat"></strong></p>
                </div>
                <div class="col-6">
                    <p class="perbandingan-bulan-lalu">Total Pendapatan Bulan Ini: <strong id="curr-month-income"></strong></p>
                </div>
            </div>
        </div>

        {{-- section 10 --}}
        <div class="row">
            <div class="col-md-6 col-12 mb-3">
                <div class="col-12 incomeSection">
                    <p>Jadwal Buka Sementara</p>
                </div>
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Armada</th>
                            <th>Kendala</th>
                            <th>Awal</th>
                            <th>Akhir</th>
                        </tr>
                    </thead>
                    <tbody id="tempOnTableBody"></tbody>
                </table>
            </div>
            <div class="col-md-6 col-12 mb-3">
                <div class="col-12 incomeSection">
                    <p>Jadwal Tutup Sementara</p>
                </div>
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Armada</th>
                            <th>Kendala</th>
                            <th>Awal</th>
                            <th>Akhir</th>
                        </tr>
                    </thead>
                    <tbody id="tempOffTableBody"></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@endsection

@section('script')
<script>
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
    /**
     * @typedef {Object} TripRouteGrouped
     * @property {number} id
     * @property {string} name_x
     * @property {string} name_y
     * @property {string} route_x
     * @property {string} route_y
     * @property {string} name
     * @property {string[]} route
     */

    /**
     * @typedef {Object} ClassInfo
     * @property {number} trip_route_id
     * @property {string} trip
     * @property {number} fleet_registration_id
     * @property {number} status
     * @property {number} tras_id
     * @property {string} assign_time
     * @property {string} bus
     * @property {string} registration
     * @property {number} fleet_type
     * @property {string} type
     * @property {number} total_seat
     * @property {number} days_active
     */

    /**
     * @typedef {Object} Ticket
     * @property {string} booking_code
     * @property {string} ticket_number
     * @property {number} passenger_count_tb
     * @property {number} tb_price
     * @property {number} tp_price
     * @property {string} seat_number
     * @property {number} tp_cancel
     * @property {string} departure_date
     * @property {string} pickup_trip_location
     * @property {string} drop_trip_location
     * @property {string} type
     * @property {number} type_id
     * @property {number} trip_route_id
     * @property {string} trip_id_no
     * @property {string} tras_id
     * @property {string} buy_date
     */

    /**
     * @typedef {Object} reportData
     * @property {TripRouteGrouped[]} trip_route_grouped
     * @property {number[]} trip_group
     * @property {number[]} trip_assign_group
     * @property {number} total_days
     * @property {string} month
     * @property {string} year
     * @property {ClassInfo[]} class_info
     * @property {Ticket[]} getTicket
     * @property {Ticket[]} getBuy
     * @property {number} target
     * @property {Ticket[]} getTicketPrevMonth
     * @property {{ tras_id: number, fleet_registration_id: number, date: string, date_finish: string, route: number, bus: string, registration: string, causes: string }[]} class_temp_off
     * @property {{ tras_id: number, fleet_registration_id: number, date: string, date_finish: string, route: number, bus: string, registration: string, causes: string }[]} class_temp_on
     */

    /** @type {reportData} */
    var reportData = {
        trip_route_grouped: @json($trip_route_grouped),
        trip_group: @json($trip_group),
        trip_assign_group: @json($trip_assign_group),
        total_days: @json($total_days),
        month: @json($month),
        year: @json($year),
        class_info: @json($class_info ?? []),
        getTicket: [],
        getBuy: [],
        target: @json($target ?? 0),
        getTicketPrevMonth: [],
        trip: @json($trip),
        class_temp_off: @json($class_temp_off ?? []),
        class_temp_on: @json($class_temp_on ?? []),
    };

    var ticketUrl = '/akap/bulanan/tickets?month=' + reportData.month + '&year=' + reportData.year + (reportData.trip ? '&trip=' + reportData.trip : '');

    var loadingOverlay = document.getElementById('fetchLoadingOverlay');
    loadingOverlay.style.display = 'flex';

    fetch(ticketUrl)
        .then(function (r) { return r.json(); })
        .then(function (ticketData) {
            reportData.getTicket = ticketData.getTicket;
            reportData.getBuy = ticketData.getBuy;
            reportData.getTicketPrevMonth = ticketData.getTicketPrevMonth;

    // console.log(reportData);

    var calcDaysOff = function (fleet_registration_id, tras_id) {
        return reportData.class_temp_off.reduce(function (total, off) {
            if (off.fleet_registration_id !== fleet_registration_id || off.tras_id !== tras_id) return total;
            var start = new Date(off.date);
            var finish = new Date(off.date_finish);
            var days = 0;
            for (var d = new Date(start); d <= finish; d.setDate(d.getDate() + 1)) {
                days++;
            }
            return total + days;
        }, 0);
    };

    var classInfoGrouped = Object.values(reportData.class_info.reduce(function (acc, item) {
        var key = item.bus;
        if (!acc[key]) {
            var daysOff = calcDaysOff(item.fleet_registration_id, item.tras_id);
            var effectiveDays = Math.max(item.days_active - daysOff, 0);
            acc[key] = {
                bus: item.bus,
                trip: item.trip,
                registration: item.registration,
                trip_route_id: item.trip_route_id,
                fleet_registration_id: item.fleet_registration_id,
                status: item.status,
                tras_id: item.tras_id,
                assign_time: item.assign_time,
                days_active: effectiveDays,
                days_off: daysOff,
                total_seat: 0,
                total_seat_month: 0,
                classes: [],
            };
        }
        acc[key].total_seat += item.total_seat;
        acc[key].total_seat_month = acc[key].total_seat * acc[key].days_active;
        acc[key].classes.push({
            fleet_type: item.fleet_type,
            type: item.type,
            total_seat: item.total_seat,
        });
        return acc;
    }, {}));

    // Group class_info by type, accumulating total_seat * effective days_active
    var classInfoByType = Object.values(reportData.class_info.reduce(function (acc, item) {
        var key = item.type;
        if (!acc[key]) {
            acc[key] = {
                type: item.type,
                fleet_type: item.fleet_type,
                total_seat: 0,
                total_seat_month: 0,
            };
        }
        var daysOff = calcDaysOff(item.fleet_registration_id, item.tras_id);
        var effectiveDays = Math.max(item.days_active - daysOff, 0);
        acc[key].total_seat += item.total_seat;
        acc[key].total_seat_month += item.total_seat * effectiveDays;
        return acc;
    }, {}));

    // console.log('classInfoGrouped',classInfoGrouped);
    // console.log('classInfoByType',classInfoByType);

    var incomeTotal = reportData.getTicket.reduce(function (sum, ticket) {
        var price = (ticket.tp_price > 0) ? ticket.tp_price : (ticket.tb_price / ticket.passenger_count_tb);
        return sum + price;
    }, 0);

    var formatIDR = function (value) {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0,
        }).format(value);
    };

    var monthNames = ['January','February','March','April','May','June','July','August','September','October','November','December'];

    var groupedByBuyMonth = reportData.getTicket.reduce(function (acc, ticket) {
        var d = new Date(ticket.buy_date);
        var key = d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0');
        var price = ticket.tp_price > 0 ? ticket.tp_price : (ticket.tb_price / ticket.passenger_count_tb);
        acc[key] = (acc[key] || 0) + price;
        return acc;
    }, {});

    var sortedKeys = Object.keys(groupedByBuyMonth).sort();
    var modalBody = document.getElementById('monthlyPriceModalBody');

    if (sortedKeys.length === 0) {
        modalBody.innerHTML = '<tr><td colspan="2" class="text-center">Tidak ada data</td></tr>';
    } else {
        modalBody.innerHTML = sortedKeys.map(function (key) {
            var parts = key.split('-');
            var monthLabel = monthNames[parseInt(parts[1], 10) - 1] + ' ' + parts[0];
            return '<tr><td>' + monthLabel + '</td><td class="text-right">' + formatIDR(groupedByBuyMonth[key]) + '</td></tr>';
        }).join('');
    }

    var groupedByDepartureDateMonthSelling = reportData.getBuy.reduce(function (acc, ticket) {
        var d = new Date(ticket.departure_date);
        var key = d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0');
        var price = ticket.tp_price > 0 ? ticket.tp_price : (ticket.tb_price / ticket.passenger_count_tb);
        acc[key] = (acc[key] || 0) + price;
        return acc;
    }, {});

    var sortedSellingKeys = Object.keys(groupedByDepartureDateMonthSelling).sort();
    var sellingModalBody = document.getElementById('sellingMonthlyModalBody');

    if (sortedSellingKeys.length === 0) {
        sellingModalBody.innerHTML = '<tr><td colspan="2" class="text-center">Tidak ada data</td></tr>';
    } else {
        sellingModalBody.innerHTML = sortedSellingKeys.map(function (key) {
            var parts = key.split('-');
            var monthLabel = monthNames[parseInt(parts[1], 10) - 1] + ' ' + parts[0];
            return '<tr><td>' + monthLabel + '</td><td class="text-right">' + formatIDR(groupedByDepartureDateMonthSelling[key]) + '</td></tr>';
        }).join('');
    }

    var sellingTotal = reportData.getBuy.reduce(function (sum, ticket) {
        var price = ticket.tp_price > 0 ? ticket.tp_price : (ticket.tb_price / ticket.passenger_count_tb);
        return sum + price;
    }, 0);

    var totalSeatMonthAll = classInfoGrouped.reduce(function (sum, bus) { return sum + bus.total_seat_month; }, 0);

    document.getElementById('income-value').textContent = formatIDR(incomeTotal);
    document.getElementById('selling-value').textContent = formatIDR(sellingTotal);
    var occupancyPercentage = totalSeatMonthAll > 0
        ? ((reportData.getTicket.length / totalSeatMonthAll) * 100).toFixed(2) + '%'
        : '0%';

    document.getElementById('total-seat-percentage').textContent = occupancyPercentage;

    var filled = reportData.getTicket.length;
    var remaining = Math.max(totalSeatMonthAll - filled, 0);
    new Chart(document.getElementById('occupancyChart'), {
        type: 'doughnut',
        data: {
            labels: ['Terisi', 'Kosong'],
            datasets: [{
                data: [filled, remaining],
                backgroundColor: ['#ff0000', '#444444'],
                borderWidth: 0,
            }],
        },
        options: {
            cutout: '70%',
            legend: { display: false },
            plugins: {
                legend: { display: false },
                tooltip: { enabled: true },
            },
        },
    });
    document.getElementById('total-seat-description').textContent = reportData.getTicket.length + ' / ' + totalSeatMonthAll + ' seat bulan ini';
    document.getElementById('target-value').textContent = reportData.target === '-'
        ? 'Target belum disetting'
        : formatIDR(reportData.target);

    var dailyMap = reportData.getTicket.reduce(function (acc, ticket) {
        var day = new Date(ticket.departure_date).getDate();
        acc[day] = (acc[day] || 0) + ticket.passenger_count_tb;
        return acc;
    }, {});

    var dailyLabels = Array.from({ length: reportData.total_days }, function (_, i) { return i + 1; });
    var dailyData = dailyLabels.map(function (d) { return dailyMap[d] || 0; });

    new Chart(document.getElementById('dailyPassenggerChart'), {
        type: 'line',
        data: {
            labels: dailyLabels,
            datasets: [{
                label: 'Penumpang',
                data: dailyData,
                borderColor: '#ff0000',
                borderRadius: 3,
                fill: false,
            }],
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false },
            },
            scales: {
                x: { title: { display: true, text: 'Tanggal' } },
                y: { beginAtZero: true, title: { display: true, text: 'Penumpang' }, ticks: { stepSize: 1 } },
            },
        },
    });

    // Section 3: Occupancy By Route
    var routeTicketMap = reportData.getTicket.reduce(function (acc, ticket) {
        acc[ticket.trip_route_id] = (acc[ticket.trip_route_id] || 0) + ticket.passenger_count_tb;
        return acc;
    }, {});

    var routeLabels = [];
    var routeCounts = [];
    var routeCapacities = [];
    reportData.trip_route_grouped.forEach(function (rg) {
        var routeIds = rg.route.map(Number);
        var count = routeIds.reduce(function (sum, routeId) {
            return sum + (routeTicketMap[routeId] || 0);
        }, 0);
        var capacity = reportData.class_info.reduce(function (sum, ci) {
            return routeIds.indexOf(ci.trip_route_id) !== -1 ? sum + (ci.total_seat * ci.days_active) : sum;
        }, 0);
        routeLabels.push(rg.name);
        routeCounts.push(count);
        routeCapacities.push(capacity);
    });

    new Chart(document.getElementById('occupancyByRouteBarChart'), {
        type: 'bar',
        data: {
            labels: routeLabels,
            datasets: [
                {
                    label: 'Sisa',
                    data: routeCapacities.map(function (cap, i) { return Math.max(cap - routeCounts[i], 0); }),
                    backgroundColor: '#444444',
                    borderRadius: 3,
                },
                {
                    label: 'Terisi',
                    data: routeCounts,
                    backgroundColor: '#ff0000',
                    borderRadius: 3,
                },
            ],
        },
        options: {
            responsive: true,
            plugins: { legend: { display: true } },
            scales: {
                y: { beginAtZero: true, ticks: { stepSize: 1 } },
            },
        },
    });

    var doughnutContainer = document.getElementById('occupancyByRouteDoughnutContainer');
    var doughnutColors = ['#ff0000','#ff6600','#ffcc00','#00cc66','#0066ff','#9900cc','#ff0099','#00ccff'];

    routeLabels.forEach(function (label, i) {
        var count = routeCounts[i];
        var capacity = routeCapacities[i];
        var pct = capacity > 0 ? ((count / capacity) * 100).toFixed(1) + '%' : '0%';
        var remaining = Math.max(capacity - count, 0);
        var canvasId = 'routeDoughnut_' + i;

        var col = document.createElement('div');
        col.className = 'col-4 col-md-3 col-lg-2 mb-3';
        col.style.justifyItems = 'center';
        col.innerHTML = '<canvas id="' + canvasId + '"></canvas>'
            + '<p class="mb-0 text-center"><strong>' + pct + '</strong></p>'
            + '<p class="mb-0 text-center" style="font-size:0.8em;">' + label + '</p>';
        doughnutContainer.appendChild(col);

        new Chart(document.getElementById(canvasId), {
            type: 'doughnut',
            data: {
                labels: [label, 'Lainnya'],
                datasets: [{
                    data: [count, remaining],
                    backgroundColor: [doughnutColors[i % doughnutColors.length], '#444444'],
                    borderWidth: 0,
                }],
            },
            options: {
                cutout: '70%',
                legend: false,
                plugins: { legend: { display: false } },
            },
        });
    });

    // Section: Occupancy By Class
    var classTicketMap = reportData.getTicket.reduce(function (acc, ticket) {
        acc[ticket.type] = (acc[ticket.type] || 0) + ticket.passenger_count_tb;
        return acc;
    }, {});

    var classLabels = classInfoByType.map(function (c) { return c.type; });
    var classCounts = classInfoByType.map(function (c) { return classTicketMap[c.type] || 0; });
    var classCapacities = classInfoByType.map(function (c) { return c.total_seat_month; });

    new Chart(document.getElementById('occupancyByClassBarChart'), {
        type: 'bar',
        data: {
            labels: classLabels,
            datasets: [
                {
                    label: 'Terisi',
                    data: classCounts,
                    backgroundColor: '#ff0000',
                    borderRadius: 3,
                },
                {
                    label: 'Sisa',
                    data: classCapacities.map(function (cap, i) { return Math.max(cap - classCounts[i], 0); }),
                    backgroundColor: '#444444',
                    borderRadius: 3,
                },
            ],
        },
        options: {
            responsive: true,
            plugins: { legend: { display: true } },
            scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } },
        },
    });

    var classColors = ['#ff0000','#ff6600','#ffcc00','#00cc66','#0066ff','#9900cc','#ff0099','#00ccff'];
    var classDoughnutContainer = document.getElementById('occupancyByClassDoughnutContainer');

    classInfoByType.forEach(function (cls, i) {
        var count = classTicketMap[cls.type] || 0;
        var capacity = cls.total_seat_month;
        var pct = capacity > 0 ? ((count / capacity) * 100).toFixed(1) + '%' : '0%';
        var remaining = Math.max(capacity - count, 0);
        var canvasId = 'classDoughnut_' + i;

        var col = document.createElement('div');
        col.className = 'col-4 col-md-3 col-lg-2 mb-3';
        col.style.justifyItems = 'center';
        col.innerHTML = '<canvas id="' + canvasId + '"></canvas>'
            + '<p class="mb-0 text-center"><strong>' + pct + '</strong></p>'
            + '<p class="mb-0 text-center" style="font-size:0.8em;">' + cls.type + '</p>';
        classDoughnutContainer.appendChild(col);

        new Chart(document.getElementById(canvasId), {
            type: 'doughnut',
            data: {
                labels: [cls.type, 'Sisa'],
                datasets: [{
                    data: [count, remaining],
                    backgroundColor: [classColors[i % classColors.length], '#444444'],
                    borderWidth: 0,
                }],
            },
            options: {
                cutout: '70%',
                legend: { display: false },
                plugins: { legend: { display: false } },
            },
        });
    });

    // Section 6: Occupancy Rate Table
    var dateInRange = function (year, month, day, dateStr, dateFinishStr) {
        var check = new Date(year + '-' + String(month).padStart(2, '0') + '-' + String(day).padStart(2, '0'));
        return check >= new Date(dateStr) && check <= new Date(dateFinishStr);
    };

    // status=1: active by default, off when in class_temp_off
    // status=0: inactive by default, on when in class_temp_on
    var isBusOffOnDay = function (bus, day) {
        if (bus.status === 1) {
            return reportData.class_temp_off.some(function (off) {
                return off.fleet_registration_id === bus.fleet_registration_id
                    && off.tras_id === bus.tras_id
                    && dateInRange(reportData.year, reportData.month, day, off.date, off.date_finish);
            });
        } else {
            // status=0: day is OFF unless it appears in class_temp_on
            return !reportData.class_temp_on.some(function (on) {
                return on.fleet_registration_id === bus.fleet_registration_id
                    && on.tras_id === bus.tras_id
                    && dateInRange(reportData.year, reportData.month, day, on.date, on.date_finish);
            });
        }
    };

    // tickets per tras_id per day
    var ticketsByTrasDay = reportData.getTicket.reduce(function (acc, t) {
        var day = new Date(t.departure_date).getDate();
        var key = t.tras_id + '_' + day;
        acc[key] = (acc[key] || 0) + t.passenger_count_tb;
        return acc;
    }, {});

    var days = Array.from({ length: reportData.total_days }, function (_, i) { return i + 1; });

    // Build header rows
    var trDate = document.getElementById('occupacyRateDate');
    var trDetail = document.getElementById('occupacyRateDetail');
    var isDayAnyOff = function (d) {
        return classInfoGrouped.some(function (bus) { return isBusOffOnDay(bus, d); });
    };

    days.forEach(function (d) {
        var th = document.createElement('th');
        th.colSpan = 3;
        th.className = 'text-center';
        th.textContent = d;
        trDate.appendChild(th);
    });

    days.forEach(function () {
        ['Max Seat', '% Occup', 'Ticket Sold'].forEach(function (label) {
            var th = document.createElement('th');
            th.textContent = label;
            trDetail.appendChild(th);
        });
    });

    // Build body rows: one per bus
    var tbody = document.getElementById('occupancyRateBody');
    tbody.innerHTML = classInfoGrouped.map(function (bus) {
        var row = '<tr><td>' + bus.bus + '</td><td>' + bus.trip + '</td>';
        days.forEach(function (d) {
            var off = isBusOffOnDay(bus, d);
            var maxSeat = off ? 0 : bus.total_seat;
            var sold = ticketsByTrasDay[bus.tras_id + '_' + d] || 0;
            var pct = maxSeat > 0 ? ((sold / maxSeat) * 100).toFixed(0) + '%' : '0';
            var offStyle = off ? ' style="background-color:#fffde7;"' : '';
            row += '<td class="text-center"' + offStyle + '>' + maxSeat + '</td>'
                 + '<td class="text-center"' + offStyle + '>' + pct + '</td>'
                 + '<td class="text-center"' + offStyle + '>' + sold + '</td>';
        });
        row += '</tr>';
        return row;
    }).join('');

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

    // Section 7: Perbandingan Titik Naik
    var pickupMap = reportData.getTicket.reduce(function (acc, t) {
        acc[t.pickup_trip_location] = (acc[t.pickup_trip_location] || 0) + t.passenger_count_tb;
        return acc;
    }, {});

    var dropMap = reportData.getTicket.reduce(function (acc, t) {
        acc[t.drop_trip_location] = (acc[t.drop_trip_location] || 0) + t.passenger_count_tb;
        return acc;
    }, {});

    var pickupSorted = Object.entries(pickupMap).sort(function (a, b) { return b[1] - a[1]; });
    var dropSorted = Object.entries(dropMap).sort(function (a, b) { return b[1] - a[1]; });

    new Chart(document.getElementById('titikNaikChart'), {
        type: 'horizontalBar',
        data: {
            labels: pickupSorted.map(function (e) { return e[0]; }),
            datasets: [{
                label: 'Penumpang',
                data: pickupSorted.map(function (e) { return e[1]; }),
                backgroundColor: '#00cc66',
                barThickness: 50,
            }],
        },
        options: {
            responsive: true,
            legend: { display: false },
            scales: {
                xAxes: [{ ticks: { beginAtZero: true, stepSize: 1 } }],
            },
        },
    });

    new Chart(document.getElementById('titikTurunChart'), {
        type: 'horizontalBar',
        data: {
            labels: dropSorted.map(function (e) { return e[0]; }),
            datasets: [{
                label: 'Penumpang',
                data: dropSorted.map(function (e) { return e[1]; }),
                backgroundColor: '#0066ff',
                barThickness: 50,
            }],
        },
        options: {
            responsive: true,
            legend: { display: false },
            scales: {
                xAxes: [{ ticks: { beginAtZero: true, stepSize: 1 } }],
            },
        },
    });

    // Section 9: Perbandingan Bulan Lalu
    var prevDailyMap = reportData.getTicketPrevMonth.reduce(function (acc, t) {
        var day = new Date(t.departure_date).getDate();
        acc[day] = (acc[day] || 0) + t.passenger_count_tb;
        return acc;
    }, {});

    var prevTotalDays = reportData.getTicketPrevMonth.length > 0
        ? new Date(new Date(reportData.year, reportData.month - 1, 0)).getDate()
        : reportData.total_days;

    var prevDailyLabels = Array.from({ length: prevTotalDays }, function (_, i) { return i + 1; });
    var maxDays = Math.max(reportData.total_days, prevTotalDays);
    var chartLabels = Array.from({ length: maxDays }, function (_, i) { return i + 1; });

    var currDailyData = chartLabels.map(function (d) { return dailyMap[d] || 0; });
    var prevDailyData = chartLabels.map(function (d) { return prevDailyMap[d] || 0; });

    var prevMonthName = monthNames[parseInt(reportData.month, 10) - 2 < 0 ? 11 : parseInt(reportData.month, 10) - 2];
    var currMonthName = monthNames[parseInt(reportData.month, 10) - 1];

    new Chart(document.getElementById('perbandinganBulanLaluChart'), {
        type: 'line',
        data: {
            labels: chartLabels,
            datasets: [
                {
                    label: currMonthName,
                    data: currDailyData,
                    borderColor: '#ff0000',
                    backgroundColor: 'rgba(255,0,0,0.1)',
                    fill: true,
                    tension: 0.3,
                },
                {
                    label: prevMonthName,
                    data: prevDailyData,
                    borderColor: '#0066ff',
                    backgroundColor: 'rgba(0,102,255,0.1)',
                    fill: true,
                    tension: 0.3,
                },
            ],
        },
        options: {
            responsive: true,
            scales: {
                xAxes: [{ scaleLabel: { display: true, labelString: 'Tanggal' } }],
                yAxes: [{ ticks: { beginAtZero: true, stepSize: 1 }, scaleLabel: { display: true, labelString: 'Penumpang' } }],
            },
        },
    });

    var prevTotal = reportData.getTicketPrevMonth.reduce(function (s, t) { return s + t.passenger_count_tb; }, 0);
    var prevIncome = reportData.getTicketPrevMonth.reduce(function (s, t) {
        return s + (t.tp_price > 0 ? t.tp_price : (t.tb_price / t.passenger_count_tb));
    }, 0);

    document.getElementById('prev-month-seat').textContent = prevTotal;
    document.getElementById('prev-month-income').textContent = formatIDR(prevIncome);
    document.getElementById('curr-month-seat').textContent = reportData.getTicket.reduce(function (s, t) { return s + t.passenger_count_tb; }, 0);
    document.getElementById('curr-month-income').textContent = formatIDR(incomeTotal);

    // Section 10: Jadwal Buka/Tutup Sementara
    var renderTempTable = function (tbodyId, data) {
        var tbody = document.getElementById(tbodyId);
        if (!data || data.length === 0) {
            tbody.innerHTML = '<tr><td colspan="5" class="text-center">Tidak ada data</td></tr>';
            return;
        }
        tbody.innerHTML = data.map(function (item, i) {
            return '<tr>'
                + '<td class="text-center">' + (i + 1) + '</td>'
                + '<td>' + item.bus + '</td>'
                + '<td>' + item.causes + '</td>'
                + '<td>' + item.date + '</td>'
                + '<td>' + item.date_finish + '</td>'
                + '</tr>';
        }).join('');
    };

    renderTempTable('tempOnTableBody', reportData.class_temp_on);
    renderTempTable('tempOffTableBody', reportData.class_temp_off);

            loadingOverlay.style.display = 'none';
        }) // end .then(ticketData)
        .catch(function (err) {
            loadingOverlay.style.display = 'none';
            console.error('Failed to load ticket data', err);
        });

</script>
@endsection