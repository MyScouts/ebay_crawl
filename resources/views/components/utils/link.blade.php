@props(['active' => '', 'text' => '', 'hide' => false, 'icon' => false, 'permission' => false, 'href' => '#'])

@if ($permission)
    @if ($logged_in_user->can($permission))
        @if (!$hide)
            <a {{ $attributes->merge(['href' => $href, 'class' => $active]) }}>
                @if ($icon)
                    <i class="{{ $icon }}"></i>
                @endif{{ strlen($text) ? $text : $slot }}
            </a>
        @endif
    @endif
@else
    @if (!$hide)
        <a {{ $attributes->merge(['href' => $href, 'class' => $active]) }}>
            @if ($icon)
                <i class="{{ $icon }}"></i>
            @endif{{ strlen($text) ? $text : $slot }}
        </a>
    @endif
@endif
