{{--
    三區入口。取代原本 `/` 直接 redirect 到 /prototypes 的行為 ——
    preview-static/ 廢除後這裡是唯一的入口，要看得到全部三區。
--}}
@extends('pi::layouts.preview')

@section('title', 'Pi Design System — Preview')

@section('content')
<main class="pv-main" style="max-width: 880px; margin: 0 auto;">
    <header class="pv-header">
        <h1 class="fz-headline-sm fz-tit">Pi Design System</h1>
        <p class="fz-body-md" style="color: var(--fg-2);">
            本機 preview。三區的清單都由檔案掃描生成，新增內容不需要改 preview。
        </p>
    </header>

    <table class="pv-props fz-body-sm">
        <thead>
            <tr><th style="width: 22%;">區</th><th style="width: 12%;">數量</th><th>內容</th></tr>
        </thead>
        <tbody>
            <tr>
                <td><a href="{{ route('foundation.index') }}">Foundation</a></td>
                <td>{{ $tokenCount }} token<br>{{ $iconCount }} icon</td>
                <td>色彩 / 字體 / 間距 / 圓角 / 陰影 / 動態 / 斷點 / icon。掃 <code>resources/scss/tokens/</code> 與 <code>assets/</code></td>
            </tr>
            <tr>
                <td><a href="{{ route('components.index') }}">元件</a></td>
                <td>{{ $componentCount }} 支</td>
                <td>每支的 props / 注意事項 / 可跑範例。掃 <code>resources/views/components/*.meta.php</code></td>
            </tr>
            <tr>
                <td><a href="{{ route('prototypes.index') }}">Prototype</a></td>
                <td>{{ $prototypeCount }} 份</td>
                <td>PM 與 AI 討論中的 page / fragment。掃 <code>prototypes/</code></td>
            </tr>
        </tbody>
    </table>

    <section class="pv-section" style="margin-top: 40px;">
        <div class="pv-section__head">
            <h2 class="fz-title-lg fz-tit">起動</h2>
        </div>
        <pre class="pv-code">cd preview
docker compose up -d      # PHP  → http://localhost:8100
npm run dev               # Vite → 5178</pre>
        <p class="fz-body-sm" style="color: var(--fg-2);">
            設計取捨與常見問題見 <code>preview/README.md</code>。
        </p>
    </section>
</main>
@endsection
