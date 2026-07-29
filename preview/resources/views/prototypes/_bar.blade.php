{{--
    Prototype 預覽的導覽列。

    ⚠️ 三個限制決定了它為什麼長這樣：

    1. **全部用 inline style，不用 _chrome.scss 的 pv-* class。**
       fragment 會被注入宿主快照，而快照載的是**專案自己的 compiled CSS** ——
       我們的 class 在那個環境不存在。inline style 才能在任何宿主裡自給自足。

    2. **不能污染交接產物。** page 靠 layout 帶入（交接時 @extends 被換掉，
       bar 就消失）；fragment 靠 controller 注入 rendered HTML（apply.php 讀的是
       原始檔，不經過這裡）。兩條路都不會讓 bar 流進專案。

    3. **要看得出「這不是 prototype 的一部分」。** 深色、貼頂、細，並寫明用途。

    $prototype 需要 name / kind / relativePath 三個 key。
--}}
@php
    $barStyle = implode('; ', [
        'position: sticky',
        'top: 0',
        'z-index: 2147483647',
        'display: flex',
        'align-items: center',
        'gap: 16px',
        'flex-wrap: wrap',
        'padding: 8px 16px',
        'background: #1F2123',
        'color: #FFF',
        'font-family: ui-sans-serif, system-ui, -apple-system, sans-serif',
        'font-size: 12px',
        'line-height: 20px',
    ]);
    $linkStyle = 'color: #FFF; text-decoration: underline; text-underline-offset: 2px;';
    $dimStyle = 'color: rgba(255, 255, 255, .56);';
    $monoStyle = 'font-family: ui-monospace, SFMono-Regular, Menlo, monospace; ' . $dimStyle;
@endphp

<div style="{{ $barStyle }}" data-pv-bar>
    <a href="{{ route('prototypes.index') }}" style="{{ $linkStyle }}">← Prototype 清單</a>
    <a href="{{ route('home') }}" style="{{ $linkStyle }}">首頁</a>

    <span style="{{ $dimStyle }}">|</span>

    <strong>{{ $prototype['project'] }}/{{ $prototype['name'] }}</strong>
    <span style="{{ $dimStyle }}">{{ $prototype['kind'] }}</span>
    <span style="{{ $monoStyle }}">{{ $prototype['relativePath'] }}</span>

    <span style="{{ $dimStyle }}; margin-left: auto;">↑ preview 專用導覽，不屬於 prototype</span>
</div>
