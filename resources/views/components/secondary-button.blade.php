<button {{ $attributes->merge(['type' => 'button', 'class' => 'btn btn-outline-secondary btn-sm']) }}>
    {{ $slot }}
</button>
