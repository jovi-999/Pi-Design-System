{{--
    Pi DS — Alert（浮出式提示條）

    對應 resources/scss/components/_alert.scss。
    結構照 preview-static/alert.html：

      <div class="gl_alert-body gl_alert-{tone}">
        <span class="gl_alert-icon"><i class="icon …"></i></span>
        <div class="gl_alert-con">
          <div>
            <div class="fz-body-sm">主文</div>
            <div class="fz-body-xs">副文</div>
          </div>
        </div>
      </div>

    注意 .gl_alert-con 內層多包一個無 class 的 <div>：因為 .gl_alert-con 是
    space-between，要讓文字群組與右側 action 分開靠兩端。只有主文時也保留
    這層，結構才一致。
--}}
@props([
    'tone' => 'basic',   // basic | info | success | warning | danger | purple
    'icon' => null,      // icon class
    'description' => null, // 副文（fz-body-xs）
])

@php
    $tones = ['basic', 'info', 'success', 'warning', 'danger', 'purple'];
    if (! in_array($tone, $tones, true)) {
        throw new InvalidArgumentException(
            'x-pi::alert 的 tone 只能是 ' . implode(' / ', $tones) . "，收到 [{$tone}]"
        );
    }
@endphp

<div {{ $attributes->class(['gl_alert-body', "gl_alert-{$tone}"]) }}>
    @if ($icon)
        <span class="gl_alert-icon"><i class="icon {{ $icon }}"></i></span>
    @endif

    <div class="gl_alert-con">
        <div>
            <div class="fz-body-sm">{{ $slot }}</div>
            @if ($description)
                <div class="fz-body-xs">{{ $description }}</div>
            @endif
        </div>

        {{-- 右側行動區（選用） --}}
        {{ $action ?? '' }}
    </div>
</div>
