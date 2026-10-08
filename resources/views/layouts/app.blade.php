<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'SafeNote') }}</title>

        @include('partials.favicon')

        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
        <link href="{{ asset('css/safenote.css') }}" rel="stylesheet">
    </head>
    <body>
        <div class="min-vh-100">
            @include('layouts.navigation')

            @isset($header)
                <header class="sn-page-header">
                    <div class="container">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <main class="container py-4">
                @if (session('status'))
                    <div class="alert alert-success d-flex align-items-center gap-2">
                        <i class="bi bi-check-circle-fill"></i> {{ session('status') }}
                    </div>
                @endif
                {{ $slot }}
            </main>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
        <script>
            document.querySelectorAll('[data-bs-toggle="tooltip"]')
                .forEach(el => new bootstrap.Tooltip(el));
        </script>
    </body>
</html>
