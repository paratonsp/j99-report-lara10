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

<div class="row mb-5">
    <div class="d-flex justify-content-end">

    </div>
    <form action="{{ url()->current() }}" method="post">
        @method('patch')
        {{ csrf_field() }}
        <div class="row col-12 mb-5">
            @foreach ($trip_route_group as $key => $value)
            <div class="row col-12 mb-3">
                <div class="col-lg-3 col-12">
                    <div class="form-group">
                        <label>Rute A</label>
                        <input type="text" name="data[{{$key}}][id]" value="<?php echo ($value->id) ?>" hidden>
                        <input type="text" class="form-control" name="data[{{$key}}][name_x]" value="<?php echo ($value->name_x) ?>">
                    </div>
                </div>
                <div class="col-lg-3 col-12">
                    <div class="form-group">
                        <label>Grup Rute A</label>
                        <select class="select2 select2-hidden-accessible" data-placeholder="Pilih Rute ID" data-width="100%" name="data[{{$key}}][route_x][]" multiple>
                            @php
                            $value->route_x = explode(",", $value->route_x);
                            @endphp

                            @foreach ($all_trip_route as $item)
                            @if(in_array($item->id, $value->route_x))
                            <option value="<?php echo ($item->id) ?>" selected><?php echo ($item->id . ': ' . $item->name) ?></option>
                            @else
                            <option value="<?php echo ($item->id) ?>"><?php echo ($item->id . ': ' . $item->name) ?></option>
                            @endif
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-lg-3 col-12">
                    <div class="form-group">
                        <label>Rute B</label>
                        <input type="text" class="form-control" name="data[{{$key}}][name_y]" value="<?php echo ($value->name_y) ?>">
                    </div>
                </div>
                <div class="col-lg-3 col-12">
                    <div class="form-group">
                        <label>Grup Rute B</label>
                        <select class="select2 select2-hidden-accessible" data-placeholder="Pilih Rute ID" data-width="100%" name="data[{{$key}}][route_y][]" multiple>
                            @php
                            $value->route_y = explode(",", $value->route_y);
                            @endphp

                            @foreach ($all_trip_route as $item)
                            @if(in_array($item->id, $value->route_y))
                            <option value="<?php echo ($item->id) ?>" selected><?php echo ($item->id . ': ' . $item->name) ?></option>
                            @else
                            <option value="<?php echo ($item->id) ?>"><?php echo ($item->id . ': ' . $item->name) ?></option>
                            @endif
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
            <div class="col-12">
                <hr>
            </div>
            @endforeach
            <div class="col-12 d-flex justify-content-end">
                <button type="button" class="btn btn-secondary mr-3" data-toggle="modal" data-target="#addnewmodal">Buat Grup Rute</button>
                <button type="submit" class="btn btn-primary" onclick="return confirm('Anda yakin data yg diisi sudah benar?')">Submit</button>
            </div>
        </div>
    </form>
</div>

<div class="modal fade" id="addnewmodal">
    <div class="modal-dialog modal-xl">
        <form action="{{ url()->current() }}" method="post">
            {{ csrf_field() }}
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Buat Grup Rute Baru</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row col-12 mb-3">
                        <div class="col-lg-3 col-12">
                            <div class="form-group">
                                <label>Rute A</label>

                                <input type="text" class="form-control" name="name_x">
                            </div>
                        </div>
                        <div class="col-lg-3 col-12">
                            <div class="form-group">
                                <label>Grup Rute A</label>
                                <select class="select2 select2-hidden-accessible" data-placeholder="Pilih Rute ID" data-width="100%" name="route_x[]" multiple>
                                    @foreach ($all_trip_route as $item)
                                    <option value="<?php echo ($item->id) ?>"><?php echo ($item->id . ': ' . $item->name) ?></option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-lg-3 col-12">
                            <div class="form-group">
                                <label>Rute B</label>
                                <input type="text" class="form-control" name="name_y">
                            </div>
                        </div>
                        <div class="col-lg-3 col-12">
                            <div class="form-group">
                                <label>Grup Rute B</label>
                                <select class="select2 select2-hidden-accessible" data-placeholder="Pilih Rute ID" data-width="100%" name="route_y[]" multiple>
                                    @foreach ($all_trip_route as $item)
                                    <option value="<?php echo ($item->id) ?>"><?php echo ($item->id . ': ' . $item->name) ?></option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer justify-content-end">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" onclick="return confirm('Anda yakin data yg diisi sudah benar?')">Submit</button>
                </div>
            </div>
            <!-- /.modal-content -->
        </form>
    </div>
    <!-- /.modal-dialog -->
</div>

@endsection

@section('script')
<script type="text/javascript">
    $(document).ready(function() {
        $('select').select2({});
    });
</script>
@endsection