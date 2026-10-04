@props(['name' => 'class', 'selected' => null, 'placeholder' => 'Select a class...', 'required' => false])

<select name="{{ $name }}" id="{{ $attributes->get('id', $name) }}"
        {{ $attributes->except(['id'])->merge(['class' => 'form-select']) }} @required($required)>
    <option value="">{{ $placeholder }}</option>
    @foreach (\App\Support\SchoolClasses::grouped() as $form => $classes)
        <optgroup label="{{ $form }}">
            @foreach ($classes as $class)
                <option value="{{ $class }}" @selected((string) $selected === $class)>{{ $class }}</option>
            @endforeach
        </optgroup>
    @endforeach
</select>
