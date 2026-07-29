{{--
    Pi DS — Content switcher（分頁式切換導覽）

    對應 resources/scss/components/_content-switcher.scss。
    結構沿用 blade 化前的 preview-static/content-switcher.html（該目錄已廢除，見 CHANGELOG）：

      <div class="gl_content-switcher-outer gl_content-switcher-{tone}">
        <button class="gl_content-switcher is-active">面試心得 <span>(48)</span></button>
        <button class="gl_content-switcher">薪資</button>
      </div>

    這支收整個 items 陣列而不是逐個 item 元件：作用色是掛在 outer 上、
    由 `.gl_content-switcher-{tone} .gl_content-switcher.is-active` 生效的，
    item 單獨存在沒有意義。
--}}
@props([
    'tone' => 'basic',  // basic | info | success
    'items' => [],      // [['label'=>…, 'count'=>…, 'active'=>bool, 'href'=>…], …]
])

@php
    // _content-switcher.scss 只定義了這 3 個 tone
    $tones = ['basic', 'info', 'success'];
    if (! in_array($tone, $tones, true)) {
        throw new InvalidArgumentException(
            'x-pi::content-switcher 的 tone 只能是 ' . implode(' / ', $tones) . "，收到 [{$tone}]。"
            . '（只有 basic / info / success 有 active 色）'
        );
    }
@endphp

<div {{ $attributes->class(['gl_content-switcher-outer', "gl_content-switcher-{$tone}"]) }}>
    @foreach ($items as $item)
        @php
            $tag = ! empty($item['href']) ? 'a' : 'button';
        @endphp
        <{{ $tag }}
            @class(['gl_content-switcher', 'is-active' => ! empty($item['active'])])
            @if ($tag === 'a') href="{{ $item['href'] }}" @else type="button" @endif
        >{{ $item['label'] ?? '' }}@if (isset($item['count']))<span style="color: var(--fg-3); font-weight: 400; margin-left: 4px;">({{ $item['count'] }})</span>@endif</{{ $tag }}>
    @endforeach

    {{-- 自行組 item 時用 slot（此時 items 留空） --}}
    {{ $slot }}
</div>
