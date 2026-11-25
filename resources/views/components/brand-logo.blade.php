@props(['class' => 'w-32 h-auto'])
<img src="{{ asset('images/logo.png') }}" alt="Grand Sulawesi Group" {{ $attributes->merge(['class' => $class]) }} />
