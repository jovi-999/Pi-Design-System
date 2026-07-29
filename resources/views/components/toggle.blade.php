{{--
    Pi DS — Toggle（開關）

    對應 resources/scss/components/_toggle.scss。
    結構沿用 blade 化前的 preview-static/form.html（該目錄已廢除，見 CHANGELOG）：

      <label class="gl_toggle gl_toggle-{size} gl_toggle-{tone}">
        <input type="checkbox" class="toggle-checkbox" />
        <span class="knobs"></span>
        <span class="layer"></span>
      </label>

    三個子元素的順序不能換：SCSS 用的是相鄰／後續兄弟選擇器
    （.toggle-checkbox:checked + .knobs、:checked ~ .layer），
    input 必須在最前面，knobs 必須緊接其後。
--}}
@props([
    'size' => 'md',     // lg | md | sm | xs
    'tone' => 'basic',  // basic | info | success | warning | danger
    'name' => null,
    'value' => null,
    'checked' => false,
    'disabled' => false,
    'id' => null,       // 要落在內層 checkbox 上。.gl_toggle 本身是 <label>，
                        // id 掛在它身上的話外部 <label for> 會指向一個 label，無效。
])

@php
    $sizes = ['lg', 'md', 'sm', 'xs'];
    if (! in_array($size, $sizes, true)) {
        throw new InvalidArgumentException(
            'x-pi::toggle 的 size 只能是 ' . implode(' / ', $sizes) . "，收到 [{$size}]。"
            . '（toggle 沒有 xl）'
        );
    }
    // _toggle.scss 只定義了這 5 個 tone（沒有 purple / orange）
    $tones = ['basic', 'info', 'success', 'warning', 'danger'];
    if (! in_array($tone, $tones, true)) {
        throw new InvalidArgumentException(
            'x-pi::toggle 的 tone 只能是 ' . implode(' / ', $tones) . "，收到 [{$tone}]。"
            . '（toggle 沒有 purple）'
        );
    }
@endphp

<label {{ $attributes->class(['gl_toggle', "gl_toggle-{$size}", "gl_toggle-{$tone}"]) }}>
    <input
        type="checkbox"
        class="toggle-checkbox"
        @if ($id) id="{{ $id }}" @endif
        @if ($name) name="{{ $name }}" @endif
        @if ($value !== null) value="{{ $value }}" @endif
        @checked($checked)
        @disabled($disabled)
    />
    <span class="knobs"></span>
    <span class="layer"></span>
</label>
