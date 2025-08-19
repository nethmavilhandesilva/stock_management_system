<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

   
     <title>@yield('title', 'POS-Sales')</title>

    {{-- CSRF Token --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Bootstrap 5 --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    {{-- Icons --}}
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    {{-- Your custom CSS --}}
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">

    {{-- Optional: Vite JS only --}}
    @vite(['resources/js/app.js'])

    @stack('styles')
</head>
<body class="antialiased">

    @include('layouts.partials.header')

    @hasSection('horizontal_sidebar')
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    @yield('horizontal_sidebar')
                </div>
            </div>
        </div>
    @endif

    {{-- Floating Report Button --}}
    <button class="btn btn-secondary position-fixed bottom-0 end-0 m-4 rounded-circle"
            data-bs-toggle="modal" data-bs-target="#reportFilterModal" title="Open Report Filter">
        <i class="material-icons">assessment</i>
    </button>

    <div class="main-wrapper container-fluid">
        <div class="row">
            @if (!trim($__env->yieldContent('horizontal_sidebar')))
                <div class="col-md-3">
                    @include('layouts.partials.sidebar')
                </div>
            @endif

            <div class="{{ trim($__env->yieldContent('horizontal_sidebar')) ? 'col-md-12' : 'col-md-9' }}">
                @include('layouts.partials.navbar')

                <main class="main users chart-page mt-2" id="skip-target">
                    @yield('content')
                </main>
            </div>
        </div>

        @include('layouts.partials.footer')
        @include('layouts.partials.report-modal')
        @include('layouts.partials.item-wisemodal')
        @include('layouts.partials.weight-modal')
        @include('layouts.partials.salecode-modal')
        @include('layouts.partials.sales-modal')
        @include('layouts.partials.salesadjustments-modal')
        @include('layouts.partials.dayStartModal')
        @include('layouts.partials.LoanReport-Modal')
        @include('layouts.partials.grn-modal')
    </div>

    {{-- Bootstrap JS (with Popper) --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    @stack('scripts')
</body>
</html>
