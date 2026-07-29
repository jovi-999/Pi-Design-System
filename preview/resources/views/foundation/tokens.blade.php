{{-- 全部 token 一頁列完 + 搜尋。取代舊的 preview-static/tokens.html（26 KB 手維護 HTML）。 --}}
@extends('pi::layouts.preview')

@section('title', '全部 token — Pi DS')

@section('content')
<div class="pv-layout">
    @include('foundation._sidebar', ['current' => 'tokens'])

    <main class="pv-main">
        <header class="pv-header">
            <h1 class="fz-headline-sm fz-tit">全部 token</h1>
            <p class="fz-body-md" style="color: var(--fg-2);">
                共 {{ $tokenCount }} 個。點名稱複製 <code>var(--x)</code>。
                「值」欄是瀏覽器解析後的實際值。
            </p>

            <div class="pv-search">
                <input
                    type="search"
                    class="gl_form-control"
                    placeholder="搜尋 token 名稱或說明"
                    data-search-input
                    aria-label="搜尋 token"
                >
                <span class="fz-body-sm" style="color: var(--fg-3); white-space: nowrap;">
                    顯示 <span data-search-count>{{ $tokenCount }}</span> / {{ $tokenCount }}
                </span>
            </div>
        </header>

        @foreach ($groups as $key => $group)
            <section class="pv-section">
                <div class="pv-section__head">
                    <h2 class="fz-title-lg fz-tit">{{ $group['label'] }}</h2>
                    <span class="pv-tag fz-body-sm">tokens/_{{ $key }}.scss</span>
                </div>

                @include('foundation._token-table', ['group' => $group])
            </section>
        @endforeach
    </main>
</div>
@endsection

@push('scripts')
    @include('foundation._scripts')
@endpush
