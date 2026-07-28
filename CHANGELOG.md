# Changelog

本文件記錄 Pi Design System 的每一次發版。格式遵循 [Keep a Changelog](https://keepachangelog.com/)，版本號遵循 [SemVer](https://semver.org/)。

> **版本語意**
> - **major** — breaking：token 改名、component class 改名、刪除元件
> - **minor** — 新增：新 component、新 token、新 variant（不破壞既有 API）
> - **patch** — 修正：bug fix、視覺微調、文件更新

---

## [Unreleased]

### ⚠ Breaking — 平台化 Phase 1（`design-guideline-spec.md`）

repo 從「vendored SCSS 對照用」轉為 **Composer 套件**。下游已 vendored 的專案需配合調整。

- **`src/` → `resources/scss/`**（目錄搬遷）
  `src/` 依 Composer / PSR-4 慣例保留給 PHP class。下游 vendor 腳本與
  `@use` 路徑需同步（`.scratch/pm-to-blade-skill/vendor-copy.sh` 已更新）。

- **`reset` 移出 `index.scss`，成為獨立檔 `resources/scss/reset.scss`**
  `@use ".../index"` 不再帶入 `html` / `body` / `h1..h6` / `p` / `code` 的樣式。
  既有專案本來就不該吃這些（會影響所有既有頁面），故此為預期行為；
  需要 reset 的新專案請另外 `@use ".../reset"`。
  本 repo preview 改吃新入口 `preview-all.scss`（= `reset` + `index`）。

- **所有 DS 樣式進 CSS `@layer`**（`pi-reset` < `pi`）
  含 `resources/scss/**` 與 `assets/symicon.css`。
  對下游的影響：**專案未分層的樣式從此永遠勝過 DS**，衝突時不必再靠特異度
  或 `!important`，也不用管 import 順序。反之，若專案原本依賴「DS 蓋掉自己
  某條樣式」，那條會反轉 —— 導入時請做一次前後截圖比對。

- **`$font-path` 預設值 `"../../fonts"` → `"/fonts"`**
  原本的相對路徑對所有消費端都解不到（Sass 不 rebase `url()`，Vite 是相對
  於 entry CSS 檔的目錄解），`vite build` 會警告 `didn't resolve at build time`。
  preview 的 icon 之所以正常，是靠 `assets/symicon.css` 另外宣告了一份同名的
  symicon `@font-face`。新預設值對 `sass` CLI / Vite dev / Vite build / 專案
  `public/fonts/` 四者皆成立；下游原本手動覆寫成 `'/fonts'` 的可以移除覆寫。

### Added — 平台化 Phase 2 + 3（Blade 元件與 prototype 系統）

- **13 支 Blade 元件**（`resources/views/components/`）：`alert` / `button` /
  `callout` / `checkbox` / `content-switcher` / `dropdown-item` /
  `form-control` / `loading` / `modal` / `notification` / `pagination` /
  `radio` / `toggle`。呼叫寫法 `<x-pi::button tone="success" />`。
  SCSS 一行都沒有改 —— 只加了 markup 層。
  `border` / `radius` / `shadow` 不轉元件（是 utility class，沒有自己的結構）。
- 每支元件配 `.meta.php`（props / slots / notes / examples）。examples 的 code
  是可直接 render 的合法 blade，同一份資料同時餵 preview 頁與 CLAUDE.md 清單。
- **元件的 prop 守衛**：不存在的組合直接丟例外，而不是輸出不存在的 class。
  各元件的 tone 清單其實不同（button 8 個、callout/alert 6 個、toggle 5 個、
  checkbox/radio 4 個、content-switcher 3 個），守衛照 SCSS 實際定義收斂。
- `resources/views/layouts/preview.blade.php` —— prototype 的殼。
- **`preview/`：blade preview 的 Laravel app**（開發工具，`export-ignore`）。
  PHP 在單一 php-cli container（port 8100），Vite 在 host（5178）。
  不接公司 Laradock —— 本 repo 是 library，沒有 build step / DB / queue。
  元件目錄頁與 prototype 清單都由檔案掃描生成，新增內容不必改 preview。
- **`prototypes/`：PM & AI 的討論區**（`export-ignore`）。
  page / fragment / fixtures / `_hosts` 四層結構，附 `project-a` 完整範例。
- `@piFixture($x, 'name')` directive + `src/Prototype/FixtureLoader.php`。
- `scripts/sync-component-list.php` —— meta → CLAUDE.md 元件清單（`--check`
  給 CI）。meta 的 `notes` 中以 ⚠️ 開頭者會被抽成「已知元件缺口」段落。
- `scripts/fetch-host.php` —— 抓專案頁面 rendered HTML 當宿主快照。
- `scripts/apply.php` —— fragment → blade 片段或 unified diff patch。
- CLAUDE.md 新增 prototype 六條硬性規則與自動生成的元件清單。

### ⚠ Breaking — 套件名與 namespace 定案

Placeholder 換成正式名稱。**現在改的成本是零** —— 還沒有任何專案依賴這個套件；
等 Phase 4 接上第一個專案之後才改，就變成使用端也要同步的 breaking change。

| | 之前 | 之後 |
|---|---|---|
| Composer 套件名 | `company/pi-design-system` | `pi-tw/pi-design-system` |
| PSR-4 namespace | `Company\PiDesignSystem\` | `PiTw\PiDesignSystem\` |

`pi-tw` 是公司的 GitHub org（Symmetry Information Co., Ltd.）。

Repo 目前仍在個人帳號下，尚未推到 `pi-tw` org。這不影響套件名 —— composer 的
vendor 名與 repo 放在哪無關（VCS repository 是拿 URL 去抓、再核對 `composer.json`
的 `name`）。搬到 org 之後要改的只有各專案的 `composer config repositories.pi vcs <URL>`。

### Changed — `pm-to-preview` skill 改走 blade 路線

Skill 原本產 `preview-static/<name>.html`（裸 HTML）。blade 路線做好後若不改 skill，
PM 討論流程仍走舊路線，Phase 2 + 3 等於沒被用到。

- 產物改為 `prototypes/<project>/{pages,fragments}/<name>.blade.php` + `fixtures/*.php`
- 流程第 1 步新增「判定 page 還是 fragment」與「確認專案名」
- 第 2 步要求先讀 `.meta.php` 確認 props 值域（各元件 tone / size 清單不一致）
- 刪掉「新頁需兩處註冊」—— prototype 清單頁是掃檔生成的
- `references/preview-setup.md` → `prototype-setup.md`（整份重寫）
- 前端 handoff 的元件清單由「`gl_` class 表」改為「元件 + props 表」，並新增
  `apply.php` 的套用指令；後端 handoff 的資料欄位表改為指向 fixture 檔
- 新的回歸基準：`prototypes/project-a/pages/salary-report.blade.php`
  + `.scratch/salary-report/{frontend,backend}-handoff.md`
- `.scratch/` 六份舊路線 handoff 加標記，避免被照抄

### Added — `form-control` / `toggle` 的 `id` prop

兩支元件原本**無法被外部 `<label for>` 關聯**：`id` 若當一般 attribute 傳，會跟著
`$attributes` 落在外層的 `div` / `label` 上，`for` 指過去是無效的。新增 `id` prop，
落在真正的 control（`input` / `select` / `textarea`、toggle 的內層 checkbox）。

`checkbox` / `radio` 不需要 —— 它們的 `<label>` 已包住 input，是隱式關聯。

### Fixed — `scripts/apply.php`（端到端實跑與 code review 抓到）

- **只支援 fragment** —— 只找 `prototypes/<project>/fragments/`，且無條件要求
  manifest 有 `slot`。page prototype 完全無法交接。已加 page 支援：
  `--output=blade` 印出可貼的整頁 blade；`--output=patch` 對 page 明確擋掉並說明
  原因（整頁搬進專案是新增檔案，沒有插入點，產 diff 無意義）。
- **殘留檢查誤判** —— prototype 的說明註解提到 `@piFixture` 這個字串就被判定為
  「移除後仍有殘留」而中止。改為只認帶括號的呼叫。
- **交出的 blade 含已失效的指示** —— prototype 檔頭常寫「交接時刪掉下面兩行
  `@piFixture`」，但那兩行已被腳本移除，前端會照指示去找不存在的行。現在會一併
  移除「提到 `@piFixture` / `@piFragment` 的 blade 註解」；講設計決策的註解
  （組合件、元件缺口）不含這兩個字，會保留。
- **`preg_replace` 回傳 null 未處理** —— 失敗時後續的殘留檢查會對 null 比對而
  靜默通過，正是該段要防的事。改為失敗即中止並印出 `preg_last_error_msg()`。
- 檔頭 docblock 與 `--help` 仍寫 `<fragment-name>`、只描述 fragment 流程，已同步。

### Fixed — Phase 2 + 3

- `icon-shield-checked` 不存在（symicon 的正確名稱是 `icon-shield-check`）。
  `preview-static/callout.html`、`modal.html`、`assets/icons-preview.html`
  這三處的圖示原本是空的。抓出這個錯字的機制是「把 preview 實際輸出的
  142 個 class 拿去比對編譯後的 CSS」。

### Changed — 與 spec 的兩處偏離（spec 該處有缺陷）

- **fixture 載入**：spec 的 `@php($x = include __DIR__.'/../fixtures/x.php')`
  不可能運作 —— Blade 編譯成 `storage/framework/views/` 的快取檔後，`__DIR__`
  指向快取目錄而非 prototype 原始檔。改為 `@piFixture` directive。
- **slot marker**：spec 用 blade 註解，但 blade 註解 render 後會消失，抓回的
  宿主快照裡就沒有錨點。改為 HTML 註解 `<!-- @pi-slot: … -->`，同一個 marker
  同時服務 `apply.php`（改 blade 原始碼）與 fragment 注入（改 rendered HTML）。
- `preview/` → `preview-static/`（靜態 HTML 對照頁讓位給 Laravel app）。

### Added
- **Composer 套件本體**：`composer.json`（`pi-tw/pi-design-system`，
  依賴僅 `illuminate/support`）+ `src/PiDesignSystemServiceProvider.php`。
  提供 Blade 命名空間 `pi`（`<x-pi::button />`）與 `@piFragment` directive。
  元件 blade 本身是 Phase 2。
- `resources/scss/_layers.scss` —— `@layer` 順序宣告，必須最先載入。
- `resources/scss/_component-base.scss` —— 元件契約：`corner-shape` 與
  `font-weight: 500`。這兩項沒有任何標準 reset 會提供，少了元件會變成
  正圓角 + 字重 400。已實測不載 `reset.scss` 時元件仍完全正確。
- `.gitattributes` 的 `export-ignore` 定義套件出貨範圍（69 檔 / 320 KB）。
  出貨含 `fonts/` 與 `assets/symicon.css`（元件吃 `icon-*` class）。
- `scripts/check-build.mjs` 從 8 項擴充到 14 項，新增分層防護：順序宣告
  位置、無未分層規則、`index` 產物不含 reset、元件契約存在。
- STRUCTURE.md 新增「分層規則」與「元件契約 vs reset」兩節。
- README 新增「三個 SCSS 入口，選哪個」與「樣式隔離（`@layer`）」兩節。

### Fixed
- `reset.scss` 補全域 `box-sizing: border-box`（主流 reset 都有，本檔原本
  沒有）。連帶修正兩處既有 bug：`.gl_alert-body` 原本因 padding 超出自己
  宣告的 `max-width: 736px`（實際渲染 768px）；`.form-prompt-text` 原本
  `width: 100%` + padding 溢出父容器。
- `@extend .fz-tit` 4 處改為 `@include fz-tit`。`@extend` 會把宣告輸出在
  「被 extend 的原始規則位置」，使用端在 `@layer` 內時樣式會逃出 layer
  變成未分層，破壞隔離保證。

### 其他
- README 新增「icon 字型（symicon）維護」章節：分「本預覽專案升級」與「其他 Vite 專案使用」兩情境。

### Changed
- repo 定位明確為「前端切版對照用，不發布 npm」。
- `package.json` 改名 `@yourteam/design-system` → `pi-design-system`，移除 npm 發版機制（`exports`/`main`/`files`/`publishConfig`/`prepublishOnly`/`repository` placeholder）。
- README / STRUCTURE / SKILL 去 npm 化、去 sandbox 化，改述本機預覽與 vendored 使用。

### Removed
- 散佈產物 CSS：`styles.css`、`colors_and_type.css`、`assets/components.css`（無下游 link，repo 純預覽用）。

---

## [0.1.0] — 2026-04-23

首次 npm 化發版。把原本散在各專案的 token + component 集中成可安裝的 package。

### Added
- `src/` SCSS 架構：`tokens/` + `base/` + `components/`
- 完整 tokens：`$cl-*`（7 色 × 12 階 + alpha overlay）、`$fz-*`、`$space-*`、`$radius-*`、`$shadow-*`、`$dur-*`、`$ease-*`
- 語意 aliases：`$fg`、`$bg`、`$surface`、`$border`、`$brand` 等
- Component：`.gl_btn`、`.gl_form-group` / `.gl_form-control`、`.gl_checkbox-layout`、`.gl_radio-layout`、`.gl_toggle`、`.gl_alert-body`、`.gl_callout-wrap`、`.gl_loading`、`.gl_dropdown-item`、`.gl_content-switcher`、`.gl_pagination`、`.gl_modal`、`.gl_notification-item`
- `package.json` 設定 subpath exports（`tokens` / `base` / `components` / `components/*`）
- `preview/tokens.html` — 單頁可搜尋 / 可複製的 token 對照表
- `docs/ai-guide.md` — 給 AI 切版 agent 的 cheatsheet

### Changed
- 顏色 token 改名：`danger` → `red`、`warning` → `yellow`、`success` → `green`、`info` → `blue`（語意別名保留，例如 `$brand` 仍指向 `$cl-green-500`）
- 移除 `primary` ramp（原 teal #009688），logo 色改用 `$cl-green-500`

### Migration from legacy `colors_and_type.css`

舊專案用 `<link rel="stylesheet" href=".../colors_and_type.css">` 的可繼續用；新專案請改成：

```scss
@use "@yourteam/design-system" as *;
```

之後會在 1.0.0 把 `colors_and_type.css` 標記為 deprecated。

---

<!--
版本紀錄範本（複製以下區塊到 [Unreleased] 上方）：

## [0.x.y] — YYYY-MM-DD

### Added
- ...

### Changed
- ...

### Deprecated
- ...

### Removed
- ...

### Fixed
- ...

### Security
- ...

### Breaking
- ...

-->
