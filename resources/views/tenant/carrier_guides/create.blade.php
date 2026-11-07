@extends('tenant.layouts.app')

@section('content')
    <div class="page-header pr-0">
        <h2><a href="/dashboard"><i class="fas fa-tachometer-alt"></i></a></h2>
        <ol class="breadcrumbs">
            <li><a href="{{ route('tenant.carrier_guides.index') }}">Guías de Transportista</a></li>
            <li class="active"><span>Nueva</span></li>
        </ol>
    </div>
    <tenant-carrier-guides-create></tenant-carrier-guides-create>
@endsection