@extends('layouts.print')

@section('title', __('Caseload Report'))
@section('doctype', __('Caseload Report'))
@section('back-url', route('records.index'))

@section('toolbar')
    <form method="GET" action="{{ route('reports.caseload') }}" class="d-flex flex-wrap gap-2 align-items-center">
        <label class="small text-muted" for="from">{{ __('From') }}</label>
        <input type="date" id="from" name="from" value="{{ $from->format('Y-m-d') }}" class="form-control form-control-sm" style="width: auto;">
        <label class="small text-muted" for="to">{{ __('To') }}</label>
        <input type="date" id="to" name="to" value="{{ $to->format('Y-m-d') }}" class="form-control form-control-sm" style="width: auto;">
        <div class="form-check mb-0">
            <input class="form-check-input" type="checkbox" id="names" name="names" value="1" @checked($showNames)>
            <label class="form-check-label small" for="names">{{ __('Show student names') }}</label>
        </div>
        <button type="submit" class="btn btn-outline-secondary btn-sm">{{ __('Update') }}</button>
    </form>
@endsection

@section('content')
    <h1 class="doc-title">{{ __('Counselling Caseload Report') }}</h1>
    <p class="doc-meta">
        {{ __('Period') }} <strong>{{ $from->format('d F Y') }} – {{ $to->format('d F Y') }}</strong> &middot;
        {{ __('Counsellor') }} <strong>{{ auth()->user()->name }}</strong>
    </p>

    <div class="figures">
        <div class="figure-box">
            <div class="label">{{ __('Total sessions') }}</div>
            <div class="value">{{ $total }}</div>
        </div>
        <div class="figure-box">
            <div class="label">{{ __('Students supported') }}</div>
            <div class="value">{{ $students }}</div>
        </div>
        <div class="figure-box">
            <div class="label">{{ __('Leading issue') }}</div>
            <div class="value" style="font-size: 11pt; padding-top: 0.2rem;">
                {{ $total > 0 ? $byIssue->first()['label'] : '—' }}
            </div>
        </div>
    </div>

    <p style="font-size: 8pt; color: #5a6b7f;">
        {{ __('This report contains case statistics only. Session notes are never included.') }}
        @unless ($showNames)
            {{ __('Student names and classes are withheld; students appear by reference number.') }}
        @endunless
    </p>

    @if ($total === 0)
        <div class="note-empty">{{ __('No counselling sessions were recorded in this period.') }}</div>
    @else
        <h2>{{ __('Sessions by Category') }}</h2>
        <table class="data">
            <thead>
                <tr>
                    <th>{{ __('Category') }}</th>
                    <th class="bar-cell">{{ __('Share') }}</th>
                    <th class="num">{{ __('Sessions') }}</th>
                    <th class="num">%</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($byCategory as $row)
                    @php $pct = round($row['count'] / $total * 100); @endphp
                    <tr>
                        <td>{{ $row['label'] }}</td>
                        <td class="bar-cell"><div class="bar"><span style="width: {{ $pct }}%;"></span></div></td>
                        <td class="num">{{ $row['count'] }}</td>
                        <td class="num">{{ $pct }}%</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td>{{ __('Total') }}</td>
                    <td class="bar-cell"></td>
                    <td class="num">{{ $total }}</td>
                    <td class="num">100%</td>
                </tr>
            </tfoot>
        </table>

        <h2>{{ __('Sessions by Presenting Issue') }}</h2>
        <table class="data">
            <thead>
                <tr>
                    <th>{{ __('Issue') }}</th>
                    <th>{{ __('Category') }}</th>
                    <th class="bar-cell">{{ __('Share') }}</th>
                    <th class="num">{{ __('Sessions') }}</th>
                    <th class="num">%</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($byIssue as $row)
                    @php $pct = round($row['count'] / $total * 100); @endphp
                    <tr>
                        <td>{{ $row['label'] }}</td>
                        <td style="color: #5a6b7f;">{{ $row['category'] }}</td>
                        <td class="bar-cell"><div class="bar"><span style="width: {{ $pct }}%;"></span></div></td>
                        <td class="num">{{ $row['count'] }}</td>
                        <td class="num">{{ $pct }}%</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <h2>{{ __('Session List') }}</h2>
        <table class="data">
            <thead>
                <tr>
                    <th class="num" style="width: 10mm;">#</th>
                    <th style="width: 26mm;">{{ __('Date') }}</th>
                    <th>{{ __('Student') }}</th>
                    @if ($showNames)
                        <th style="width: 26mm;">{{ __('Class') }}</th>
                    @endif
                    <th>{{ __('Presenting Issue') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($records as $record)
                    <tr>
                        <td class="num">{{ $loop->iteration }}</td>
                        <td>{{ $record->session_date->format('d M Y') }}</td>
                        @if ($showNames)
                            <td>{{ $record->student->name }}</td>
                            <td>{{ $record->student->class ?? '—' }}</td>
                        @else
                            <td>{{ __('Student') }} #{{ $record->student_id }}</td>
                        @endif
                        <td>{{ $record->issue_type_label }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
@endsection
