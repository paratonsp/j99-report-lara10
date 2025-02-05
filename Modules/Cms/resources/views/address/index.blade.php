@extends('layouts.main', ['title' => $title ])

@section('content')
 
<div class="card">
    <div class="card-header">
        <h3 class="card-title">List {{ $title }}</h3>
        <div class="float-right">
          @if (permissionCheck('add'))
            <a href="{{ url('usermanagement/menu/add') }}" class="btn bg-gradient-primary btn-sm">Tambah data</a>
          @endif
        </div>
    </div>
    <!-- /.card-header -->
    <div class="card-body">
      <table id="datatable-def" class="table table-bordered table-striped">
        <thead>
        <tr>
          <th>No</th>
          <th>Nama Alamat</th>
          <th>Detail Alamat</th>
          <th>Telephone</th>
          <th>Aksi</th>
        </tr>
        </thead>
        <tbody>
          @foreach ($list as $key => $value)
            <tr>
              <td width="20" class="text-center">{{ intval($key) + 1 }}</td>
              <td>{{ $value->title }}</td>
              <td>{{ $value->detail }}</td>
              <td>{{ $value->phone }}</td>
              <td>
                <div class="btn-group btn-block">
                  @if (permissionCheck('edit')) <a href="#" class="btn btn-success btn-sm">Edit</a> @endif
                  @if (permissionCheck('delete')) <a href="#" onclick="return confirm('Anda yakin menghapus data ini?')" class="btn btn-danger btn-sm">Hapus</a> @endif
                </div>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
    <!-- /.card-body -->
  </div>
 
@endsection