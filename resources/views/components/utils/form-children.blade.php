@props([
    'action' => '#',
    'method' => 'POST',
    'name' => '',
    'formClass' => 'd-inline',
    'buttonClass' => '',
    'icon' => false,
    'permission' => false,
    'text' => '',
])

@if ($permission)
    @if ($logged_in_user->can($permission))
        <form method="POST" action="{{ $action }}" name="{{ $name }}" class="{{ $formClass }}">
            @csrf
            @method($method)
            {{ $slot }}
            <button type="submit" class="{{ $buttonClass }}">
                @if ($icon)
                    <i class="{{ $icon }}"></i>
                @endif{{ $text }}
            </button>
        </form>
    @endif
@else
    <form method="POST" action="{{ $action }}" name="{{ $name }}" class="{{ $formClass }}">
        @csrf
        @method($method)
        {{ $slot }}
        <button type="submit" class="{{ $buttonClass }}">
            @if ($icon)
                <i class="{{ $icon }}"></i>
            @endif{{ $text }}
        </button>
    </form>
@endif
