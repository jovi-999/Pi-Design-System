{{-- Foundation 索引。分組與數量都是掃出來的。 --}}
@extends('pi::layouts.preview')

@section('title', 'Foundation — Pi DS')

@section('content')
<div class="pv-layout">
    @include('foundation._sidebar')

    <main class="pv-main">
        <header class="pv-header">
            <h1 class="fz-headline-sm fz-tit">Foundation</h1>
            <p class="fz-body-md" style="color: var(--fg-2);">
                {{ $tokenCount }} 個 token、{{ $iconCount }} 個 icon。
                全部從套件的 <code>resources/scss/tokens/</code> 與 <code>assets/</code>
                掃出來 —— 沒有手維護的清單，加 token 這裡自己就多一列。
            </p>
        </header>

        <section class="pv-section">
            <div class="pv-section__head">
                <h2 class="fz-title-lg fz-tit">Token</h2>
                <a class="fz-body-sm" href="{{ route('foundation.tokens') }}">全部列在一頁（可搜尋）→</a>
            </div>

            <table class="pv-props fz-body-sm">
                <thead>
                    <tr><th>分頁</th><th>數量</th><th>內容</th></tr>
                </thead>
                <tbody>
                    @foreach ($groups as $key => $group)
                        <tr>
                            <td><a href="{{ route('foundation.group', $key) }}">{{ $group['label'] }}</a></td>
                            <td>{{ count($group['tokens']) }}</td>
                            <td>{{ $pages[$key] ?? '' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </section>

        <section class="pv-section">
            <div class="pv-section__head">
                <h2 class="fz-title-lg fz-tit">Icon</h2>
            </div>
            <p class="fz-body-sm" style="color: var(--fg-2);">
                <a href="{{ route('foundation.icons') }}">{{ $iconCount }} 個 symicon →</a>
                　名稱、class、codepoint，點擊複製。
            </p>
        </section>
    </main>
</div>
@endsection
