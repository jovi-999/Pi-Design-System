# Pi Design System

這個 repo 是 **token 與 component 的唯一真相來源**。出貨兩層東西：

| 層 | 內容 | 綁框架？ |
|---|---|---|
| `resources/scss/` | SCSS 原始碼（token + 元件樣式）。**沒有 build step** —— 專案用自己的 Vite / sass 去 `@use`，才吃得到專案自己的變數覆寫 | ❌ 框架中立 |
| `resources/views/` | 13 支 Blade 元件（`<x-pi::button>` …） | ✅ Laravel |

三個用途：

1. **前端切版對照** —— 本機跑 preview，看元件的 props、注意事項與可跑範例。
2. **專案透過 Composer 依賴** —— `composer require pi-tw/pi-design-system`，用版本號回答「這個專案吃哪一版」。
3. **PM 與 AI agent 討論 prototype** —— 在本 repo 的 `prototypes/` 產出 blade，定案後用 `scripts/apply.php` 交接給前後端（流程見 [docs/prototype-flow.md](docs/prototype-flow.md)）。

**不發布 npm**（Node 只用於本 repo 的 SCSS build 與 preview）。

> **不採 vendored（複製檔案進專案）。** 那條路無法回答「專案 B 現在用哪一版」，且
> 專案為趕上線就地改樣式後就再也不敢 sync。理由詳見
> [design-guideline-spec.md](design-guideline-spec.md) 的 **D1**。

---

## 對象與導覽

本 README 給**前端 / 維護者**（設計原則、本機預覽、開發與字型管理）。其他文件分工：

| 文件 | 對象 | 內容 |
|---|---|---|
| [STRUCTURE.md](STRUCTURE.md) | 人 + agent | 檔案樹狀圖、各區用途、修改方式與注意 |
| [preview/README.md](preview/README.md) | 人 | preview 的起動方式與設計取捨（**要跑 preview 先讀這支**） |
| [docs/prototype-flow.md](docs/prototype-flow.md) | 人 + agent | PM 需求 → preview → 前後端交接的完整流程圖 |
| [docs/ai-guide.md](docs/ai-guide.md) | agent | Figma 名稱 ↔ class 對照表、產頁硬規則 |
| [design-guideline-spec.md](design-guideline-spec.md) | 維護者 | 平台化的架構決策記錄（D1–D6）與被排除的方案 |
| [CLAUDE.md](CLAUDE.md) | agent | 對話自動載入的專案規則（禁自創 token、prototype 六條硬規則、**自動生成的元件清單**） |
| [SKILL.md](SKILL.md) | agent | 結構化使用規則 |
| `.claude/skills/` | agent | 專案 skill（`pm-to-preview`：PM 需求→prototype；`figma-to-pi-ds`：Figma→Pi DS） |
| [TODO.md](TODO.md) | 維護者 | 平台化各 Phase 的進度與所有偏離記錄 |
| [CHANGELOG.md](CHANGELOG.md) | 全體 | 版本變更記錄 |

> 想快速定位「要改什麼動哪裡」→ 直接看 **[STRUCTURE.md](STRUCTURE.md)**。

---

## 目錄

1. [字體排版](#字體排版)
2. [色彩](#色彩)
3. [圖示](#圖示)
4. [間距、圓角與層次](#間距圓角與層次)
5. [安裝與使用](#安裝與使用) —— 跑 preview、SCSS 入口、`@layer`、Composer 安裝、Blade 元件、prototype 流程
6. [開發本系統](#開發本系統)
7. [字型管理（Fonts）](#字型管理fonts)
8. [icon 字型（symicon）維護](#icon-字型symicon維護)
9. [授權 / 引用](#授權--引用)

---

## 字體排版

**主字體：** Noto Sans TC（400 / 500 / 600 / 700）。
**英數字體：** Google Sans Flex（variable，400–700 / stretch 48%–150%）。
**Display（僅數字用）：** `.ft-semicondensed` / `.ft-condensed` —— 只用於統計、價格、面試次數、薪資範圍。**永遠不要** 用 display 設定內文。
**圖示：** `symicon`（自家 icon font，250 個 glyph，見 [圖示](#圖示)）。

**Scale。** 詳見 `resources/scss/tokens/_typography.scss`、preview 的 `/foundation/typography`。三大家族：

- **Headline**（`fz-headline-*`，xxl→xs）：主視覺標題、區塊主標。搭配 `.fz-tit`；xl / xxl 微負字距。
- **Title**（`fz-title-*`，lg/md/sm）：卡片標題、表單標籤、行內強調。搭配 `.fz-tit`。
- **Body**（`fz-body-*`，lg/md/sm/xs）：段落、描述、metadata。預設繼承全域 500，行高寬鬆（1.7–1.8）。

舊有的 `fz-h1`/`fz-s1`/`fz-t1` 別名為了漸進遷移保留 —— 新功能請用語意化命名。

---

## 色彩

**色階結構。** 每個色相都有 50→900 的色階。500 是品牌／語意主色；400 是淺色背景上的 hover/active；600 是深色背景上的 hover。50 與 100 用於塊狀背景（badge、callout）。**永遠不要** 自創中間值（例如 550）—— 需要的話用 500 配 alpha overlay，或改用語意 alias。

---

## 圖示

**字體家族。** `symicon-fill` —— 250 個 glyph、單一線重的 filled 風格。幾何形狀對齊 24×24 畫板、2px 視覺筆畫；每個 icon 視覺量約佔 18×18。整套刻意只服務本產品族群：求職動詞為主（`interview-logo`、`interview-luckybag`、`allowance-book`、`atm`、`receipt`、`factory`），不收一般通用 dev / file 圖示。

**用法。**

```html
<!-- 偏好寫法 -->
<i class="icon icon-search" aria-hidden="true"></i>

<!-- 純 icon 按鈕一定要加 aria-label -->
<button aria-label="搜尋"><i class="icon icon-search" aria-hidden="true"></i></button>
```

**顏色。** Icon 透過 `currentColor` 繼承。**永遠不要** 在 icon 上寫死 hex —— 跟父層文字色繼承才會跟著主題切換。

完整 class 清單見 `assets/symicon.css`，視覺索引見 preview 的 `/foundation/icons`。

---

## 間距、圓角與層次

**Space scale**（`--sp-*` / `$sp-*`）：4、8、12、16、20、24、32、40、48、64。

**圓角**（`--radius-*` / `$radius-*`）：

- `--radius-xs` : 4px
- `--radius-sm` : 8px
- `--radius-md` : 12px
- `--radius-lg` : 16px
- `--radius-xl` : 24px
- `--radius-xxl` : 32px
- `--radius-pill` : 999px
- `--corner-shape` : `superellipse(1.05)` —— **不是尺寸，是圓角的形狀**

`--corner-shape` 是 2024 才進規格的新 CSS 屬性，沒有任何標準 reset 會提供。元件的
20 處 `border-radius` 全靠它，少了圓角會變成正圓。因此它放在 `_component-base.scss`
（元件契約）而不是等專案的 reset 給 —— 詳見 [1b](#1b-三個-scss-入口選哪個) 下方說明。

完整 token 清單（170 個）見 preview 的 `/foundation/tokens`，可搜尋、點擊複製。

---

## 安裝與使用

兩種身份，看不同小節：

| 你是 | 看 |
|---|---|
| 前端／設計，想對照元件與 token | [1 啟動預覽](#1-啟動預覽)、[2 切版怎麼對照](#2-切版怎麼對照) |
| 要把設計系統接進 Laravel 專案 | [3 Composer 安裝](#3-laravel-專案怎麼裝composer)、[1b SCSS 入口](#1b-三個-scss-入口選哪個)、[1c `@layer`](#1c-樣式隔離layer) |
| PM／agent，要產 prototype | [5 prototype 流程](#5-pm-與-ai-agent-討論-prototype) |

### 1. 啟動預覽

```bash
git clone <repo-url>
cd Pi-Design-System/preview

docker compose run --rm app composer install   # 第一次
npm install

docker compose up -d      # PHP（container）→ http://localhost:8100
npm run dev               # Vite（host）→ 5178
```

開 http://localhost:8100 進入三區：**Foundation**（token / icon）、**元件**、**Prototype**。

預覽吃 `resources/scss/preview-all.scss`，改 SCSS 後 HMR 即時更新，不用先 build。
起動細節與設計取捨見 [preview/README.md](preview/README.md)。

> repo 根目錄的 `npm` 只剩 SCSS 的 build / lint（`npm run build`、`npm run test`、
> `npm run lint:scss`）—— preview 已全部搬進 `preview/` 的 Laravel app。

### 1b. 三個 SCSS 入口，選哪個

| 入口 | 內容 | 給誰 |
|---|---|---|
| `resources/scss/index.scss` | tokens + 字型 + 工具 class + 元件契約 + 元件。**不含 reset** | **既有專案**（有自己 reset 的） |
| `resources/scss/reset.scss` | 頁面級預設：`box-sizing`、`html`/`body`、`h1..h6`、`p`、`code` | 從零開始的新專案，自行決定要不要載 |
| `resources/scss/preview-all.scss` | = `reset` + `index` | 本 repo 的 preview |
| `resources/scss/tokens/index.scss` | 只有 token（`:root` 變數 + Sass 變數） | 只要設計變數、自己寫元件 |

**既有專案請用 `index.scss`，不要載 `reset.scss`。** reset 裡全是頁面級意見（`h1` 該多大、`body` 什麼底色），既有專案一定已經有自己的一套（normalize.css / Tailwind preflight / Bootstrap reboot），再蓋一層只會打架，而且會影響專案**所有既有頁面**，不只是用到 DS 元件的地方。

元件不依賴 `reset.scss` 就能長對 —— 元件真正需要而標準 reset 不會提供的兩項（`corner-shape` 的超橢圓角、`font-weight: 500`）放在 `_component-base.scss`，已包含在 `index.scss` 內。

### 1c. 樣式隔離（`@layer`）

所有 DS 樣式都包在 CSS `@layer` 內，兩層：`pi-reset`（reset）在前、`pi`（token / 工具 class / 元件）在後。

**因此專案既有樣式一行都不用改** —— CSS 規定「未分層樣式的優先權高於任何分層樣式」，所以衝突時專案永遠勝出，也不必煩惱 `@use` / `<link>` 的順序。

反過來說：**專案若想覆寫 DS，直接寫未分層的 CSS 即可**，不需要提高特異度、不需要 `!important`。

```scss
// 專案的 app.scss —— 順序不影響結果
@use "pi-ds/index";     // 進 @layer pi
@use "legacy/main";     // 未分層 → 衝突時勝出
```

### 2. 切版怎麼對照

- **看元件樣式**：preview 的 `/components` → 點各元件，會看到 props 值域、注意事項，以及每個範例的「渲染結果 + 可複製的 blade 原始碼」對照。
- **查 class 名稱**：class 前綴 `gl_`，真相在 `resources/scss/components/_<元件>.scss`；Figma 名稱 ↔ class 對照見 [docs/ai-guide.md](docs/ai-guide.md)。
- **查 token / 色票 / 圖示**：preview 的 `/foundation`。`/foundation/tokens` 是全部 170 個 token 一頁列完（可搜尋、點擊複製 `var(--x)`），`/foundation/icons` 是 250 個 icon。兩者都是掃原始碼生成，不會與實際程式碼不同步。
- **禁自創 token / class**：切版只能用設計系統已存在的 token / class，不確定先 `grep resources/scss/` 或讀 `resources/scss/tokens`、`resources/scss/components` 確認。

### 3. Laravel 專案怎麼裝（Composer）

> ⚠️ **Phase 4 尚未執行** —— repo 目前還在個人帳號下、未推到 `pi-tw` org，所以下面的
> URL 還不能用。步驟本身已定案，進度見 [TODO.md](TODO.md)。

一次性設定，每個專案只做一次：

```bash
composer config repositories.pi vcs git@github.com:pi-tw/<repo>.git
composer require pi-tw/pi-design-system:^1.0
```

ServiceProvider 由 Laravel 自動發現，裝完就能用 `<x-pi::button>`。

**SCSS 那邊**：Sass 不吃 Vite alias，只吃 `loadPaths`。建議在專案放一支 shim：

```scss
// resources/sass/_pi-ds.scss
@forward '../../vendor/pi-tw/pi-design-system/resources/scss/index';
```

然後 `app.scss`：

```scss
@use "pi-ds";        // 進 @layer pi
@use "legacy/main";  // 未分層 → 衝突時勝出（見 1c）
```

**導入驗證（必做）**：接上套件但**先不掛任何新頁面**，把既有主要頁面前後截圖比對。
**理論上應該零差異** —— 有差異就是漏了裸元素選擇器或 reset 汙染，趁這時候抓出來。
之後每次升版都跑一次，成本很低但能擋掉最惡劣的那類 regression。

**升級時機由各專案自行決定。** 半年前上線、目前沒在維護的專案鎖在 `^1.8` 完全沒問題
—— 這是版本化最大的好處，不需要「所有專案都跟到最新」。

### 4. Blade 元件

13 支，呼叫寫法 `<x-pi::button tone="success" size="md">送出</x-pi::button>`。

完整清單見 [CLAUDE.md](CLAUDE.md) 的「可用元件清單」（由
`scripts/sync-component-list.php` 自動生成，永遠與實際程式碼一致），
各元件的 props 值域與注意事項見 preview 的 `/components/<name>`。

**各元件的 tone / size 清單並不一致** —— button 有 8 個 tone、callout / alert /
notification 6 個（無 `orange`）、toggle 5 個且沒有 `xl` 尺寸、checkbox / radio
4 個（無 `warning` / `purple`）、content-switcher 3 個。給錯值元件會**直接丟例外**，
不會輸出一個不存在的 class。動手前讀 `resources/views/components/<name>.meta.php`。

**已知元件缺口**（不要自己刻，走缺口流程提報）：`dropdown` 只有選單項目、沒有浮出
面板；`modal` 只有面板外觀、沒有遮罩／置中／focus trap。缺口清單也在 CLAUDE.md 內。

### 5. PM 與 AI agent 討論 prototype

在**本 repo** 的 `prototypes/<project>/` 進行，不複製 preview 環境到各專案。

```
prototypes/<project>/
├── pages/<name>.blade.php        整頁
├── fragments/<name>.blade.php    頁面中一塊（宣告 @piFragment manifest）
├── fixtures/<name>.php           假資料 = 給後端的資料契約
└── _hosts/<name>.html            專案頁面的 rendered 快照（fragment 用）
```

定案後交接：

```bash
php scripts/apply.php <project> <name>                    # 印出可貼的 blade
php scripts/apply.php <project> <name> --output=patch \   # fragment 才有
    --target=<專案路徑>/resources/views/<...>.blade.php
```

前端只需改兩處（刪 `@piFixture`、換 `@extends`），**body 一個字都不用改** —— 因為
專案與 preview 吃同一版元件套件。後端看 fixture 就知道要提供什麼結構。

完整流程（含缺件提報的中斷規則）見 [docs/prototype-flow.md](docs/prototype-flow.md)；
產 prototype 的規範見 `.claude/skills/pm-to-preview/`。

---

## 開發本系統

Repo 結構與「要改什麼動哪裡」見 **[STRUCTURE.md](STRUCTURE.md)**（單一真相，此處不重複）。

### 日常開發

改 SCSS（顏色、間距、單一元件樣式）：

```bash
# 1. 開預覽頁即時看視覺（HMR，不用 build）
cd preview
docker compose up -d      # PHP  → http://localhost:8100
npm run dev               # Vite → 5178

# 2. 改完跑一次 build + smoke test，確認沒語法錯
npm run build
npm test                  # 檢查產出 css 含關鍵 token / class

# 3. 寫 CHANGELOG → commit → push
```

### CI 的四個 gate

`.github/workflows/ci.yml` —— 任一失敗就擋 PR。四個都能在本機跑：

```bash
npm test                              # ① SCSS build + 14 項 smoke（含 @layer 分層防護）
php scripts/sync-component-list.php --check   # ② CLAUDE.md 的元件清單是否與 *.meta.php 同步
php scripts/check-prototypes.php      # ③ prototype 樣式門檻 + fragment manifest
composer validate --no-check-publish  # ④ 套件定義合法
```

**② 存在的理由**：手寫的元件列表三個月後一定跟實際元件對不上，然後 agent 開始產出不存在的元件。清單自動生成，CI 確認沒人忘記跑。

**③ 存在的理由**：`CLAUDE.md` 第 3 條的 30 行樣式門檻是整套流程的品質閥門 —— 自訂樣式寫太多代表元件庫缺東西。門檻計入 `<style>` 行數**與 inline `style=""` 的宣告數**（只數前者的話，把樣式搬進 attribute 就能繞過）。preview 清單頁顯示的數字與 CI 用同一份實作（`src/Prototype/StyleBudget.php`），不會一邊算 25 一邊算 32。

**超標時要提報元件缺口，不是把門檻調高。**

CI 刻意不跑 `preview/` 的 Laravel app（它是開發工具、整個 `export-ignore`），也還沒跑 `lint:scss`（stylelint 目前不在 `devDependencies`，靠本機全域安裝）。

### 編輯規則

- 只改 `resources/scss/` 內檔案；CSS 一律由 `npm run build` 從 SCSS 產出，不手寫 CSS。
- 加新元件 → `resources/scss/components/_xxx.scss` + `resources/scss/components/index.scss` 加一行 `@forward` + `resources/views/components/xxx.blade.php` 與 `xxx.meta.php`。**preview 不用改** —— 目錄頁是掃 `*.meta.php` 生成的。
- 改 token → 先看 preview 的 `/foundation/tokens` 評估衝擊面，CHANGELOG 寫清楚。
- **禁自創 token / class**：不確定先 `grep resources/scss/` 或讀 `resources/scss/tokens`、`resources/scss/components` 確認，絕不憑記憶發明。
- rename / 移除 token、改 class 名屬 breaking change → CHANGELOG 寫清楚並同步所有引用處（preview、docs、README）。

---

## 字型管理（Fonts）

字型是 design system 裡最容易出問題的部分 —— **檔案在哪、SCSS 怎麼引用、路徑怎麼解析** 一旦不一致就會 404 / FOUT / build 失敗。這節是唯一的真相來源，所有字型相關的決策都從這裡開始。

### 架構

```
fonts/                     ← 字型原始檔（woff / woff2）
resources/scss/base/_fonts.scss       ← @font-face 宣告，路徑由 $font-path 控制
resources/scss/tokens/_typography.scss ← --font-sans / --font-display CSS variable
```

`_fonts.scss` 用 `$font-path !default` 留了**覆寫鉤子**：

```scss
$font-path: "/fonts" !default;

@font-face {
  font-family: "symicon";
  src: url("#{$font-path}/symicon-6.4s.woff2") format("woff2"),
       url("#{$font-path}/symicon-6.4s.woff") format("woff");
}
```

預設 `"/fonts"`（web root 起算的絕對路徑）。preview 與專案端（字型放 `public/fonts/`）都吃這個預設值即可，不用改；只有字型放 CDN 時才需覆寫：

```scss
@use "pi-design-system/base/fonts" with (
  $font-path: "https://cdn.example.com/fonts"
);
```

> 為什麼用絕對路徑：Sass 不會 rebase `url()`，會原樣輸出；Vite 是相對於「entry CSS 檔的目錄」去解，不是相對於 `_fonts.scss` 這支 partial。因此任何相對路徑都只能對其中一個消費端正確（`sass` CLI 輸出到 `dist/`、preview 的 Vite entry 在 `preview/resources/scss/`、專案端 entry 在 `resources/sass/`，三者位置各不相同）。絕對路徑對三者都成立。
>
> preview 的 `/fonts` 由 `preview/public/fonts` → `../../fonts` 的 symlink 服務。

### 換 / 升級字型怎麼做

| 情況 | 動哪裡 |
|---|---|
| 字型版本升級（family / 檔名不變，只換內容） | 把新檔覆蓋進 `fonts/`，起 preview 確認還能載 |
| 加新字型檔（例如升級 symicon 版本） | `fonts/` 加檔 → 更新對應 `@font-face` 的檔名與版本號 |
| 替換字型（改 family / token） | `fonts/` 換檔 → 改 `resources/scss/base/_fonts.scss` 的 `@font-face` → 改 `resources/scss/tokens/_typography.scss` 的 `--font-sans` → 開 preview 的 `/foundation/typography` 與 `/components` 對照視覺有無走鐘（中文寬度可能不同）|

改完一律起 preview 確認字型載入（DevTools Network 看字型 200），並寫 CHANGELOG。

### 給 AI agent 的提醒

- ❌ **不要** 在 component 檔裡寫 `@import url('https://fonts.googleapis.com/...')`
- ❌ **不要** 在 component 檔裡再加 `@font-face`
- ✅ 字型只在 `resources/scss/base/_fonts.scss` 一個地方宣告
- ✅ 引用字型一律用 `$font-sans` / `$font-display` / `$font-icon` 變數，不要寫死 `font-family: "symicon"`

---

## icon 字型（symicon）維護

icon 字型跟文字字型機制不同：它**不走 `resources/scss/base/_fonts.scss`**，而是獨立在 `assets/symicon.css` —— 一個檔同時管 `@font-face` 與每個 `.icon-*` 的 codepoint（`content: "\eXXX"`）。版本號直接寫進**檔名**（`symicon-6.4s.woff2`）當作 cache-bust，升級時連檔名一起換。

```
fonts/symicon-6.4s.woff2 / .woff   ← icon 字型本體（檔名帶版本號）
assets/symicon.css                 ← @font-face（url 指向 ../fonts/）+ .icon-* codepoint
assets/icon-cp-map.json            ← icon 名稱 ↔ codepoint 對應
assets/icon-names.json             ← icon 名稱清單
```

> ⚠️ **codepoint 對應**：icon 字型工具（IcoMoon / Fontello）匯出時附 `selection.json`（每個 icon 的 codepoint）。**glyph 順序一變，所有 `content: "\eXXX"` 都要重新對應** —— 升級前務必拿到對照表確認。

### 情境 1：在本 repo 升級 icon 字型

`assets/` 與 `fonts/` 由 preview 的 `public/` symlink 服務（`preview/public/assets` →
`../../assets`、`preview/public/fonts` → `../../fonts`），**加檔不用改任何設定**。

```bash
# 1. 放新字型檔進 fonts/，檔名帶新版本號（如 6.4s → 7.0s），舊檔先留著
cp ~/Downloads/symicon-7.0s.woff2 fonts/
cp ~/Downloads/symicon-7.0s.woff  fonts/

# 2. 改 assets/symicon.css：
#    - 檔頭註解版本號 symicon-6.4s → symicon-7.0s
#    - @font-face 的 src url：../fonts/symicon-7.0s.woff2 / .woff
#    - 若 glyph 順序變了 → 依新 selection.json 重新對應所有 .icon-* 的 content
#      （連帶更新 assets/icon-cp-map.json 與 icon-names.json ——
#       preview 的 /foundation/icons 掃這兩支，改完那頁自己就對）

# 3. 啟動預覽驗證
cd preview
docker compose up -d      # PHP  → http://localhost:8100
npm run dev               # Vite → 5178
#    開 icons 對照頁 / 各元件頁，確認 icon 正常顯示
#    重點檢查有用到 icon 的元件：form 的 valid/invalid、loading、alert、callout

# 4. 全部 OK → 刪舊 symicon-6.4s.*，寫 CHANGELOG
```

### 情境 2：專案端使用 icon

**字型檔與 class 表都隨套件出貨** —— `fonts/` 與 `assets/symicon.css` 刻意**沒有**
`export-ignore`（元件吃 `icon-*` class，漏了 icon 全空）。專案端不需要複製任何檔案。

**a. 讓 `/fonts` 解得到**。`symicon.css` 的 `@font-face` 走 `../fonts/`（相對 `assets/`），
但元件的 `_fonts.scss` 用的是絕對路徑 `/fonts`。專案端把 vendor 內的字型接到 web root：

```bash
# Laravel：在 public/ 建 symlink（或用 build 步驟 copy）
ln -s ../vendor/pi-tw/pi-design-system/fonts public/fonts
```

或覆寫 `$font-path` 指到專案已有的字型位置：

```scss
@use "pi-design-system/base/fonts" with ($font-path: "/assets/fonts");
```

**b. 載 icon class 表**。`assets/symicon.css` 是編譯好的 CSS（含 `@layer pi`），
直接在 layout 引入 vendor 內的那支，或在 build 步驟 copy 進 `public/`：

```html
<link rel="stylesheet" href="/vendor/pi-ds/symicon.css">
```

**c.（建議）preload 首屏會用到的 icon 字型**，降 FOUT：

```html
<link rel="preload" href="/fonts/symicon-6.4s.woff2" as="font" type="font/woff2" crossorigin>
```

**d. 升級**：`composer update pi-tw/pi-design-system`。字型檔名帶版本號（cache-bust），
所以升級後要同步改 preload 的 `href`。glyph 順序變動屬 breaking change，會寫在 CHANGELOG。

> 共通鐵則：**版本號寫進檔名**（cache-bust）、**舊檔驗證通過才刪**、**glyph 順序變動務必重對 codepoint**。

---

## 授權 / 引用

- **Noto Sans TC** —— SIL Open Font License 1.1，© Google。
- **Google Sans Flex** —— SIL Open Font License 1.1，© Google。
- **symicon-fill** —— 內部資產，© Pi。
