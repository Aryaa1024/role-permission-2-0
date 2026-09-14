@extends('admin.layouts.app')
@push('page-style')
    <style>

    </style>
@endpush
@section('page-content')
    <div class="row">
        <div class="col-sm-12">
            <ul class="breadcrumb">
                <li class="breadcrumb-item"> <a href="{{ route('admin.dashboard') }}">Dashboard</a> </li>
                <li class="breadcrumb-item active">Users</li>
                <li class="breadcrumb-item active">Profile</li>
            </ul>
            <hr class="mb-4">
        </div>
    </div>
    <div class="row mb-3 d-flex align-items-center justify-content-between">
        <div class="col-sm-12 col-md-6">
            <h3 class="h3">Profile</h3>
        </div>
    </div>
    <div class="row">

    </div>
@endsection
@push('page-script')
    <script>

    </script>
@endpush
