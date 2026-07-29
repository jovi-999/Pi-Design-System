# ds-card-list（面試邀約）— 前端 Handoff

專案：`interview`　類型：page prototype

## 版面結構

```
iv-page（max-width 1120px，置中）
├── iv-head
│   └── h1 面試邀約
└── iv-layout（CSS grid，8fr / 4fr，gap var(--sp-6)，align-items: start）
    ├── iv-main（8）— 垂直堆疊，gap var(--sp-4)
    │   └── 卡片 ×3（資料驅動，@foreach $cards）
    │       ├── iv-card__media（固定寬 160px）→ img
    │       └── iv-card__body
    │           ├── 標題（1 行，超出 ellipsis）
    │           ├── 內文（2 行，超出 -webkit-line-clamp 截斷）
    │           └── 按鈕
    └── iv-nav（4）— 導覽連結清單，垂直堆疊，gap var(--sp-1)
        └── 連結 ×5（資料驅動，@foreach $navLinks；current 那一項是 placeholder）
```

≤768px（`--bp-md`）收成單欄。

## 元件清單

| 實例 | Pi DS 元件 | props |
|---|---|---|
| 卡片的行動按鈕 | `<x-pi::button>` | `as="a" size="xs" tone="success" :href`（variant 用預設 `solid`） |
| Sidebar 導覽連結 | `<x-pi::dropdown-item>` | `:href :icon`（size 用預設 `md`） |

卡片本身與圖片**沒有對應元件** —— 見下方「組合件」。

## 歧義收斂

- 「導覽列連結」→ `<x-pi::dropdown-item href>`（render 成 `a.gl_dropdown-item`，有 hover 底色）。
  不是 `<x-pi::content-switcher>` —— 那是水平分頁，`.is-active` 綁死在 `border-bottom` 容器 + 底部 `::after` 短底線上，垂直清單套不了。

## 組合件（⚠️ 待確認）

兩個都是缺件門檻第 2 層（現有 token 疊得出、無 JS 行為），prototype 直接跑，但**不是正式元件**：

- **卡片外框** — `gl_border-outer`（1px outline）+ `gl_radius-lg`（16px）+ `gl_shadow-sm`，底色 `var(--cl-basic-0)`，內距 `var(--sp-4)` — ⚠️ 待確認「組合而非正式元件」
- **卡片縮圖** — 設計系統沒有 image / thumbnail 元件，用裸 `<img>` + page-scoped class（`object-fit: cover`）— ⚠️ 待確認

### 候選缺口：`card` — **PM 已決定不立案**

同一卡片結構在本頁重複 3 次，已超出「一次性組合」的範圍，曾提報為候選元件缺口。

**PM 決定：不立案，卡片樣式到各專案後自行改成專案獨有的樣式。**

因此設計系統不會有 `card` 元件，本頁的三 class 疊加 + page-scoped 排版就是最終交付物 ——
interview 端可以直接改它，不需要回頭跟設計系統對齊。

已知後果（決策已定，記錄用）：跨頁的圓角階、陰影階、內距不會一致，且沒有機制會抓到。

原提議留檔備查：

| # | 欄位 | 內容 |
|---|---|---|
| 1 | 暫定名稱 + 用途 | `card`（新元件）—— 內容卡片容器，橫式變體為「媒體 + 內容」兩欄 |
| 2 | 最接近的現有件 | 無元件。目前是 `gl_border-outer` + `gl_radius-lg` + `gl_shadow-sm` 三個 utility class 疊加 |
| 3 | 為何組不出 | 疊得出視覺，但「哪個 radius 階 + 哪個 shadow 階 + 內距多少」每次組都要重新決定，跨頁一定 drift |
| 4 | prototype 先用什麼頂替 | 就是現在這個三 class 疊加 + page-scoped 排版 |


## Page-scoped SCSS

- **13 行**（`<style>` 13 + inline 0），上限 30 —— CI（`php scripts/check-prototypes.php`）已通過。
- 用途：純排版（grid 8/4、flex 堆疊、標題 1 行 ellipsis、內文 2 行 line-clamp、單欄斷點）。
- 零顏色與字級決策 —— 全走既有 class（`fz-title-md` / `fz-body-sm` / `fz-tit` / `text-basic-700` / `text-basic-500`）與 token（`var(--sp-*)` / `var(--cl-basic-0)`）。
- `@media` 的 `768px` 是 `--bp-md` 的值（CSS 的 `@media` 條件不吃 CSS var，只能寫實際值），不是自創斷點。

## 狀態 + 互動（AI 推測）

- 頁面級：整頁 loading / 卡片列表為空的空狀態 / 導覽項的權限控制 — ⚠️ 待確認
- 卡片級：hover 是否要抬升（shadow 升階）？整張卡是否可點（目前只有按鈕可點）？ — ⚠️ 待確認
- 導覽級：`current` 由誰判定（route name？controller 傳入？）— ⚠️ 待確認
- 按鈕：loading / disabled 狀態 — ⚠️ 待確認
- 卡片數量超過一頁時要不要 `<x-pi::pagination>` — ⚠️ 待確認

## 待確認新件

### `nav-link` — 🆕 PM 已同意為新件，但**實作由各專案自行處理**

**PM 決定：設計系統這輪不做 `nav-link`，前端到各別專案後自行實作 active 樣式。**

所以 interview 端要做的是：拆掉下方 placeholder（`iv-nav__current` 的框 + 可見標籤
「🆕 nav-link active（待確認）」），換成 interview 自己的 active 視覺。
**不要用 `gl_` 前綴** —— 那是設計系統出貨的命名空間，自訂的 active 樣式不屬於它。

已知後果（決策已定，記錄用）：每個專案會長出不同的 active 視覺，設計系統不會知道，
因為缺口被各專案自行實作吸收掉了。

原提議留檔備查：

| # | 欄位 | 內容 |
|---|---|---|
| 1 | 暫定名稱 + 用途 | `nav-link`（新元件）—— 垂直導覽清單裡的單一連結，含「當前頁」狀態 |
| 2 | 最接近的現有件 | `<x-pi::dropdown-item>`（`a.gl_dropdown-item`：hover 底色 `$cl-basic-50`、前置 icon、md/sm 兩尺寸）。狀態指示概念最近的是 `content-switcher` 的 `.is-active` |
| 3 | 為何組不出 | active 的視覺規格（底色色階？左側指示條？字色 + 字重？）是設計系統該定的事。`_dropdown.scss` 沒有任何 active modifier；`content-switcher.is-active` 綁死在水平底線 + `border-bottom` 容器上，垂直清單套不了。prototype 自己挑一個色階疊出來 = 把缺口藏起來 |
| 4 | prototype 先用什麼頂替 | `<x-pi::dropdown-item href>` 當殼，current 那一項外層包 `gl_border-outer` + `gl_radius-sm` 框出範圍，加可見標籤「🆕 nav-link active（待確認）」 |

無 dashed border token（已查 `resources/scss/` 確認），故 placeholder 用現有 solid 描邊。

## Prototype

- 路徑 `prototypes/interview/pages/ds-card-list.blade.php`
- 預覽 `http://localhost:8100/prototypes/interview/ds-card-list`
- 起動：`cd preview && docker compose up -d && npm run dev`

## 套用方式

```bash
php scripts/apply.php interview ds-card-list          # 印出可貼的 blade
```

只動兩處：刪掉 `@piFixture` 那兩行（資料改由 controller 傳入）、`@extends('pi::layouts.preview')` 換成專案 layout。`@section('content')` 內的 body 一個字都不用改。

`--output=patch` 不適用 page（page 是整頁新檔，沒有插入點）。
