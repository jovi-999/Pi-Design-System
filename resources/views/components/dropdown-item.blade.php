{{--
    Pi DS — Dropdown item

    對應 resources/scss/components/_dropdown.scss。

      <button class="gl_dropdown-item gl_dropdown-item-md"><i class="icon …"></i>收藏</button>

    命名為 dropdown-item 而不是 dropdown：SCSS 只提供「選單項目」的樣式，
    浮出面板本身（定位、陰影、開關行為）不在設計系統內 —— 這是已知缺口，
    需要時走元件缺口流程，不要自己刻一個 .gl_dropdown 出來。
--}}
@props([
    'size' => 'md',  // md | sm
    'icon' => null,  // icon class
    'href' => null,  // 給了就 render <a>
])

@php
    $sizes = ['md', 'sm'];
    if (! in_array($size, $sizes, true)) {
        throw new InvalidArgumentException(
            'x-pi::dropdown-item 的 size 只能是 md / sm，收到 [' . $size . ']'
        );
    }

    // hover 樣式的選擇器是 `a.gl_dropdown-item` / `button.gl_dropdown-item`，
    // 所以一定要 render 成這兩種標籤之一，不能用 div。
    $tag = $href ? 'a' : 'button';
@endphp

<{{ $tag }}
    {{ $attributes->class(['gl_dropdown-item', "gl_dropdown-item-{$size}"]) }}
    @if ($href) href="{{ $href }}" @else type="button" @endif
>@if ($icon)<i class="icon {{ $icon }}"></i>@endif{{ $slot }}</{{ $tag }}>
