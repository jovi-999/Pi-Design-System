{{-- Prototype 清單。掃 prototypes/ 生成，不手維護。 --}}
@extends('pi::layouts.preview')

@section('title', 'Prototype 清單')

@section('content')
<div class="pv-main" style="max-width: 1080px; margin: 0 auto;">
    <header class="pv-header">
        <h1 class="fz-headline-sm fz-tit">Prototype</h1>
        <p class="fz-body-md" style="color: var(--fg-2);">
            共 {{ $prototypes->count() }} 份。掃 <code>prototypes/&lt;project&gt;/{pages,fragments}/</code> 生成。
            <a href="{{ route('components.index') }}">→ 元件目錄</a>
        </p>
    </header>

    @if ($prototypes->isEmpty())
        <div class="pv-demo fz-body-sm">
            還沒有任何 prototype。在 <code>prototypes/&lt;project&gt;/pages/</code> 或
            <code>fragments/</code> 放一支 blade 即可。
        </div>
    @else
        <table class="pv-props fz-body-sm">
            <thead>
                <tr>
                    <th>Prototype</th>
                    <th>類型</th>
                    <th>Manifest</th>
                    <th>自訂 SCSS</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($prototypes as $key => $item)
                    <tr>
                        <td>
                            <a href="{{ route('prototypes.show', [$item['project'], $item['name']]) }}">{{ $key }}</a>
                            <div style="color: var(--fg-3);">{{ $item['relativePath'] }}</div>
                        </td>
                        <td>{{ $item['kind'] }}</td>
                        <td>
                            @if ($item['kind'] === 'fragment')
                                @if (empty($item['manifest']))
                                    <span class="pv-error">未宣告 @piFragment</span>
                                @else
                                    @foreach ($item['manifest'] as $k => $v)
                                        <div>{{ $k }}: {{ $v }}</div>
                                    @endforeach
                                @endif
                            @else
                                —
                            @endif
                        </td>
                        <td>
                            {{-- CLAUDE.md 第 3 條的健康指標：超過 30 行就是元件缺口的訊號 --}}
                            @if ($item['pageScopedScssLines'] > 30)
                                <span class="pv-error">{{ $item['pageScopedScssLines'] }} 行 · 超過 30 行門檻</span>
                            @else
                                {{ $item['pageScopedScssLines'] }} 行
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
@endsection
