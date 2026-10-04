@props(['label', 'value', 'tone' => 'slate'])

{{-- A small figure that sits in a page head: the number leads, the label
     explains it, and the tone is never the only thing carrying meaning. --}}
<span class="sn-chip-stat sn-chip-{{ $tone }}">
    <span class="sn-chip-value">{{ $value }}</span>
    <span class="sn-chip-label">{{ $label }}</span>
</span>
