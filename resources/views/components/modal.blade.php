{{--
    Pi DS — Modal 面板

    對應 resources/scss/components/_modal.scss。
    結構照 preview-static/modal.html 的 .gl_modal 範例：

      <div class="gl_modal">
        <div class="gl_modal-header flex-column">
          <span class="gl_modal-icon text-green-500"><i class="icon …"></i></span>
          <div class="gl_modal-info">
            <div class="fz-title-lg fz-tit">標題</div>
            <div class="fz-body-sm">說明</div>
          </div>
        </div>
        <div class="gl_modal-footer flex-column">…按鈕…</div>
      </div>

    ⚠️ 這支只有「面板外觀」。遮罩、置中、開關行為、focus trap 都不在
    設計系統內 —— preview-static/modal.html 用的 .gl_gs-modal 是那一頁自帶的
    inline CSS（該檔註解寫明「此 namespace 無 src CSS，preview 專用」），
    不是套件的一部分，所以這裡不使用它。

    需要可開關的 modal：用原生 <dialog> 包住這支元件，或走元件缺口流程提報。
--}}
@props([
    'icon' => null,       // 頂部 icon class
    'iconClass' => null,  // icon 顏色，用 base/_utilities.scss 的 .text-* class
    'title' => null,      // 標題（fz-title-lg fz-tit）
    'footerLayout' => 'flex-column', // flex-column | flex-row | flex-row flex-end
])

@php
    // _modal.scss 對 footer 只定義了這三種排列
    $layouts = ['flex-column', 'flex-row', 'flex-row flex-end'];
    if (! in_array($footerLayout, $layouts, true)) {
        throw new InvalidArgumentException(
            "x-pi::modal 的 footerLayout 只能是 'flex-column' / 'flex-row' / 'flex-row flex-end'，"
            . "收到 [{$footerLayout}]"
        );
    }
@endphp

<div {{ $attributes->class(['gl_modal']) }}>
    @if ($icon || $title || isset($description))
        <div class="gl_modal-header flex-column">
            @if ($icon)
                <span @class(['gl_modal-icon', $iconClass => (bool) $iconClass])>
                    <i class="icon {{ $icon }}"></i>
                </span>
            @endif

            <div class="gl_modal-info">
                @if ($title)
                    <div class="fz-title-lg fz-tit">{{ $title }}</div>
                @endif
                @isset($description)
                    <div class="fz-body-sm">{{ $description }}</div>
                @endisset
            </div>
        </div>
    @endif

    {{-- 主內容（選用）。header 已含標題與說明時通常不需要。 --}}
    @if (trim($slot) !== '')
        <div class="gl_modal-content">{{ $slot }}</div>
    @endif

    @isset($footer)
        <div class="gl_modal-footer {{ $footerLayout }}">{{ $footer }}</div>
    @endisset
</div>
