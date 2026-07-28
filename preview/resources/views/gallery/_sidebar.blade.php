{{-- 元件側邊目錄。由 ComponentCatalog 掃 meta 生成，不手維護清單。 --}}
<aside class="pv-sidebar">
    <div class="pv-sidebar__title fz-title-xs fz-tit">元件（{{ $components->count() }}）</div>
    <nav class="pv-nav fz-body-sm">
        @foreach ($components as $slug => $item)
            <a
                href="{{ route('components.show', $slug) }}"
                @if (($current ?? null) === $slug) aria-current="page" @endif
            >{{ $item['name'] }}</a>
        @endforeach
    </nav>
</aside>
