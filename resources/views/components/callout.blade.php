{{--
    Pi DS — Callout

    對應 resources/scss/components/_callout.scss。

    結構（沿用 blade 化前的 preview-static/callout.html，該目錄已廢除）：

      <div class="gl_callout-wrap gl_callout-{tone}">
        <span class="gl_callout-wrap__icon-wrap"><i class="icon …"></i></span>
        <div class="gl_callout-wrap__content">
          <div class="fz-title-md fz-tit">標題</div>
          <div class="fz-body-sm" style="color: var(--fg-2)">內文</div>
        </div>
      </div>

    內文的 color 在既有 markup 是 inline style（var(--fg-2)）—— 設計系統沒有
    對應的 utility class，這裡照搬不自創。
--}}
@props([
    'tone' => 'basic',  // basic | info | success | warning | danger | purple
    'icon' => null,     // icon class，如 'icon-info'
    'title' => null,    // 標題（不給就只有內文）
])

@php
    // _callout.scss 只定義了這 6 個 tone（沒有 orange）
    $tones = ['basic', 'info', 'success', 'warning', 'danger', 'purple'];
    if (! in_array($tone, $tones, true)) {
        throw new InvalidArgumentException(
            'x-pi::callout 的 tone 只能是 ' . implode(' / ', $tones) . "，收到 [{$tone}]。"
            . '（注意：callout 沒有 orange，跟 button 不同）'
        );
    }
@endphp

<div {{ $attributes->class(['gl_callout-wrap', "gl_callout-{$tone}"]) }}>
    @if ($icon)
        <span class="gl_callout-wrap__icon-wrap"><i class="icon {{ $icon }}"></i></span>
    @endif

    <div class="gl_callout-wrap__content">
        @if ($title)
            <div class="fz-title-md fz-tit">{{ $title }}</div>
        @endif
        <div class="fz-body-sm" style="color: var(--fg-2);">{{ $slot }}</div>
    </div>

    {{-- 右側行動區（選用）：<x-slot:action><x-pi::button …/></x-slot:action> --}}
    {{ $action ?? '' }}
</div>
