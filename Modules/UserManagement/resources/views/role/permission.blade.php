@extends('layouts.main', ['title' => $title ])

@section('content')

@php
    $accessName = ['Index','Lihat','Tambah','Edit','Hapus']
@endphp

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
 
<div class="card card-primary">
  <div class="card-header">
    <h3 class="card-title">Form {{ $title }} {{ $role_info->title }}</h3>
  </div>
  <!-- /.card-header -->
  <!-- form start -->
    <form action="{{ url()->current() }}" method="post">
      @csrf
        <div class="card-body">
          <div class="row">
            <div class="col-sm-12">
              <table class="table table-bordered table-hover">
                <thead>
                  <tr>
                    <th width="10">No</th>
                    <th>Nama Fitur</th>
                    <th width="10">Index</th>
                    <th width="10">Lihat</th>
                    <th width="10">Tambah</th>
                    <th width="10">Edit</th>
                    <th width="10">Hapus</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach ($permissionList as $key => $item)
                    <tr data-widget="expandable-table" aria-expanded="false">
                      <td>{{ $key + 1 }}</td>
                      <td>{{ $item->parent_title ? $item->parent_title.' - ' : '' }}{{ $item->title }}</td>
                      @foreach ($item->access as $keyAccess => $itemAccess)
                        <td class="text-center">
                          <div class="icheck-success d-inline">
                            <input 
                              type="checkbox" 
                              id="{{ $item->slug.'_'.$itemAccess->name }}" 
                              name="permission[]" 
                              value="{{ $itemAccess->permissionId }}"
                              {{ $itemAccess->isGranted ? 'checked' : '' }} 
                              {{ !$itemAccess->isAvailable ? 'disabled=""' : '' }}
                            >
                            <label for="{{ $item->slug.'_'.$itemAccess->name }}">
                              {{ $accessName[$keyAccess] }}
                            </label>
                          </div>
                        </td>
                      @endforeach
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          </div>
        </div>
    <!-- /.card-body -->

    <div class="card-footer">
      <button type="submit" class="btn btn-primary">Submit</button>
      <a href="{{ url('usermanagement/role') }}" onclick="return confirm('Anda yakin mau kembali?')" class="btn btn-success">Kembali</a>
    </div>
  </form>
</div>
 
@endsection