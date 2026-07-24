# Preview 機制：目錄、跑法、引用、註冊

盤點自本專案現有 preview 機制（範本 `preview/button.html`、`preview/index.html`）。

## 落地目錄與啟動

- **落地目錄**：`preview/`（原始碼、可跑）。build 產物在 `dist-preview/preview/`（gitignore）。
- **啟動**：`npm run dev`（vite HMR，改 SCSS 免 build，主要方式，port 5173 自動開 `/preview/index.html`）。build：`npm run preview:build` / `preview:serve`。設定在 `vite.config.js`（scss 用 modern-compiler；build input 逐頁列在 `rollupOptions.input`）。

## preview HTML 引用方式（照抄範本 `preview/button.html` 的 head）

三支 CSS link：

- `/src/index.scss` ← **設計系統唯一真相源**（tokens + base + components）
- `/preview/preview.scss` ← preview 專用排版（`.wrap` / `.panel` / `.row` / `.dek`）
- `/assets/symicon.css` ← icon 字型（需要才引）

頁殼結構：head 三 link → `.wrap` → `<header>` → 多個 `<section>`（h2 + `.dek` + `.panel gl_border-inner` 展示區）。每頁頂部有 `<!-- @dsCard ... -->` metadata 註解。

## class / token 慣例

- 元件前綴 `gl_`（`gl_btn gl_btn-md`、`gl_border-inner`、`gl_shadow-xs`）。
- 字級 `fz-*`（`fz-title-xs`、`fz-headline-xl`）。
- token 以 CSS var 用（`var(--cl-basic-900)`、`var(--radius-md)`）。

## 新增元件頁：兩處註冊

1. `preview/index.html` 的 PAGES 陣列（sidebar + iframe 頁殼）。
2. `src/components/index.scss`（`@forward`）—— 若新增了元件 SCSS 才需要；純用現有 class 的 prototype 頁通常只需第 1 處。

## 原始碼位置

- 元件 `src/components/_<name>.scss`；token `src/tokens/_*.scss`；總入口 `src/index.scss`。
- 相關文件：`STRUCTURE.md`、`docs/ai-guide.md`（Figma 名稱 ↔ class 對照）、`SKILL.md`。
- 原則：禁自創 token / class；改元件須同步 preview HTML + `docs/ai-guide.md`。
