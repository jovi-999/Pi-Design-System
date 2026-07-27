# 03 現有 preview 機制盤點

Type: research
Status: resolved
Blocked by:

## Question

盤點本專案現有的 component preview 機制：可跑 HTML preview 放哪個目錄、用什麼 build/dev 指令啟動、既有 preview 檔怎麼引用 component/token。目的：決定 prototype 產物該落在哪、怎麼跑，供 `05 端到端範例` 使用。AFK，用 `/research` 子代理盤點。

## Answer

- **落地目錄**：`preview/`（原始碼、可跑）；build 產物在 `dist-preview/preview/`（gitignore）。
- **啟動指令**：`npm run dev`（vite HMR，改 SCSS 免 build，主要方式，port 5173 自動開 `/preview/index.html`）。build：`npm run preview:build` / `preview:serve`。設定在 `vite.config.js:15-51`（scss 用 modern-compiler；build input 逐頁列在 rollupOptions.input）。
- **preview HTML 引用方式**（範本 `preview/button.html:8-10`）：三支 CSS link —
  - `/resources/scss/index.scss` ← 設計系統唯一真相源（tokens+base+components）
  - `/preview/preview.scss` ← preview 專用排版（.wrap/.panel/.row/.dek）
  - `/assets/symicon.css` ← icon 字型（需要才引）
- **class 慣例**：元件前綴 `gl_`（`gl_btn gl_btn-md`、`gl_border-inner`、`gl_shadow-xs`）；字級 `fz-*`（`fz-title-xs`、`fz-headline-xl`）；token 以 CSS var 用（`var(--cl-basic-900)`、`var(--radius-md)`）。
- **範本可照抄**：頁殼 `preview/index.html`（sidebar + iframe，PAGES 陣列在 `:142-239` 註冊每頁）；元件頁 `preview/button.html`（head 三 link → `.wrap` → `<header>` → 多個 `<section>`：h2 + `.dek` + `.panel gl_border-inner` 展示區）。每頁頂部有 `<!-- @dsCard ... -->` metadata 註解。
- **新增元件頁**要兩處註冊：`preview/index.html` PAGES 陣列 + `resources/scss/components/index.scss`（@forward）。
- **原始碼**：元件 `resources/scss/components/_<name>.scss`；token `resources/scss/tokens/_*.scss`；總入口 `resources/scss/index.scss`。
- **相關文件**：`STRUCTURE.md`、`docs/ai-guide.md`（Figma 名稱↔class 對照）、`SKILL.md`。原則：禁自創 token/class；改元件須同步 preview HTML + `docs/ai-guide.md`。
