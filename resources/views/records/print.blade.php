@extends('layouts.print')

@section('title', __('Counselling Record'))
@section('doctype', __('Counselling Session Record'))
@section('back-url', route('records.show', $record))

@section('content')
    <h1 class="doc-title">{{ $record->student->name }}</h1>
    <p class="doc-meta">
        {{ __('Record') }} <strong>#{{ $record->id }}</strong> &middot;
        {{ __('Session on') }} <strong>{{ $record->session_date->format('d F Y') }}</strong> &middot;
        {{ __('Counsellor') }} <strong>{{ $record->counsellor->name }}</strong>
    </p>

    <h2>{{ __('Case Details') }}</h2>
    <dl class="fields">
        <dt>{{ __('Student') }}</dt>
        <dd>{{ $record->student->name }}</dd>

        <dt>{{ __('Class') }}</dt>
        <dd>{{ $record->student->class ?? '—' }}</dd>

        <dt>{{ __('Session Date') }}</dt>
        <dd>{{ $record->session_date->format('d F Y') }}</dd>

        <dt>{{ __('Presenting Issue') }}</dt>
        <dd>{{ $record->issue_type_label }}</dd>

        <dt>{{ __('Category') }}</dt>
        <dd>{{ $record->category_label }}</dd>

        <dt>{{ __('Counsellor') }}</dt>
        <dd>{{ $record->counsellor->name }}</dd>

        <dt>{{ __('Last Updated') }}</dt>
        <dd>{{ $record->updated_at->format('d F Y, H:i') }}</dd>
    </dl>

    <h2>{{ __('Session Notes') }}</h2>
    <div class="notes">{{ $record->content }}</div>
@endsection
