{{-- Foundation 側邊目錄。分頁清單來自 FoundationController::PAGES。 --}}
<aside class="pv-sidebar">
    <nav class="pv-nav fz-body-sm">
        <a href="{{ route('home') }}">← 全部</a>
    </nav>

    <div class="pv-sidebar__title fz-title-xs fz-tit">Foundation</div>
    <nav class="pv-nav fz-body-sm">
        <a href="{{ route('foundation.tokens') }}"
           @if (($current ?? null) === 'tokens') aria-current="page" @endif>全部 token</a>

        @foreach ($pages as $key => $desc)
            <a href="{{ route('foundation.group', $key) }}"
               @if (($current ?? null) === $key) aria-current="page" @endif>{{ $key }}</a>
        @endforeach

        <a href="{{ route('foundation.icons') }}"
           @if (($current ?? null) === 'icons') aria-current="page" @endif>icons</a>
    </nav>

    <div class="pv-sidebar__title fz-title-xs fz-tit">其他</div>
    <nav class="pv-nav fz-body-sm">
        <a href="{{ route('components.index') }}">元件</a>
        <a href="{{ route('prototypes.index') }}">Prototype</a>
    </nav>
</aside>
