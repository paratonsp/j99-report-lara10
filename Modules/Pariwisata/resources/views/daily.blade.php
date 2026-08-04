@extends('layouts.main', ['title' => $title ])

@section('content')

<div class="row mb-2">
    <div class="col-12">
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

        <div class="col-12 incomeSection mb-5 mt-3">
            <p>Total Pendapatan:</p>
            <p><strong>{{ $income_total }}</strong></p>
        </div>

        <div class="row mb-5">
            <div class="col-12 incomeSection">
                <p>Penjualan Berdasarkan Hari</p>
            </div>
            <div class="col-12">
                <x-chartjs-component :chart="$penjualan_harian_chart" />
            </div>
        </div>

        <div class="row mb-5">
            <div class="col-12 incomeSection">
                <p>Jumlah Bus Laku Harian</p>
            </div>
            <div class="col-12">
                <x-chartjs-component :chart="$bus_laku_harian_chart" />
            </div>
        </div>

        <div class="row mb-5">
            <div class="col-12 incomeSection">
                <p>Jumlah Hari Bus Beroperasi</p>
            </div>
            <div class="col-12">
                <small class="text-muted">
                    Dari {{ $total_days }} hari dalam periode terpilih.
                    Booking multi-hari dihitung per hari (tanggal mulai s/d tanggal selesai).
                </small>
            </div>
            <div class="col-12 mt-2">
                @if($bus_running_days->isEmpty())
                    <p class="text-muted">Tidak ada data.</p>
                @else
                <table class="table table-bordered table-sm">
                    <thead>
                        <tr>
                            <th>Bus</th>
                            <th class="text-center">Jumlah Hari Beroperasi</th>
                            <th class="text-center">Jumlah Booking</th>
                            <th class="text-center">Persentase Hari</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($bus_running_days as $bus)
                        <tr>
                            <td>{{ $bus->bus_name }}</td>
                            <td class="text-center">{{ $bus->total_days }}</td>
                            <td class="text-center">{{ $bus->total_booking }}</td>
                            <td class="text-center">
                                {{ $total_days > 0 ? round($bus->total_days / $total_days * 100, 1) : 0 }}%
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @endif
            </div>
        </div>

        <div class="row mb-5">
            <div class="col-12 incomeSection">
                <p>Bus Yang Beroperasi Per Tanggal</p>
            </div>
            <div class="col-12">
                @php $busDetailByDate = $bus_detail->groupBy('date'); @endphp
                @if($busDetailByDate->isEmpty())
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
                        @foreach($busDetailByDate as $date => $buses)
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

        <div class="row mb-5">
            <div class="col-12 incomeSection">
                <p>Perbandingan Dengan Bulan Lalu</p>
            </div>
            <div class="col-12">
                <small class="text-muted">
                    Periode lalu: {{ date('d/m/Y', strtotime($prev_date_start)) }} &ndash; {{ date('d/m/Y', strtotime($prev_date_end)) }}
                </small>
            </div>
            <div class="col-12 mt-2">
                <x-chartjs-component :chart="$perbandingan_bulan_chart" />
            </div>
        </div>
    </div>
</div>

@endsection

@section('script')
<script type="text/javascript">
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