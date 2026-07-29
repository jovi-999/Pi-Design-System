{{-- Icon 索引。清單來自套件的 assets/icon-names.json + icon-cp-map.json。 --}}
@extends('pi::layouts.preview')

@section('title', 'Icon — Pi DS')

@section('content')
<div class="pv-layout">
    @include('foundation._sidebar', ['current' => 'icons'])

    <main class="pv-main">
        <header class="pv-header">
            <h1 class="fz-headline-sm fz-tit">Icon</h1>
            <p class="fz-body-md" style="color: var(--fg-2);">
                {{-- 引號也要 &quot; 轉義：raw HTML 裡只要出現 `class="` 字面，
                     grep 與靜態檢查就會把它當成真的 class attribute（實際踩過）。
                     轉義後畫面完全一樣，但原始碼裡沒有那個字面。 --}}
                共 {{ count($icons) }} 個。用法
                <code>&lt;i class=&quot;icon icon-{name}&quot;&gt;&lt;/i&gt;</code>，
                點格子複製 class 名。
            </p>

            <div class="pv-search">
                <input
                    type="search"
                    class="gl_form-control"
                    placeholder="搜尋 icon 名稱"
                    data-search-input
                    aria-label="搜尋 icon"
                >
                <span class="fz-body-sm" style="color: var(--fg-3); white-space: nowrap;">
                    顯示 <span data-search-count>{{ count($icons) }}</span> / {{ count($icons) }}
                </span>
            </div>
        </header>

        @if ($missing !== [])
            {{-- 兩支 JSON 不同步的訊號，要看得見 --}}
            <div class="pv-error fz-body-sm" style="margin-bottom: 24px;">
                <strong>{{ count($missing) }} 個 icon 在 icon-cp-map.json 裡找不到 codepoint</strong><br>
                {{ implode('、', $missing) }}
            </div>
        @endif

        <div class="pv-icons">
            @foreach ($icons as $icon)
                <button
                    type="button"
                    class="pv-icon pv-copy"
                    data-copy="{{ $icon['class'] }}"
                    data-search="{{ $icon['name'] }} {{ $icon['class'] }}"
                    title="{{ $icon['class'] }}{{ $icon['codepoint'] ? ' · \\e' . $icon['codepoint'] : '' }}"
                >
                    <i class="icon {{ $icon['class'] }}"></i>
                    <span class="pv-icon__name fz-body-xs">{{ $icon['name'] }}</span>
                </button>
            @endforeach
        </div>
    </main>
</div>
@endsection

@push('scripts')
    @include('foundation._scripts')
@endpush
