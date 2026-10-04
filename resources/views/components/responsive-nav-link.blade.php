@props(['active'])

<a {{ $attributes->merge(['class' => 'dropdown-item' . (($active ?? false) ? ' active' : '')]) }}>
    {{ $slot }}
</a>
