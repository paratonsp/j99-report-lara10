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
</div>
</div>

@endsection

@section('script')
<script>
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
     * @typedef {Object} TypeData
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
     */

    /** @type {TypeData} */
    var typedata = {
        trip_route_grouped: @json($trip_route_grouped),
        trip_group: @json($trip_group),
        trip_assign_group: @json($trip_assign_group),
        total_days: @json($total_days),
        month: @json($month),
        year: @json($year),
        class_info: @json($class_info ?? []),
        getTicket: @json($getTicket ?? []),
        getBuy: @json($getBuy ?? []),
        target: @json($target ?? 0),
    };

    console.log(typedata);

    var classInfoGrouped = Object.values(typedata.class_info.reduce(function (acc, item) {
        var key = item.bus;
        if (!acc[key]) {
            acc[key] = {
                bus: item.bus,
                trip: item.trip,
                registration: item.registration,
                trip_route_id: item.trip_route_id,
                fleet_registration_id: item.fleet_registration_id,
                status: item.status,
                tras_id: item.tras_id,
                assign_time: item.assign_time,
                days_active: item.days_active,
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

    console.log(classInfoGrouped);

    var incomeTotal = typedata.getTicket.reduce(function (sum, ticket) {
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

    var groupedByBuyMonth = typedata.getTicket.reduce(function (acc, ticket) {
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

    var groupedByDepartureDateMonthSelling = typedata.getBuy.reduce(function (acc, ticket) {
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

    var sellingTotal = typedata.getBuy.reduce(function (sum, ticket) {
        var price = ticket.tp_price > 0 ? ticket.tp_price : (ticket.tb_price / ticket.passenger_count_tb);
        return sum + price;
    }, 0);

    var totalSeatMonthAll = classInfoGrouped.reduce(function (sum, bus) { return sum + bus.total_seat_month; }, 0);

    document.getElementById('income-value').textContent = formatIDR(incomeTotal);
    document.getElementById('selling-value').textContent = formatIDR(sellingTotal);
    var occupancyPercentage = totalSeatMonthAll > 0
        ? ((typedata.getTicket.length / totalSeatMonthAll) * 100).toFixed(2) + '%'
        : '0%';

    document.getElementById('total-seat-percentage').textContent = occupancyPercentage;

    var filled = typedata.getTicket.length;
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
    document.getElementById('total-seat-description').textContent = typedata.getTicket.length + ' / ' + totalSeatMonthAll + ' seat bulan ini';
    document.getElementById('target-value').textContent = typedata.target === '-'
        ? 'Target belum disetting'
        : formatIDR(typedata.target);
</script>
@endsection