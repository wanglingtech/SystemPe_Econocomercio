@extends('tenant.layouts.app')
@section('content')
    <tenant-dashboard-index
        :type-user="{{ json_encode(auth()->user()->type) }}"
        :soap-company="{{ json_encode($soap_company) }}"
        :user-establishment-id="{{ json_encode(auth()->user()->establishment_id) }}">
    </tenant-dashboard-index>
@endsection