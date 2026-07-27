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

### Added
- **Composer 套件本體**：`composer.json`（`company/pi-design-system`，
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
