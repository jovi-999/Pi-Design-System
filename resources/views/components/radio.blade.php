{{--
    Pi DS — Radio

    對應 resources/scss/components/_radio.scss。
    結構與 checkbox 相同三層（layout > label > input + span），
    差別只在 input type 與 ::after 的圓點。
--}}
@props([
    'tone' => 'basic',  // basic | info | success | danger
    'name' => null,     // 同一組 radio 必須共用 name
    'value' => null,
    'checked' => false,
    'disabled' => false,
])

@php
    // _radio.scss 只定義了這 4 個 tone
    $tones = ['basic', 'info', 'success', 'danger'];
    if (! in_array($tone, $tones, true)) {
        throw new InvalidArgumentException(
            'x-pi::radio 的 tone 只能是 ' . implode(' / ', $tones) . "，收到 [{$tone}]。"
            . '（radio 沒有 warning / purple）'
        );
    }
@endphp

<div {{ $attributes->class(['gl_radio-layout', "gl_radio-{$tone}"]) }}>
    <label>
        <input
            type="radio"
            @if ($name) name="{{ $name }}" @endif
            @if ($value !== null) value="{{ $value }}" @endif
            @checked($checked)
            @disabled($disabled)
        />
        {{-- _radio.scss 只對 .fz-body-sm 補了 margin-left，不像 checkbox 有兩種 --}}
        <span class="fz-body-sm">{{ $slot }}</span>
    </label>
</div>
