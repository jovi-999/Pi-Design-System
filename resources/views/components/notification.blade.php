{{--
    Pi DS — Notification item（通知列表的一列）

    對應 resources/scss/components/_notification.scss。
    結構沿用 blade 化前的 preview-static/notification.html（該目錄已廢除，見 CHANGELOG）：

      <div class="gl_notification-item gl_notification-{tone}">
        <span class="gl_icon-wrap"><i class="icon …"></i></span>
        <div class="gl_notification-item__main">
          <div class="fz-title-sm fz-tit">標題</div>
          <div class="fz-body-sm">內文</div>
        </div>
        <div class="fz-body-xs">時間</div>
      </div>

    內文與時間的顏色在既有 markup 是 inline style（var(--fg-2) / var(--fg-3)）——
    設計系統沒有對應的 utility class，照搬不自創。
--}}
@props([
    'tone' => 'basic',  // basic | info | success | warning | danger | purple
    'icon' => null,     // icon class
    'title' => null,    // 標題
    'time' => null,     // 右側時間字串（如「3 分鐘前」）
])

@php
    $tones = ['basic', 'info', 'success', 'warning', 'danger', 'purple'];
    if (! in_array($tone, $tones, true)) {
        throw new InvalidArgumentException(
            'x-pi::notification 的 tone 只能是 ' . implode(' / ', $tones) . "，收到 [{$tone}]"
        );
    }
@endphp

<div {{ $attributes->class(['gl_notification-item', "gl_notification-{$tone}"]) }}>
    @if ($icon)
        <span class="gl_icon-wrap"><i class="icon {{ $icon }}"></i></span>
    @endif

    <div class="gl_notification-item__main">
        @if ($title)
            <div class="fz-title-sm fz-tit">{{ $title }}</div>
        @endif
        <div class="fz-body-sm" style="color: var(--fg-2);">{{ $slot }}</div>
    </div>

    @if ($time)
        <div class="fz-body-xs" style="color: var(--fg-3); white-space: nowrap;">{{ $time }}</div>
    @endif
</div>
