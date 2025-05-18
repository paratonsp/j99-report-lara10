@extends('layouts.main', ['title' => $title ])

@section('content')

@if ($errors->any())
<div class="alert alert-danger alert-dismissible">
    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
    <h5><i class="icon fas fa-ban"></i> Gagal Validasi!</h5>
    @foreach ($errors->all() as $error)
    <li>{{ $error }}</li>
    @endforeach
</div>
@endif

@if (session('success'))
<div class="alert alert-success alert-dismissible">
    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
    <h5><i class="icon fas fa-check"></i> Berhasil!</h5>
    {{ session('success') }}
</div>
@endif

@if (session('failed'))
<div class="alert alert-danger alert-dismissible">
    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
    <h5><i class="icon fas fa-ban"></i> Gagal!</h5>
    {{ session('failed') }}
</div>
@endif

<div class="row">
    <div class="col-12 mb-3">
        <button type="button" class="btn btn-secondary mr-3" data-toggle="modal" data-target="#addmodal">Buat Target Baru</button>
    </div>

    <div class="col-12">
        <table id="datatable" class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Bulan</th>
                    <th>Tahun</th>
                    <th>Target</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($list_pariwisata_target as $key => $item)
                <tr>
                    <td>{{ $item->month }}</td>
                    <td>{{ $item->year }}</td>
                    <td>{{ number_format($item->target, 0) }}</td>
                    <td>
                        <div class="row" style="place-content: center;">
                            <div>
                                <button type="button" class="btn btn-secondary mr-3" data-toggle="modal" data-target="#editmodal"
                                    data-target-id="{{ $item->id }}" data-target-month="{{ $item->month }}" data-target-year="{{ $item->year }}" data-target-target="{{ $item->target }}">Edit</button>
                            </div>
                            <div>
                                <form action="{{ url()->current() }}" method="post">
                                    {{ csrf_field() }}
                                    @method('delete')
                                    <input type="text" class="form-control" id="id" name="id" value="{{ $item->id }}" hidden>
                                    <button type="submit" class="btn btn-danger mr-3">Delete</button>
                                </form>
                            </div>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="addmodal">
    <div class="modal-dialog modal-xl">
        <form action="{{ url()->current() }}" method="post">
            {{ csrf_field() }}
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Buat Target Baru</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row col-12 mb-3">
                        <div class="col-lg-2 col-12">
                            <div class="form-group">
                                <label>Bulan</label>
                                <input type="text" class="form-control" id="month" name="month">
                            </div>
                        </div>
                        <div class="col-lg-2 col-12">
                            <div class="form-group">
                                <label>Tahun</label>
                                <input type="text" class="form-control" id="year" name="year">
                            </div>
                        </div>
                        <div class="col-lg-8 col-12">
                            <div class="form-group">
                                <label>Target</label>
                                <input type="text" class="form-control" id="target" name="target">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer justify-content-end">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" onclick="return confirm('Anda yakin data yg diisi sudah benar?')">Submit</button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="editmodal">
    <div class="modal-dialog modal-xl">
        <form action="{{ url()->current() }}" method="post">
            @method('patch')
            {{ csrf_field() }}
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Edit Target</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <input type="text" class="form-control" id="editid" name="id" hidden>
                <div class="modal-body">
                    <div class="row col-12 mb-3">
                        <div class="col-lg-2 col-12">
                            <div class="form-group">
                                <label>Bulan</label>
                                <input type="text" class="form-control" id="editmonth" readonly>
                            </div>
                        </div>
                        <div class="col-lg-2 col-12">
                            <div class="form-group">
                                <label>Tahun</label>
                                <input type="text" class="form-control" id="edityear" readonly>
                            </div>
                        </div>
                        <div class="col-lg-8 col-12">
                            <div class="form-group">
                                <label>Target</label>
                                <input type="text" class="form-control" id="edittarget" name="target">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer justify-content-end">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" onclick="return confirm('Anda yakin data yg diisi sudah benar?')">Submit</button>
                </div>
            </div>
        </form>
    </div>
</div>

@endsection

@section('script')

<script type="text/javascript">
    $(document).ready(function() {

        $("#editmodal").on("show.bs.modal", function(e) {
            var id = $(e.relatedTarget).data('target-id');
            var month = $(e.relatedTarget).data('target-month');
            var year = $(e.relatedTarget).data('target-year');
            var target = $(e.relatedTarget).data('target-target');
            $('#editid').val(id);
            $('#editmonth').val(month);
            $('#edityear').val(year);
            $('#edittarget').val(target);
        });
    });
</script>

@endsection