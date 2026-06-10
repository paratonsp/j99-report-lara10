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