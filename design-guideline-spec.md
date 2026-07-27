# Design Guideline 平台化實作規格

> 本文件為交付給 AI Agent 執行的實作規格。
> 目標讀者：負責實作的 AI Agent 與前端工程師。

---

## 0. 背景與目標

### 現況

- 公司有一份 Design Guideline，目前為 **Vite + HTML + SCSS** 的獨立專案
- 用途：PM 與 AI Agent 共同討論專案畫面 prototype 與前端動態效果
- Prototype 需以 guideline 提供的元件繪製
- 討論定案後產出 **Blade + SCSS**，交接給前後端接續開發

### 要解決的核心問題

1. Guideline 如何散佈到各專案，且不會 drift（各專案樣式互相分歧）
2. Prototype 產出如何「零轉換」進入專案
3. Prototype 不一定是整頁，常常只是頁面中某個區塊（fragment）
4. Guideline 本身如何長期維護不腐化

### 最終目標

- **單一真實來源**：元件只有一份定義，各專案透過版本化依賴取得
- **零轉換交接**：prototype 產出的 blade 直接貼進專案即可運作
- **可回答版本**：任何時候都能知道「專案 X 用的是 guideline 哪一版」
- **缺口可見**：元件庫做不到的需求會浮上檯面，而非被手刻掩蓋

---

## 1. 架構決策記錄（重要，請勿自行變更）

以下決策已經過評估，實作時請遵循。若發現窒礙難行，請先提出討論而非自行改變方向。

### D1. 散佈方式 = Composer 版本化依賴，不是複製

**決策**：Guideline 做成獨立 repo，透過 Composer VCS repository 散佈到各專案。

**理由**：若採用「複製一份 guideline 進專案，有異動時回頭更新」，會必然發生：
- 專案為趕上線就地修改樣式，之後 sync 時 diff 一團亂不敢覆蓋
- 無法回答「專案 B 現在用哪個版本」——沒有版本號，只有一坨被改過的檔案
- 數個月後變成多份互相衝突的 fork

版本化依賴讓「guideline 更新」變成 tag 版本 + `composer update`，且各專案可以刻意落後（已上線不再維護的專案鎖在舊版完全合理）。

### D2. 討論場所 = Guideline repo，不是各專案

**決策**：所有 prototype 討論在 guideline repo 進行，不複製 preview 環境到各專案。

**理由**：Preview harness 與 agent 規範複製到每個專案是不可能維護的。

**注意**：Fragment 需要專案脈絡時，做法是**把專案頁面的 rendered HTML 抓回 guideline repo 當背景快照**（往內拉），而不是把討論推回專案（往外送）。

### D3. 產出格式 = Blade，不是 HTML

**決策**：Prototype 的保存產物是 blade 檔案；HTML 只是 preview 顯示用的畫面。

**理由**：若產物是裸 HTML，回專案時要反向對應回 `<x-dg::*>`，會有兩個問題：
- AI 反推元件結構會猜錯，且錯的時候不易察覺
- 元件的內部 DOM 結構被寫死進專案。日後 guideline 改元件結構，用 `<x-dg::card>` 的專案 `composer update` 就跟上了，但轉出去的裸 HTML 永遠停在舊版

這等於放棄元件化最主要的好處。

### D4. Guideline repo 需要 Laravel（僅 preview 用）

**決策**：Repo 根目錄是 Composer 套件；`preview/` 子目錄放一個 Laravel app 作為開發工具。

**理由**：要 render blade 就需要 PHP。但套件本體只依賴 `illuminate/support`，不是整包 framework。

**注意**：套件出貨的是 **SCSS 原始碼**，不是編譯後 CSS。專案端用自己的 Vite 去 `@use`，才能吃到專案自己的變數覆寫。所以套件本身沒有 build step。

### D5. 樣式隔離 = `dg-` 前綴 + `@layer dg`

**決策**：所有 class 加 `dg-` 前綴、CSS 變數加 `--dg-`、元件樣式全部包在 `@layer dg` 內。

**理由**：專案原本就有既有樣式，會撞在三個地方：
1. class 撞名（`.btn` vs `.btn`）——前綴解決
2. 裸元素選擇器汙染（`h1 { margin: 0 }` 影響整個專案既有頁面）——禁止輸出裸選擇器解決
3. specificity 戰爭（專案的 `.card .btn` 影響到 `.dg-button`）——`@layer` 解決

`@layer` 的關鍵性質：**未分層的樣式優先權高於任何分層的樣式**。所以 guideline 進 layer、專案既有樣式不動，衝突時專案永遠勝出，既有 code 一行都不用改，也不用煩惱 import 順序。

### D6. 暫不做獨立平台

**決策**：先在 repo 內把 fragment 模型做完整，觀察後再決定是否包平台 UI。

**理由**：
- Blade 是 server-side，要做到 Claude Design 那種即時預覽，需要維護 render service、併發、逾時、沙箱隔離——是基礎設施難度，不是 UI 工程難度
- 加上登入、權限、版本管理、產出物回 git，等於多養一個內部產品
- 本規格定義的 fragment manifest（target / slot / host）**就是未來平台的資料模型**。先讓它被真實需求打磨幾個月，之後要包 UI 是加一層，不是重做

**值得做平台的訊號**（可觀察，不用現在猜）：PM 反覆卡在環境設定、同時有 5 個以上專案在跑 prototype、非技術角色也要參與討論。

---

## 2. Repo 結構

```
design-guideline/
├── composer.json                       # company/design-guideline（套件本體）
├── src/
│   └── DesignGuidelineServiceProvider.php
│
├── resources/                          # ── 套件本體，會被裝進專案 ──
│   ├── views/components/
│   │   ├── button.blade.php
│   │   ├── button.meta.php
│   │   ├── card.blade.php
│   │   ├── card.meta.php
│   │   └── ...
│   └── scss/
│       ├── _tokens.scss                # 只有變數與 mixin，@use 不產生 CSS
│       ├── index.scss                  # 元件樣式，@layer dg + dg- 前綴
│       └── reset.scss                  # 獨立檔案，既有專案不載
│
├── prototypes/                         # ── 討論區，不進套件 ──
│   └── project-a/
│       ├── pages/
│       │   └── member-list.blade.php
│       ├── fragments/
│       │   └── member-list.filters.blade.php
│       ├── fixtures/
│       │   ├── member-list.php
│       │   └── member-status.php
│       └── _hosts/
│           └── members-index.html      # 專案頁面 rendered 快照
│
├── preview/                            # ── Laravel app，開發工具 ──
│   ├── composer.json
│   ├── package.json
│   ├── vite.config.js
│   ├── routes/web.php
│   └── app/Http/Controllers/
│       ├── PrototypeIndexController.php
│       ├── PreviewController.php
│       └── RenderController.php
│
├── scripts/
│   ├── sync-component-list.php         # meta → CLAUDE.md
│   ├── fetch-host.php                  # 抓專案頁面快照
│   └── apply.php                       # 產生要套進專案的 patch
│
├── CLAUDE.md                           # Agent 規範
└── CHANGELOG.md
```

---

## 3. Phase 1：Repo 重構

### 3.1 套件 composer.json

```json
{
    "name": "company/design-guideline",
    "type": "library",
    "require": {
        "php": "^8.2",
        "illuminate/support": "^11.0|^12.0"
    },
    "autoload": {
        "psr-4": { "Company\\DesignGuideline\\": "src/" }
    },
    "extra": {
        "laravel": {
            "providers": [
                "Company\\DesignGuideline\\DesignGuidelineServiceProvider"
            ]
        }
    },
    "archive": {
        "exclude": ["/prototypes", "/preview", "/scripts", "/tests"]
    }
}
```

**重點**：
- 根目錄必須是套件本身，否則專案端 `composer require` 這個 VCS repo 時抓不到正確的 composer.json
- 依賴只寫 `illuminate/support`，不要整包 `laravel/framework`
- `archive.exclude` 確保討論區不會被裝進專案

### 3.2 ServiceProvider

```php
<?php

namespace Company\DesignGuideline;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class DesignGuidelineServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'dg');

        Blade::anonymousComponentPath(
            __DIR__ . '/../resources/views/components',
            'dg'
        );
    }
}
```

使用 anonymous component path 的好處：**一個檔案就是一個元件，不需要寫 PHP class、不需要註冊清單**。`<x-dg::button>` 自動對應到 `components/button.blade.php`。

### 3.3 preview/ Laravel app

建立標準 Laravel app，`preview/composer.json` 用 path repository symlink 上層：

```json
{
    "repositories": [
        {
            "type": "path",
            "path": "../",
            "options": { "symlink": true }
        }
    ],
    "require": {
        "laravel/framework": "^12.0",
        "company/design-guideline": "*"
    }
}
```

**Symlink 的好處**：改根目錄的元件，preview 立刻反映，不需要 `composer update`。

`preview/vite.config.js`：

```js
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import path from 'path';

export default defineConfig({
    plugins: [laravel({ input: ['resources/scss/preview.scss'], refresh: true })],
    resolve: {
        alias: {
            'design-guideline': path.resolve(__dirname, '../resources/scss'),
        },
    },
});
```

### 3.4 SCSS 分層規則

**`resources/scss/_tokens.scss`** — 只放變數與 mixin，`@use` 進來不產生任何 CSS。這個切分很重要：既有專案可以安心 `@use` tokens 拿設計變數用在自己的樣式上，完全不會多出 CSS。

**`resources/scss/index.scss`** — 元件樣式。因為 SCSS 的 `@use` 必須寫在檔案最前面、不能包在 `@layer` 裡，所以**分層由套件自己在各元件檔內宣告**：

```scss
// resources/scss/components/_button.scss
@use "../tokens" as *;

@layer dg {
    .dg-button {
        box-sizing: border-box;      // 明講，不假設全域有設
        font-size: 16px;
        line-height: 1.5;
        border: 0;
        cursor: pointer;

        &--primary { background: var(--dg-color-primary); }
        &--md      { padding: 12px 24px; }
    }
}
```

**`resources/scss/reset.scss`** — 獨立檔案，既有專案不要載。

### 3.5 SCSS 硬性規則

1. **禁止裸元素選擇器**：不得出現 `h1`、`a`、`button`、`*` 等未加 class 的選擇器（reset.scss 除外）
2. **所有 class 加 `dg-` 前綴，CSS 變數加 `--dg-`**
3. **元件必須自給自足**：對自己用到的屬性明確賦值，不繼承環境假設

第 3 點的理由：preview 環境與專案環境的 base style 一定不一樣。如果元件依賴 reset 才長得對（假設 `box-sizing: border-box` 是全域、假設 `h3` 的 margin 已被清掉），prototype 在 guideline repo 看起來完美，貼進專案就走鐘——而這正是整個流程最想避免的失敗。

```scss
@layer dg {
    .dg-card {
        box-sizing: border-box;
        font-size: 16px;
        line-height: 1.5;

        &__title { margin: 0 0 12px; }   // 不假設 reset 清過 margin
    }
}
```

---

## 4. Phase 2：元件轉換（HTML → Blade）

### 4.1 轉換模式

現有 HTML snippet：

```html
<button class="dg-button dg-button--primary dg-button--md">送出</button>
```

轉成 anonymous component：

```blade
{{-- resources/views/components/button.blade.php --}}
@props(['variant' => 'primary', 'size' => 'md'])

<button {{ $attributes->class([
    'dg-button',
    "dg-button--{$variant}",
    "dg-button--{$size}",
]) }}>
    {{ $slot }}
</button>
```

**不用動的部分**：所有 SCSS（`_tokens.scss` 與元件樣式）一行都不用改。真正要轉的只有 markup 層。

### 4.2 每個元件配一份 metadata

```php
<?php
// resources/views/components/button.meta.php

return [
    'name' => 'Button',
    'description' => '主要操作按鈕',
    'props' => [
        'variant' => ['primary', 'secondary', 'ghost'],
        'size'    => ['sm', 'md', 'lg'],
    ],
    'examples' => [
        ['label' => '主要按鈕', 'code' => '<x-dg::button>送出</x-dg::button>'],
        ['label' => '次要按鈕', 'code' => '<x-dg::button variant="secondary">取消</x-dg::button>'],
    ],
];
```

**這份 metadata 有兩個消費者**：
1. Preview page 掃資料夾自動 render 所有變體 → 新增元件不用手改 preview
2. `sync-component-list.php` 生成 CLAUDE.md 的元件清單 → 規範永遠跟實際程式碼一致

如果不做這件事，新增一個元件要同時改元件檔 + preview 頁面 + 規範文件，三個地方遲早不同步。

### 4.3 轉換順序

1. 先轉 3–5 個常用元件（button / input / card），確認 preview 跑得起來、`@layer` 行為正確
2. 剩下的批次轉——模式高度重複，適合交給 Agent
3. 舊的靜態 style guide 頁面拆成 `.meta.php` 的 examples，preview 頁改成自動生成

---

## 5. Phase 3：Preview 與 Prototype 系統

### 5.1 路由

```php
// preview/routes/web.php
Route::get('/preview', PrototypeIndexController::class);            // 所有 prototype 列表
Route::get('/preview/{project}/{name}', PreviewController::class);  // 單一預覽
Route::post('/preview/render', RenderController::class);            // AI 迭代用
```

`PreviewController` 需判斷 page 或 fragment：
- **Page**：直接 render
- **Fragment**：載入 `_hosts/` 快照 HTML，把 render 完的片段注入 slot marker 位置

### 5.2 Page prototype 格式

```blade
{{-- prototypes/project-a/pages/member-list.blade.php --}}
@php($members = include __DIR__.'/../fixtures/member-list.php')
@extends('dg::layouts.preview')

@section('content')
    <x-dg::page-header title="會員列表" />

    <x-dg::table>
        @foreach($members as $member)
            <x-dg::table.row>{{ $member['name'] }}</x-dg::table.row>
        @endforeach
    </x-dg::table>
@endsection
```

**交接時只動兩行**：刪掉 `@php(...)`（改由 controller 傳資料）、把 `@extends` 換成專案 layout。**body 一個字都不用改。**

### 5.3 Fragment prototype 格式

```blade
{{-- prototypes/project-a/fragments/member-list.filters.blade.php --}}
@dgFragment([
    'target' => 'project-a:members/index.blade.php',
    'slot'   => 'member-list.filters',
    'host'   => 'project-a/_hosts/members-index.html',
])

@php($statuses = include __DIR__.'/../fixtures/member-status.php')

<x-dg::filter-bar>
    <x-dg::select name="status" :options="$statuses" />
    <x-dg::date-range name="joined_at" />
</x-dg::filter-bar>
```

Manifest 三個欄位的意義：
- `target`：要插入的專案檔案
- `slot`：對應專案 blade 裡的 marker 名稱
- `host`：preview 時用哪份宿主快照當背景

### 5.4 Fixture 規則

**Blade 內不得寫死資料，一律走 fixtures/。**

```php
<?php
// prototypes/project-a/fixtures/member-status.php

return [
    ['value' => 'active',    'label' => '啟用中'],
    ['value' => 'suspended', 'label' => '已停權'],
];
```

Fixture 的 key 結構**就是給後端的資料契約**，比口頭交接精確得多。

### 5.5 宿主快照

```bash
php scripts/fetch-host.php project-a members/index
```

腳本行為：去 staging 抓目標頁的 rendered HTML，存成靜態檔到 `_hosts/`。CSS 直接引用專案的 compiled CSS URL（不要下載，讓它跟著專案走）。

**目的**：PM 看到的 fragment 是坐在真實脈絡裡的樣子，能判斷跟上方 page-header 的間距、跟下方 table 的視覺重量搭不搭——而不是懸在空白頁上。

### 5.6 CLAUDE.md 規範

```markdown
# Design Guideline — Agent 規範

## 硬性規則（手寫，勿刪）

1. 只能使用 `<x-dg::*>` 元件，不准手刻 HTML 或裸 class
2. 資料一律放 `fixtures/`，blade 內不得寫死
3. page-scoped SCSS 上限 30 行。超過即視為元件缺口 —— **停下來提報，不要自己刻**
4. 不得輸出裸元素選擇器（h1 / a / button / *）
5. Fragment 必須宣告 `@dgFragment` manifest（target / slot / host）
6. 元件樣式一律包在 `@layer dg` 內，class 加 `dg-` 前綴

## 可用元件清單

<!-- COMPONENTS:START -->
（由 scripts/sync-component-list.php 自動生成，請勿手動修改）
<!-- COMPONENTS:END -->
```

**第 3 條是整套流程的品質閥門。** PM 不會知道什麼做得到什麼做不到；如果 Agent 遇到缺口就自己刻一個出來，元件庫的缺口就被藏起來了，drift 從這裡開始。

`sync-component-list.php` 掛進 CI，PR 時自動更新 `COMPONENTS` 區段。**原則手寫、清單生成**——手寫的元件列表三個月後一定跟實際元件對不上，然後 Agent 開始產出不存在的元件。

---

## 6. Phase 4：專案端導入

### 6.1 一次性設定（每個專案只做一次）

```bash
composer config repositories.dg vcs git@github.com:company/design-guideline.git
composer require company/design-guideline:^1.0
```

`app.scss` **順序很重要**：

```scss
@use "design-guideline";   // 進 @layer dg
@use "legacy/main";        // 未分層 → 衝突時永遠勝出
```

`vite.config.js` 加 alias 指到 `vendor/company/design-guideline/resources/scss`。

專案 `CLAUDE.md` 加一行指向 `vendor/company/design-guideline/`，Agent 就讀得到元件定義，不需要複製一份。

### 6.2 導入驗證（必做）

接上套件但**先不掛任何新頁面**，把既有主要頁面前後截圖比對。

**理論上應該零差異。** 有差異就是套件漏了裸元素選擇器或 reset 汙染，趁這時候抓出來。

之後每次 guideline 升版都跑一次，成本很低但能擋掉最惡劣的那類 regression。

### 6.3 埋 slot marker

在要新增區塊的既有 blade 放一行註解：

```blade
<x-dg::page-header title="會員列表" />

{{-- @dg-slot: member-list.filters --}}

<x-dg::table>...</x-dg::table>
```

這行同時是自動插入的錨點，與「此位置有 prototype 在跑」的標記。

### 6.4 CI 注意事項

Composer 私有套件在 CI 環境要拉取，記得處理 deploy key 或 token。**這是最常卡住的一步**，建議在 Phase 4 一開始就先確認。

---

## 7. 工作流程

### 7.1 PM & AI Agent 討論流程

```
① PM 提需求
   └→ 建立 manifest：這是 page 還是 fragment？插哪個 slot？

② 抓宿主快照（fragment 才需要）
   └→ php scripts/fetch-host.php project-a members/index

③ Agent 讀 CLAUDE.md → 取得元件清單與規範

④ Agent 產出 blade + fixture
   └→ 只用 <x-dg::*>，資料進 fixtures/

⑤ Preview render → PM 看到真實脈絡裡的畫面

⑥ 迭代 ④⑤ 直到 PM 滿意

   ⚠ 若 Agent 判定現有元件做不出來：
       停止產出 → 提報元件缺口 → 走「新增元件」分支
       不要手刻，不要硬湊

⑦ 定案 → commit 進 prototypes/project-a/
```

**第 ⑥ 步的中斷規則是整個流程的核心。** 讓缺口浮出檯面，變成一張新增元件的工單。

### 7.2 Handoff 產出物

| 檔案 | 給誰 | 用途 |
|---|---|---|
| `fragments/*.blade.php` 或 `pages/*.blade.php` | 前端 | 直接插入的內容 |
| `fixtures/*.php` | **後端** | 資料契約 |
| manifest（target / slot） | 前端 | 插入位置 |
| page-scoped SCSS（若有） | 前端 | 通常接近零 |
| Preview URL | 全部 | 驗收基準 |

### 7.3 前端接手

```bash
php scripts/apply.php project-a member-list.filters --output=patch
```

腳本行為：
1. 在 target 檔案中找到 `@dg-slot` marker
2. 插入 fragment 內容
3. 移除 `@php(...)` fixture 引入那行

前端只需把資料改由 controller 傳入。**body 一個字都不用改**——因為專案與 preview 環境依賴同一個版本的元件套件。

### 7.4 後端接手

看 fixture 就知道要提供什麼結構。Controller 傳出同樣結構的資料即可。

### 7.5 驗收

拿 preview URL 跟專案實際頁面對照。因為兩邊用同一份元件、同一個版本，理論上只有真實資料的差異。

---

## 8. 維護流程

### 8.1 Guideline 改動

```
改元件 → 更新 .meta.php
       → visual regression 跑 preview page（擋掉 regression）
       → CI 自動同步 CLAUDE.md 元件清單
       → 寫 CHANGELOG
       → tag 版本
```

Visual regression snapshot 跑在 preview page 上：guideline 改動如果弄壞既有元件，PR 階段就會被擋下來，不用等專案端發現。

### 8.2 Semver 判斷標準

| 層級 | 情境 |
|---|---|
| **patch** | 純樣式微調，不影響版面尺寸 |
| **minor** | 新增元件、新增 optional prop |
| **major** | 移除或改名 prop、改變元件 DOM 結構、移除元件 |

**Major 這條要守住紀律**，不然版本號就失去意義了。

### 8.3 專案端更新

```bash
composer update company/design-guideline
```

**時機由各專案自行決定。** 半年前上線、目前沒在維護的專案鎖在 `^1.8` 完全沒問題——這是版本化最大的好處，不需要「所有專案都跟到最新」。

### 8.4 Prototype 生命週期

上線後從 `prototypes/` 移除或搬到 `_archive/`。不清的話一年後沒人分得清哪些還有效。

既有 blade 裡的 `@dg-slot` marker 可以留著，下次同一位置要改時直接重用。

### 8.5 元件缺口回流

流程第 ⑥ 步提報的缺口，走正常元件開發流程進 guideline，發 minor 版。

**這是元件庫持續完整的唯一機制。** 如果缺口都被 Agent 手刻掉了，元件庫會停止成長，各專案開始長出自己的樣式——回到最初要解決的問題。

### 8.6 健康指標

**Prototype 產出的 page-scoped SCSS 應該接近零。**

如果 Agent 一直在寫自訂樣式，那不是 Agent 的問題，是元件庫缺東西的訊號。訂門檻（單頁超過 30 行自訂 SCSS 就要 review），這會逼著缺口回流成新元件，而不是散在各專案裡。

這條同時解決「產出好維護」與「guideline 持續完整」兩件事。

---

## 9. 導入順序

| Phase | 內容 | 完成標準 |
|---|---|---|
| 1 | Repo 重構、ServiceProvider、`@layer` + 前綴、preview app | preview 跑得起來 |
| 2 | 轉 3–5 個常用元件 → 批次轉其餘 → meta 自動生成 preview | 所有元件可在 preview 檢視 |
| 3 | Prototype 系統：page → fragment → slot marker → apply script | 完整跑過一次 fragment 流程 |
| 4 | 第一個專案 `composer require`，跑完整流程 | 零差異截圖驗證通過 |
| 5 | 既有專案逐個遷移，每個都先做零差異驗證 | — |
| 6 | 觀察是否需要平台 UI | 見 D6 的觀察訊號 |

**建議**：Phase 4 挑一個**新專案**當白老鼠跑完整流程，順了再回頭遷移既有專案，一次一個。

---

## 10. 附錄：被評估後排除的方案

以下方案已評估過並排除，實作時請勿改採。

### A. 複製 guideline 進各專案

排除理由見 D1。核心問題：「有異動時回頭更新」靠人的紀律，現實中必然失敗，且無法回答版本問題。

### B. 平台產出 HTML + SCSS，回專案再轉 Blade

排除理由見 D3。核心問題：反向對應會猜錯，且把元件內部結構寫死進專案，放棄元件化的主要好處。另外，平台若輸出元件 SCSS，會把 drift 問題原地重建——平台應只輸出 page-scoped 的少量 SCSS。

### C. Custom element 中介格式（`<dg-card>` → `<x-dg::card>`）

**保留為備案，目前不採用。**

可行性：這是合法 HTML，靠一層薄的 custom element 定義就能 client-side render，回專案的轉換是純字串替換，沒有猜測空間。

不採用的理由：元件要維護兩套 template（web component 一套、blade 一套），兩邊行為不一致的 bug 很難查。只有在確定要讓非 Laravel 的人參與、或平台要完全脫離 PHP 時才划算。

### D. Git subtree（Composer 的降級備案）

若 Composer 私有套件的 CI 認證一時處理不了，可先用 git subtree 把 guideline 拉進專案的 `packages/design-guideline`，鎖 commit hash。

維護性比 composer 差一截，但「哪個版本」仍可回答、更新仍是單一指令，跟直接複製完全不同層級。

---

## 11. 補充：這個架構沒有把你鎖進 Laravel

轉成 blade 只綁死了 **markup 層**。SCSS 那層仍然是框架中立的。

哪天有 Vue 專案要用同一套設計，`_tokens.scss` 與元件樣式可以直接吃，只需要另外寫一套 Vue template。而 SCSS 通常才是這套東西裡累積最久、最有價值的部分。
