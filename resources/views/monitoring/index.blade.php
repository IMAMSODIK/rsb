@extends('layouts.template')

@section('content')
    <div class="app-hero-header d-flex align-items-center">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <i class="bi bi-house lh-1 pe-3 me-3 border-end border-dark"></i>
                <a href="{{ route('dashboard') }}" class="text-decoration-none">Home</a>
            </li>
            <li class="breadcrumb-item text-secondary">Monitoring</li>
            <li class="breadcrumb-item text-secondary" aria-current="page">{{ $title }}</li>
        </ol>
    </div>

    <div class="app-body">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">{{ $title }}</h5>
                </div>
                <div class="card-body">
                    <p class="mb-0 text-secondary">Modul {{ strtolower($title) }} belum tersedia.</p>
                </div>
            </div>
        </div>
    </div>
@endsection
