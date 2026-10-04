@props(['urgency', 'label'])

<span {{ $attributes->merge(['class' => 'sn-urgency sn-urgency--'.$urgency]) }}>{{ $label }}</span>
