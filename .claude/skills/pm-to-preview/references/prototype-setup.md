# Prototype 機制：目錄、跑法、語法、交接

Prototype 是 blade 而不是 HTML。理由見 `design-guideline-spec.md` 的 D3。

## 目錄結構

```
prototypes/<project>/
├── pages/<name>.blade.php            整頁 prototype
├── fragments/<name>.blade.php        頁面中一塊（要宣告 manifest）
├── fixtures/<name>.php               假資料 = 給後端的資料契約
└── _hosts/<name>.html                專案頁面的 rendered 快照（fragment 才要）
```

`prototypes/` 不出貨（`.gitattributes` 的 `export-ignore`）—— 它是討論區，不是套件內容。

**完整可跑的範例：`prototypes/project-a/`**。動手前先讀那四個檔，那就是產出該有的樣子。

## 啟動與網址

```bash
cd preview
docker compose up -d      # PHP（container）→ http://localhost:8100
npm run dev               # Vite（host）→ 5178
```

| 網址 | 內容 |
|---|---|
| `/prototypes` | prototype 清單（掃檔生成，含 manifest 與自訂 SCSS 行數） |
| `/prototypes/<project>/<name>` | 單一 prototype 預覽 |
| `/components` | 元件目錄 |
| `/components/<name>` | 單一元件的 props / notes / 可跑範例 |

改 `resources/scss/**` 或 `resources/views/**` 會即時反映（Vite HMR + full reload）。
細節與設計取捨見 `preview/README.md`。

## Page prototype

```blade
@piFixture($members, 'member-list')

@extends('pi::layouts.preview')

@section('title', '會員列表')

@section('content')
    <h1 class="fz-headline-sm fz-tit">會員列表</h1>
    <x-pi::pagination :current="1" :last="3" />
@endsection
```

**交接時只動兩行**：刪掉 `@piFixture`（資料改由 controller 傳入）、`@extends` 換成專案 layout。`@section('content')` 內的 body 一個字都不用改 —— 專案與 preview 吃同一版元件套件。

## Fragment prototype

```blade
@piFragment([
    'target' => 'project-a:resources/views/members/index.blade.php',
    'slot'   => 'member-list.filters',
    'host'   => 'project-a/_hosts/members-index.html',
])

@piFixture($statuses, 'member-status')

<div class="pa-filters">
    <x-pi::form-control name="keyword" icon="icon-search" placeholder="姓名 · Email" />
    <x-pi::form-control type="select" name="status" placeholder="全部狀態" :options="$statuses" />
</div>
```

Manifest 三個欄位：

| 欄位 | 意義 |
|---|---|
| `target` | 要插入的專案檔案（`project:path` 形式，不是本機路徑） |
| `slot` | 專案 blade 裡的 marker 名稱 |
| `host` | preview 時當背景的宿主快照（相對 `prototypes/`） |

Fragment 不 `@extends` layout —— 它是片段，preview 會把它注入宿主快照。

## Fixture

```php
<?php
// prototypes/project-a/fixtures/member-status.php

return [
    ['value' => 'active',    'label' => '啟用中'],
    ['value' => 'suspended', 'label' => '已停權'],
];
```

**blade 內不得寫死資料。** fixture 的 key 結構就是給後端的資料契約，比口頭交接精確得多。

## 三個必須照做的語法規則

### 1. `@piFixture($x, 'name')` —— 不是 `include __DIR__`

```blade
@piFixture($statuses, 'member-status')                                {{-- ✅ --}}
@php($statuses = include __DIR__.'/../fixtures/member-status.php')    {{-- ❌ 必定失敗 --}}
```

Blade 會把 view 編譯成 `storage/framework/views/` 的快取檔再執行，所以編譯後的 `__DIR__` 是**快取目錄**，不是 prototype 原始檔的位置。實作見 `src/Prototype/FixtureLoader.php`。

### 2. Slot marker 必須是 **HTML 註解**

```blade
<!-- @pi-slot: member-list.filters -->      ✅
{{-- @pi-slot: member-list.filters --}}     ❌ render 後會消失
```

blade 註解 render 後就不存在，`fetch-host.php` 抓回的宿主快照裡就沒有錨點，fragment 無處可插。同一個 marker 同時服務兩件事：`apply.php`（改 blade 原始碼）與 fragment 注入（改 rendered HTML）。

### 3. 檔名含 dot 的 fragment 不能用 `@include`

`member-list.filters.blade.php` —— Blade 把 dot 當目錄分隔，`@include('…member-list.filters')` 解不到。preview 的 controller 用 `View::file()` 讀實體路徑繞過。**page 裡不要 `@include` fragment**；page 只放 slot marker，兩者是獨立的 prototype。

## 宿主快照（fragment 才需要）

```bash
php scripts/fetch-host.php <project> <name> <url>
# 例：
php scripts/fetch-host.php project-a members-index https://staging.example.com/members
```

- 存成 `prototypes/<project>/_hosts/<name>.html`
- 站根相對路徑會被改寫成絕對 URL，**CSS 刻意不下載** —— 讓快照樣式跟著專案走
- 找不到 `<!-- @pi-slot: … -->` 錨點會警告並 exit 1（代表專案端還沒埋 marker）
- 需要登入的頁面抓不到 → 改用瀏覽器另存 HTML 放進 `_hosts/`
- **快照會過期**，專案改版後重跑，不要手改那個檔

目的：PM 看到的 fragment 是坐在真實脈絡裡的樣子，能判斷跟上方標題的間距、跟下方表格的視覺重量搭不搭 —— 而不是懸在空白頁上。

若 preview 顯示「未注入宿主快照」，原因會寫在畫面最上面（缺 manifest／找不到快照／快照裡沒有 marker）。

## 交給前端

```bash
# page 與 fragment 都可以：印出可直接貼的 blade
php scripts/apply.php <project> <name>

# 只有 fragment 有 patch 模式
php scripts/apply.php <project> <name> --output=patch \
    --target=/專案本機路徑/resources/views/members/index.blade.php
```

腳本會移除 `@piFragment`、`@piFixture`，以及只在 prototype 情境下才有意義的說明註解（判準：blade 註解裡提到這兩個 directive 的），fragment 另外依 slot marker 的縮排對齊後產出 unified diff。**不直接寫入專案檔** —— 跨 repo 自動改檔風險太高，交 patch 讓前端 `git apply` 並 review。

`--output=patch` **只適用 fragment**：page 是整頁搬進專案的新檔案，沒有插入點，產 diff 沒有意義（對 page 給這個參數會直接報錯）。Page 的交接就是「刪掉 `@piFixture`、換 `@extends`」兩處。

## 元件與 class 慣例

- 元件一律 `<x-pi::*>`（清單見 `CLAUDE.md` 的 COMPONENTS 區段，自動生成）。
- **各元件 props 值域不一致，動手前讀 `resources/views/components/<name>.meta.php`。**
  例：button 8 個 tone（`dark` 只有 solid 有）、callout / alert / notification 6 個（無 `orange`）、toggle 5 個且沒有 `xl` 尺寸、checkbox / radio 4 個（無 `warning` / `purple`）、content-switcher 3 個。給錯值元件會丟例外。
- 版面用的字級 class 直接寫（`fz-title-md`、`fz-headline-sm`、`fz-tit`）；token 以 CSS var 用（`var(--cl-basic-900)`、`var(--fg-2)`、`var(--radius-md)`）。
- `border` / `radius` / `shadow` 沒有元件 —— 它們是 utility class（`gl_border-outer`、`gl_shadow-md`），套在別的元素上用。

## 已知不可用的東西

- **`gl_gs-modal` 不是設計系統的一部分** —— 是 `preview-static/modal.html` 那一頁自帶的 inline CSS（該檔註解寫明「此 namespace 無 src CSS，preview 專用」）。不要當成可用 class。
- `CLAUDE.md` 底部的「已知元件缺口」段落列的是已確認缺的東西（目前：dropdown 沒有浮出面板、modal 沒有遮罩／置中／focus trap）。遇到走缺件流程，不要自己刻。

## 原始碼位置

- 元件 blade：`resources/views/components/<name>.blade.php` + `<name>.meta.php`
- 元件 SCSS：`resources/scss/components/_<name>.scss`；token：`resources/scss/tokens/_*.scss`
- 相關文件：`preview/README.md`、`STRUCTURE.md`、`docs/ai-guide.md`（Figma 名稱 ↔ class 對照）

## 唯一的落檔位置

Prototype 一律落在 `prototypes/<project>/`。**repo 內其他任何目錄都不是 prototype 的家** —— 特別是 `preview-static/`（那是 blade 化之前的遺留物，只剩 foundation 對照頁還在用）。
