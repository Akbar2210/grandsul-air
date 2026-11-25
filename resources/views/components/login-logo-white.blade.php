@props(['class' => 'w-full h-auto object-contain'])
<img src="{{ asset('images/logo-login-white.png') }}" alt="Grand Sulawesi Group" {{ $attributes->merge(['class' => $class]) }} />
