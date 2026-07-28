{{--
    單一元件頁：props 表 + notes + 每個 example 的「渲染結果 + 原始碼」對照。

    渲染結果與原始碼同時顯示，是為了讓 agent 有可複製的正確用法、
    讓人有可驗收的畫面 —— 兩邊出自同一份 meta，不會不同步。
--}}
@extends('pi::layouts.preview')

@section('title', $component['name'] . ' — Pi DS')

@section('content')
<div class="pv-layout">
    @include('gallery._sidebar', ['current' => $component['slug']])

    <main class="pv-main">
        <header class="pv-header">
            <h1 class="fz-headline-sm fz-tit">{{ $component['name'] }}</h1>
            <p class="fz-body-md" style="color: var(--fg-2);">{{ $component['description'] }}</p>
            <p>
                <span class="pv-tag fz-body-sm">{{ $component['tag'] }}</span>
                @if (! empty($component['scss']))
                    <span class="pv-tag fz-body-sm">{{ $component['scss'] }}</span>
                @endif
            </p>
        </header>

        @if (! empty($component['props']))
            <section class="pv-section">
                <div class="pv-section__head">
                    <h2 class="fz-title-lg fz-tit">Props</h2>
                </div>
                <table class="pv-props fz-body-sm">
                    <thead>
                        <tr><th>Prop</th><th>可用值 / 說明</th></tr>
                    </thead>
                    <tbody>
                        @foreach ($component['props'] as $prop => $spec)
                            <tr>
                                <td>{{ $prop }}</td>
                                <td>
                                    @if (is_array($spec))
                                        {{ collect($spec)->map(fn ($v) => $v === null ? '（不給）' : $v)->implode(' · ') }}
                                    @else
                                        {{ $spec }}
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </section>
        @endif

        @if (! empty($component['slots']))
            <section class="pv-section">
                <div class="pv-section__head">
                    <h2 class="fz-title-lg fz-tit">Slots</h2>
                </div>
                <table class="pv-props fz-body-sm">
                    <tbody>
                        @foreach ($component['slots'] as $slot => $desc)
                            <tr><td>{{ $slot }}</td><td>{{ $desc }}</td></tr>
                        @endforeach
                    </tbody>
                </table>
            </section>
        @endif

        @if (! empty($component['notes']))
            <section class="pv-section">
                <div class="pv-section__head">
                    <h2 class="fz-title-lg fz-tit">注意事項</h2>
                </div>
                <ul class="pv-notes fz-body-sm">
                    @foreach ($component['notes'] as $note)
                        <li>{{ $note }}</li>
                    @endforeach
                </ul>
            </section>
        @endif

        @foreach ($rendered as $example)
            <section class="pv-section">
                <div class="pv-section__head">
                    <h2 class="fz-title-lg fz-tit">{{ $example['label'] }}</h2>
                </div>

                @if ($example['error'])
                    <div class="pv-error fz-body-sm">
                        範例 render 失敗：{{ $example['error'] }}
                    </div>
                @else
                    {{-- 表單類元件是 width:100% 的區塊，橫排會擠成一團 --}}
                    <div @class(['pv-demo', 'pv-demo--stack' => str_contains($example['code'], 'form-control')])>
                        {!! $example['html'] !!}
                    </div>
                @endif

                <pre class="pv-code">{{ $example['code'] }}</pre>
            </section>
        @endforeach
    </main>
</div>
@endsection
