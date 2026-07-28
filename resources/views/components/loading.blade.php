{{--
    Pi DS — Loading spinner

    對應 resources/scss/components/_loading.scss。

      <span class="gl_loading"><i class="icon icon-_loading"></i></span>

    SCSS 只做「讓內部 .icon 旋轉」，尺寸與顏色不在元件內 ——
    既有 markup 是用 inline style 控制（font-size / color），
    設計系統沒有 gl_loading-{size} 這類 modifier，所以這裡也不發明，
    改開 size / color prop 直接吐 inline style。
--}}
@props([
    'icon' => 'icon-_loading',  // 旋轉的 icon class
    'size' => null,             // CSS font-size，如 '24px'
    'color' => null,            // CSS color，如 'var(--cl-green-500)'
])

@php
    $styles = array_filter([
        $size ? "font-size: {$size}" : null,
        $color ? "color: {$color}" : null,
    ]);
@endphp

<span
    {{ $attributes->class(['gl_loading']) }}
    @if ($styles) style="{{ implode('; ', $styles) }};" @endif
><i class="icon {{ $icon }}"></i></span>
