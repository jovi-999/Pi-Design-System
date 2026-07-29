{{--
    Pi DS — Button

    對應 resources/scss/components/_button.scss。
    這裡不產生任何新 class，只是把 SCSS 已有的 class 組起來。

    class 組合規則（照 _button.scss 的實際定義）：
      solid  → .gl_btn-{tone}
      其餘   → .gl_btn-{variant}-{tone}

    tone 的可用範圍依 variant 而異：只有 solid 有 dark。
    給錯組合會直接丟例外，而不是輸出一個不存在的 class —— 讓錯誤在
    preview 階段就爆掉，而不是變成「看起來沒樣式但沒人知道為什麼」。
--}}
@props([
    'variant' => 'solid',   // solid | outline | ghost | secondary | gray
    'tone' => 'basic',      // basic | dark(僅 solid) | info | success | warning | danger | orange | purple
    'size' => 'md',         // xl | lg | md | sm | xs
    'icon' => null,         // 純 icon 按鈕：傳 icon class（如 'icon-search'），需自行給 aria-label
    'iconLeft' => null,     // 文字左側 icon class
    'iconRight' => null,    // 文字右側 icon class
    'as' => 'button',       // button | a
])

@php
    $variants = ['solid', 'outline', 'ghost', 'secondary', 'gray'];
    $tones = ['basic', 'dark', 'info', 'success', 'warning', 'danger', 'orange', 'purple'];
    $sizes = ['xl', 'lg', 'md', 'sm', 'xs'];

    if (! in_array($variant, $variants, true)) {
        throw new InvalidArgumentException(
            "x-pi::button 的 variant 只能是 " . implode(' / ', $variants) . "，收到 [{$variant}]"
        );
    }
    if (! in_array($tone, $tones, true)) {
        throw new InvalidArgumentException(
            "x-pi::button 的 tone 只能是 " . implode(' / ', $tones) . "，收到 [{$tone}]"
        );
    }
    if (! in_array($size, $sizes, true)) {
        throw new InvalidArgumentException(
            "x-pi::button 的 size 只能是 " . implode(' / ', $sizes) . "，收到 [{$size}]"
        );
    }
    // _button.scss 只在 solid 定義了 dark，其餘 variant 沒有 .gl_btn-*-dark
    if ($tone === 'dark' && $variant !== 'solid') {
        throw new InvalidArgumentException(
            "設計系統只有 .gl_btn-dark（solid），沒有 .gl_btn-{$variant}-dark。"
            . '需要這個組合請走元件缺口流程提報，不要自行新增 class。'
        );
    }

    $toneClass = $variant === 'solid'
        ? "gl_btn-{$tone}"
        : "gl_btn-{$variant}-{$tone}";

    // 有 icon 又有文字時，文字要包 <span>（沿用 blade 化前的 preview-static/button.html，該目錄已廢除）
    $hasSideIcon = $iconLeft || $iconRight;
@endphp

<{{ $as }}
    @if ($as === 'button') type="{{ $attributes->get('type', 'button') }}" @endif
    {{ $attributes->except('type')->class([
        'gl_btn',
        "gl_btn-{$size}",
        $toneClass,
        'gl_btn-icon' => (bool) $icon,
    ]) }}
>
    @if ($icon)
        <i class="icon {{ $icon }}"></i>
    @else
        @if ($iconLeft)<i class="icon {{ $iconLeft }}"></i>@endif
        @if ($hasSideIcon)<span>{{ $slot }}</span>@else{{ $slot }}@endif
        @if ($iconRight)<i class="icon {{ $iconRight }}"></i>@endif
    @endif
</{{ $as }}>
