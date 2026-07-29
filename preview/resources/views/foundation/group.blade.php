{{--
    單一 token 群組。一支 view 服務全部 7 個分頁 —— 差異只在預覽格的畫法，
    由 group.kind 決定（見 _token-table）。

    字體頁多一段 .fz-* class；陰影頁多一段 Ring 組合（那是設計系統的既有用法：
    .gl_shadow-* 疊 .gl_border-outer / .gl_border-inner，不是新元件）。
--}}
@extends('pi::layouts.preview')

@section('title', $group['label'] . ' — Pi DS')

@section('content')
<div class="pv-layout">
    @include('foundation._sidebar', ['current' => $group['file']])

    <main class="pv-main">
        <header class="pv-header">
            <h1 class="fz-headline-sm fz-tit">{{ $group['label'] }}</h1>
            @if ($description)
                <p class="fz-body-md" style="color: var(--fg-2);">{{ $description }}</p>
            @endif
            <p><span class="pv-tag fz-body-sm">resources/scss/tokens/_{{ $group['file'] }}.scss</span></p>
        </header>

        <section class="pv-section">
            <div class="pv-section__head">
                <h2 class="fz-title-lg fz-tit">Token（{{ count($group['tokens']) }}）</h2>
            </div>
            @include('foundation._token-table', ['group' => $group])
        </section>

        @if ($typeClasses !== [])
            <section class="pv-section">
                <div class="pv-section__head">
                    <h2 class="fz-title-lg fz-tit">字級 class（{{ count($typeClasses) }}）</h2>
                </div>
                <table class="pv-props fz-body-sm">
                    <thead>
                        <tr><th style="width: 40%;">樣本</th><th>Class</th><th>宣告</th></tr>
                    </thead>
                    <tbody>
                        @foreach ($typeClasses as $item)
                            <tr>
                                <td><span class="{{ ltrim($item['class'], '.') }}">設計系統 Ag</span></td>
                                <td>
                                    <button type="button" class="pv-copy" data-copy="{{ ltrim($item['class'], '.') }}"
                                            title="點擊複製 class 名">{{ $item['class'] }}</button>
                                </td>
                                <td><code>{{ $item['declarations'] }}</code></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <p class="fz-body-sm" style="color: var(--fg-3); margin-top: 8px;">
                    {{-- @@ 是 Blade 的轉義：輸出字面的 @，否則 @include 會被當成 Blade directive --}}
                    標題字重用 <code>.fz-tit</code> 疊加（元件內部請用 <code>@@include fz-tit;</code>，
                    不要 <code>@@extend</code> —— 會讓樣式逃出 <code>@layer pi</code>）。
                </p>
            </section>
        @endif

        @if ($group['file'] === 'shadow')
            <section class="pv-section">
                <div class="pv-section__head">
                    <h2 class="fz-title-lg fz-tit">Ring（陰影 + 描邊的組合）</h2>
                </div>
                <p class="fz-body-sm" style="color: var(--fg-2);">
                    設計系統**沒有** <code>ring</code> 這個 token 或元件。所謂 Ring 是
                    <code>.gl_shadow-*</code> 疊加 <code>.gl_border-outer</code> 或
                    <code>.gl_border-inner</code> 的既有用法。
                </p>
                <div class="pv-demo">
                    <div class="pv-ring gl_shadow-md gl_border-outer">
                        <code class="fz-body-xs">gl_shadow-md + gl_border-outer</code>
                    </div>
                    <div class="pv-ring gl_shadow-md gl_border-inner">
                        <code class="fz-body-xs">gl_shadow-md + gl_border-inner</code>
                    </div>
                </div>
            </section>
        @endif
    </main>
</div>
@endsection

@push('scripts')
    @include('foundation._scripts')
@endpush
