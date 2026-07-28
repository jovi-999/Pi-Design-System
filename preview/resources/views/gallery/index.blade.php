{{-- 元件目錄首頁。清單來自 meta 檔，新增元件不需要改這支。 --}}
@extends('pi::layouts.preview')

@section('title', 'Pi DS 元件目錄')

@section('content')
<div class="pv-layout">
    @include('gallery._sidebar')

    <main class="pv-main">
        <header class="pv-header">
            <h1 class="fz-headline-sm fz-tit">Pi DS 元件目錄</h1>
            <p class="fz-body-md" style="color: var(--fg-2);">
                共 {{ $components->count() }} 支元件。清單由
                <code>resources/views/components/*.meta.php</code> 自動掃出 ——
                新增元件只要放 <code>&lt;name&gt;.blade.php</code> +
                <code>&lt;name&gt;.meta.php</code>，這頁與 CLAUDE.md 的元件清單都會跟上。
            </p>
        </header>

        <table class="pv-props fz-body-sm">
            <thead>
                <tr>
                    <th>Tag</th>
                    <th>名稱</th>
                    <th>說明</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($components as $slug => $item)
                    <tr>
                        <td>
                            <a href="{{ route('components.show', $slug) }}">{{ $item['tag'] }}</a>
                            @unless ($item['hasBlade'])
                                <span class="pv-error">缺 blade 檔</span>
                            @endunless
                        </td>
                        <td>{{ $item['name'] }}</td>
                        <td>{{ $item['description'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </main>
</div>
@endsection
