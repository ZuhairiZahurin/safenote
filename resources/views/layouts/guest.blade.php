<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'SafeNote') }}</title>

        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
        <link href="{{ asset('css/safenote.css') }}" rel="stylesheet">
    </head>
    <body>
        <div class="sn-auth">
            <div class="sn-auth-shell">
                <div class="sn-auth-brand">
                    <div>
                        <x-application-logo />
                        <p class="sn-auth-tagline mt-2 mb-0">{{ __('Unit Bimbingan dan Kaunseling, SMK Pandan Indah') }}</p>
                    </div>

                    <h1>{{ __('Student counselling records, kept confidential.') }}</h1>

                    <ul class="sn-auth-points">
                        <li>
                            <i class="bi bi-lock-fill"></i>
                            <span>
                                <strong>{{ __('Encrypted at rest') }}</strong>
                                {{ __('Session notes are stored as AES-256 ciphertext.') }}
                            </span>
                        </li>
                        <li>
                            <i class="bi bi-people-fill"></i>
                            <span>
                                <strong>{{ __('Role-based access') }}</strong>
                                {{ __('Counsellors, teachers and admins see only their own area.') }}
                            </span>
                        </li>
                        <li>
                            <i class="bi bi-journal-text"></i>
                            <span>
                                <strong>{{ __('Every action recorded') }}</strong>
                                {{ __('Logins and record changes are written to an audit log.') }}
                            </span>
                        </li>
                    </ul>
                </div>

                <div class="sn-auth-panel">
                    {{ $slot }}
                </div>
            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    </body>
</html>
