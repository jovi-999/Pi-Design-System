{{--
    Page prototype：interview 專案的 ds-card-list 頁。

    版面：2 欄（main 8 / sidebar 4）。主欄是 3 張橫式卡片（圖左 / 內容右：
    標題 1 行 + 內文 2 行 + 按鈕），側欄是導覽連結清單。
    對應 handoff：.scratch/ds-card-list/{frontend,backend}-handoff.md

    交接進專案時只動兩行：
      1. 刪掉下面兩行 @piFixture —— 資料改由 controller 傳入
      2. @extends('pi::layouts.preview') 換成專案自己的 layout

    @section('content') 內的 body 一個字都不用改。

    ── 兩個要交接的缺口 ────────────────────────────────────
    ⚠️ 組合件：卡片。設計系統沒有 card 元件（13 支裡沒有），這裡是
       gl_border-outer + gl_radius-lg + gl_shadow-sm 疊出來的，不是正式元件。
       同一結構重複 3 次，已在前端 handoff 提報 card 為候選缺口。
       圖片同理 —— 沒有 image / thumbnail 元件，用裸 <img> + page-scoped class。

    🆕 nav-link（PM 已同意的新件）：sidebar 的「當前頁」高亮沒有元件可用。
       _dropdown.scss 沒有任何 active modifier（已讀確認），
       content-switcher 的 .is-active 綁死在水平底線 + border-bottom 容器上，
       垂直清單套不了。current 那一項用 placeholder 畫法頂替。
--}}
@piFixture($cards, 'ds-card-list')
@piFixture($navLinks, 'ds-card-list-nav')

@extends('pi::layouts.preview')

@section('title', '面試邀約')

@section('content')
    {{--
        自訂樣式 13 行，未超過 CLAUDE.md 第 3 條的 30 行上限。
        全部是排版（grid / flex / 截斷），沒有任何顏色或字級決策 ——
        那些都走既有 class（fz-* / text-* / gl_*）與 token（var(--sp-*) / var(--cl-*)）。

        media query 的 768px 是 --bp-md 的值。CSS 的 @media 條件不吃 CSS var，
        所以只能寫實際值，不是自創斷點。
    --}}
    <style>
        .pt-page { max-width: 1120px; margin: 0 auto; padding: 40px 24px; }
        .pt-head { margin-bottom: var(--sp-6); }
        .pt-layout { display: grid; grid-template-columns: 8fr 4fr; gap: var(--sp-6); align-items: start; }
        .pt-main { display: flex; flex-direction: column; gap: var(--sp-4); }
        .pt-card { display: flex; gap: var(--sp-4); padding: var(--sp-4); background: var(--cl-basic-0); }
        .pt-card__media { flex: 0 0 160px; overflow: hidden; }
        .pt-card__img { display: block; width: 100%; height: 100%; object-fit: cover; }
        .pt-card__body { display: flex; flex-direction: column; align-items: flex-start; gap: var(--sp-2); min-width: 0; }
        .pt-card__title { max-width: 100%; overflow: hidden; white-space: nowrap; text-overflow: ellipsis; }
        .pt-card__text { display: -webkit-box; -webkit-box-orient: vertical; -webkit-line-clamp: 2; overflow: hidden; }
        .pt-nav { display: flex; flex-direction: column; gap: var(--sp-1); padding: var(--sp-3); background: var(--cl-basic-0); }
        .pt-nav__current { display: flex; flex-direction: column; gap: var(--sp-1); padding: var(--sp-1); }
        @media (max-width: 768px) { .pt-layout { grid-template-columns: 1fr; } }
    </style>

    <div class="pt-page">
        <div class="pt-head">
            <h1 class="fz-headline-sm fz-tit">面試邀約</h1>
        </div>

        <div class="pt-layout">
            {{-- 主欄（8）：卡片列表 --}}
            <div class="pt-main">
                @foreach ($cards as $card)
                    {{-- ⚠️ 組合件：卡片外框 = 描邊 + 圓角 + 陰影三個 utility class 疊加 --}}
                    <article class="pt-card gl_border-outer gl_radius-lg gl_shadow-sm">
                        <div class="pt-card__media gl_radius-md">
                            <img class="pt-card__img" src="{{ $card['image'] }}" alt="{{ $card['imageAlt'] }}">
                        </div>

                        <div class="pt-card__body">
                            <div class="pt-card__title fz-title-md fz-tit">{{ $card['title'] }}</div>
                            <p class="pt-card__text fz-body-sm text-basic-700">{{ $card['excerpt'] }}</p>

                            <x-pi::button
                                as="a"
                                size="xs"
                                tone="success"
                                :href="$card['action']['href']"
                            >{{ $card['action']['label'] }}</x-pi::button>
                        </div>
                    </article>
                @endforeach
            </div>

            {{-- 側欄（4）：導覽連結 --}}
            <aside class="pt-nav gl_border-outer gl_radius-lg">
                @foreach ($navLinks as $link)
                    @if ($link['current'])
                        {{--
                            🆕 nav-link（待確認）：暫用 dropdown-item 頂替，外層包
                            gl_border-outer 框出範圍。正式的 active 視覺（底色？左指示條？
                            字色字重？）由前端定案，不在這裡自己挑一個色階。
                            無 dashed border token（已查 resources/scss/），故用現有 solid 描邊。
                        --}}
                        <div class="pt-nav__current gl_border-outer gl_radius-sm">
                            <div class="fz-body-xs text-basic-500">🆕 nav-link active（待確認）</div>
                            <x-pi::dropdown-item :href="$link['href']" :icon="$link['icon']">
                                {{ $link['label'] }}
                            </x-pi::dropdown-item>
                        </div>
                    @else
                        <x-pi::dropdown-item :href="$link['href']" :icon="$link['icon']">
                            {{ $link['label'] }}
                        </x-pi::dropdown-item>
                    @endif
                @endforeach
            </aside>
        </div>
    </div>
@endsection
