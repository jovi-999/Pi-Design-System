{{--
    Pi DS — Form control

    對應 resources/scss/components/_form.scss。
    一支元件涵蓋 .gl_form-group 這個群組的完整結構，因為 SCSS 是這樣寫的：
    .gl_form-control 的樣式定義在 .gl_form-group 底下，兩者拆開用會沒有樣式。

    結構（照 preview-static/form.html 的既有 markup）：

      <div class="gl_form-group [gl_icon-input-wrap] [is-valid|is-invalid]">
        [<i class="icon …">]              ← icon prop
        <input|select|textarea class="gl_form-control">
        [<div class="form-prompt-text [is-info|is-warning]">…]   ← prompt
        [<div class="form-feedback">…]                            ← state + feedback
      </div>
--}}
@props([
    'type' => 'text',       // 原生 input type，或 'select' / 'textarea'
    'name' => null,
    'value' => null,
    'placeholder' => null,
    'options' => [],        // type=select 時的選項：[['value'=>…,'label'=>…], …] 或 ['值' => '標籤']
    'icon' => null,         // 前置 icon class，會加上 .gl_icon-input-wrap
    'prompt' => null,       // 說明文字
    'promptTone' => null,   // null | info | warning
    'promptIcon' => 'icon-info',
    'state' => null,        // null | valid | invalid
    'feedback' => null,     // 驗證訊息
    'feedbackIcon' => null, // 不給就依 state 自動選
    'disabled' => false,    // 需要獨立 prop：$attributes 會落在外層 div，
                            // 但 _form.scss 的停用樣式是 .gl_form-control[disabled]
    'id' => null,           // 同理：id 要落在真正的 control 上，<label for> 才綁得到。
                            // 若跟著 $attributes 走會綁到外層 div，關聯無效。
])

@php
    $tones = ['info', 'warning'];
    if ($promptTone !== null && ! in_array($promptTone, $tones, true)) {
        throw new InvalidArgumentException(
            'x-pi::form-control 的 promptTone 只能是 info / warning 或不給，收到 [' . $promptTone . ']'
        );
    }
    $states = ['valid', 'invalid'];
    if ($state !== null && ! in_array($state, $states, true)) {
        throw new InvalidArgumentException(
            'x-pi::form-control 的 state 只能是 valid / invalid 或不給，收到 [' . $state . ']'
        );
    }

    // .is-valid / .is-invalid 的 SCSS 是 `.is-valid .form-feedback`，
    // 所以狀態 class 掛在 group 上、不是掛在 feedback 上。
    $stateClass = $state ? "is-{$state}" : null;

    // 未指定就依狀態選 icon（兩支都是 symicon 既有的 class）
    $resolvedFeedbackIcon = $feedbackIcon
        ?? ($state === 'invalid' ? 'icon-alert-triangle' : 'icon-checked');

    // select 的選項統一正規化成 [value, label]
    $normalizedOptions = collect($options)->map(function ($option, $key) {
        if (is_array($option)) {
            return ['value' => $option['value'] ?? $key, 'label' => $option['label'] ?? $option['value'] ?? $key];
        }

        return ['value' => is_int($key) ? $option : $key, 'label' => $option];
    })->values();
@endphp

<div {{ $attributes->class([
    'gl_form-group',
    'gl_icon-input-wrap' => (bool) $icon,
    $stateClass => (bool) $stateClass,
]) }}>
    @if ($icon)
        <i class="icon {{ $icon }}"></i>
    @endif

    @if ($type === 'select')
        <select class="gl_form-control" @if ($id) id="{{ $id }}" @endif @if ($name) name="{{ $name }}" @endif @disabled($disabled)>
            @if ($placeholder)
                <option value="">{{ $placeholder }}</option>
            @endif
            @foreach ($normalizedOptions as $option)
                <option value="{{ $option['value'] }}" @selected($value !== null && $value == $option['value'])>
                    {{ $option['label'] }}
                </option>
            @endforeach
        </select>
    @elseif ($type === 'textarea')
        <textarea
            class="gl_form-control"
            @if ($id) id="{{ $id }}" @endif
            @if ($name) name="{{ $name }}" @endif
            @if ($placeholder) placeholder="{{ $placeholder }}" @endif
            @disabled($disabled)
        >{{ $value }}</textarea>
    @else
        <input
            class="gl_form-control"
            @if ($id) id="{{ $id }}" @endif
            type="{{ $type }}"
            @if ($name) name="{{ $name }}" @endif
            @if ($value !== null) value="{{ $value }}" @endif
            @if ($placeholder) placeholder="{{ $placeholder }}" @endif
            @disabled($disabled)
        />
    @endif

    @if ($prompt)
        <div @class(['form-prompt-text', "is-{$promptTone}" => (bool) $promptTone])>
            <i class="icon {{ $promptIcon }}"></i><span>{{ $prompt }}</span>
        </div>
    @endif

    @if ($feedback)
        <div class="form-feedback">
            <i class="icon {{ $resolvedFeedbackIcon }}"></i><span>{{ $feedback }}</span>
        </div>
    @endif
</div>
