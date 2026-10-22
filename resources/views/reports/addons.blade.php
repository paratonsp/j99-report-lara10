@extends('layouts.main', ['title' => $title])

@section('content')
<div class="card">
    <div class="card-body">
        <form method="get" action="{{ route('reports.addons', ['report' => $reportType]) }}" class="form-inline">
            <label for="month" class="mr-2">Bulan pembelian</label>
            <input id="month" type="month" name="month" value="{{ $month }}" required class="form-control mr-3 mb-2">
            @if ($insurance)
                <label for="status" class="mr-2">Status polis</label>
                <select id="status" name="status" class="form-control mr-3 mb-2">
                    <option value="">Semua status</option>
                    @foreach ($statuses as $value)
                        <option value="{{ $value }}" @selected($status === $value)>{{ ucfirst($value) }}</option>
                    @endforeach
                </select>
            @endif
            <button type="submit" class="btn btn-primary mr-2 mb-2">Tampilkan</button>
            <a class="btn btn-success mb-2" href="{{ route('reports.addons.export', ['report' => $reportType, 'month' => $month, 'status' => $status]) }}">Export CSV</a>
        </form>
        @foreach ($errors->all() as $error)
            <p class="text-danger">{{ $error }}</p>
        @endforeach
        <p class="text-muted mb-0">Transaksi booking yang sudah dibayar, berdasarkan tanggal pembelian addon.</p>
        @if ($insurance)
            <p class="text-muted mb-0">Tiket batal dengan polis pending tidak dihitung, mengikuti laporan admin.</p>
        @endif
    </div>
</div>
<div class="row">
    @foreach (['Booking' => number_format($summary->bookings, 0, ',', '.'), 'Addon / Tiket' => number_format($summary->addons, 0, ',', '.'), 'Total Biaya' => 'Rp '.number_format($summary->fees, 0, ',', '.'), 'Tiket Batal' => number_format($summary->cancelled, 0, ',', '.')] as $label => $value)
        <div class="col-md-3 col-6"><div class="small-box bg-white"><div class="inner"><h4>{{ $value }}</h4><p>{{ $label }}</p></div></div></div>
    @endforeach
</div>
@if ($insurance)
    <div class="card"><div class="card-body">
        <p>Jumlah polis bulan ini (semua status):</p>
        @forelse ($statusCounts as $label => $count)
            <span class="badge badge-secondary mr-2 p-2">{{ ucfirst($label) }}: {{ number_format($count, 0, ',', '.') }}</span>
        @empty
            <span class="text-muted">Belum ada polis.</span>
        @endforelse
    </div></div>
@endif
<div class="card">
    <div class="card-header"><h3 class="card-title">Daftar Transaksi {{ $month }}</h3></div>
    <div class="card-body table-responsive">
        <table class="table table-striped table-bordered">
            <thead><tr>
                <th>Tanggal Pembelian</th><th>Booking</th><th>Tiket</th><th>Penumpang</th><th>Rute</th><th>Status Tiket</th><th>Biaya</th>
                @if ($insurance)<th>Status Polis</th><th>Nomor Polis</th><th>Referensi</th>@endif
            </tr></thead>
            <tbody>
                @forelse ($transactions as $row)
                    <tr>
                        <td>{{ $row->created_at }}</td><td>{{ $row->booking_code }}</td><td>{{ $row->ticket_number }}</td>
                        <td>{{ $row->passenger_name ?? '-' }}</td><td>{{ $row->route_name }}</td>
                        <td>{{ $row->ticket_cancelled === null ? 'Tidak diketahui' : ($row->ticket_cancelled != 0 ? 'Batal' : 'Aktif') }}</td>
                        <td class="text-nowrap">Rp {{ number_format($row->fee, 0, ',', '.') }}</td>
                        @if ($insurance)<td>{{ ucfirst($row->policy_status) }}</td><td>{{ $row->policy_number ?: '-' }}</td><td>{{ $row->reference_no ?: '-' }}</td>@endif
                    </tr>
                @empty
                    <tr><td colspan="{{ $insurance ? 10 : 7 }}" class="text-center">Belum ada transaksi untuk filter ini.</td></tr>
                @endforelse
            </tbody>
        </table>
        <p>Menampilkan {{ $transactions->firstItem() ?? 0 }}–{{ $transactions->lastItem() ?? 0 }} dari {{ $transactions->total() }} transaksi.</p>
        {{ $transactions->links('pagination::bootstrap-4') }}
    </div>
</div>
@endsection
