{{--
    Pi DS — Checkbox

    對應 resources/scss/components/_checkbox.scss。
    結構照 preview-static/form.html：

      <div class="gl_checkbox-layout gl_checkbox-{tone}">
        <label>
          <input type="checkbox" checked />
          <span class="fz-body-sm">標籤</span>
        </label>
      </div>

    input 與 label 的樣式是靠後代選擇器（.gl_checkbox-layout label / input）
    掛上去的，所以這三層結構不能省。
--}}
@props([
    'tone' => 'basic',    // basic | info | success | danger
    'name' => null,
    'value' => null,
    'checked' => false,
    'disabled' => false,
    'labelSize' => 'fz-body-sm', // fz-body-sm | fz-title-sm（SCSS 只對這兩個 class 補 margin-left）
])

@php
    // _checkbox.scss 只定義了這 4 個 tone（沒有 warning / purple / orange）
    $tones = ['basic', 'info', 'success', 'danger'];
    if (! in_array($tone, $tones, true)) {
        throw new InvalidArgumentException(
            'x-pi::checkbox 的 tone 只能是 ' . implode(' / ', $tones) . "，收到 [{$tone}]。"
            . '（checkbox 沒有 warning / purple）'
        );
    }
    $labelSizes = ['fz-body-sm', 'fz-title-sm'];
    if (! in_array($labelSize, $labelSizes, true)) {
        throw new InvalidArgumentException(
            'x-pi::checkbox 的 labelSize 只能是 ' . implode(' / ', $labelSizes)
            . "，收到 [{$labelSize}]。SCSS 只對這兩個 class 補了 margin-left，其他字級會貼在方框上。"
        );
    }
@endphp

<div {{ $attributes->class(['gl_checkbox-layout', "gl_checkbox-{$tone}"]) }}>
    <label>
        <input
            type="checkbox"
            @if ($name) name="{{ $name }}" @endif
            @if ($value !== null) value="{{ $value }}" @endif
            @checked($checked)
            @disabled($disabled)
        />
        <span class="{{ $labelSize }}">{{ $slot }}</span>
    </label>
</div>
