# CLAUDE.md — 原型專案規則（每次對話自動載入）

> 這是**原型 repo**：PM 與 AI agent 討論功能 → 產出畫面與流程。
> 所有畫面樣式**一律以 vendored 的 Pi Design System 為基準**，不自由發揮。
> 原型定案後，blade + scss 會複製進 production 專案。

## 語言
- 回覆**一律使用繁體中文**，禁止簡體字。

## 最高原則：先讀 DS 指南（產任何畫面前）
- 產任何頁面 / 元件前，**先讀 `resources/pi-ds/docs/ai-guide.md`**（vendored 進來的 DS 指南）。規則照抄，不要自由發揮。
- 樣式只能用 vendored Pi DS **實際存在**的 token / class。不確定就 `grep resources/pi-ds/resources/scss/` 或讀 `resources/pi-ds/resources/scss/tokens/`、`resources/pi-ds/resources/scss/components/` 確認，**絕不憑記憶或推測發明**。
- 需要 DS 沒有的值 / 元件時，**先問使用者**，不要自己編一個（不自創 token、不自創 class）。

> vendored DS 路徑：`resources/pi-ds/`（= 從 guideline repo 複製的 `resources/scss/` + `docs/ai-guide.md`）。若實際放別的路徑，改這份文件對應即可。

## 樣式產出規則
- **優先用現成 class**：`.gl_*` 元件 class（如 `.gl_btn .gl_btn-success .gl_btn-md`）+ utility（`.fz-*`、`.flex-*` 等）拼 markup，盡量不自己寫樣式。
- 真的需要頁面級 layout 才寫**少量 page scss**，且**一律 `@use` DS token**：
  ```scss
  @use "../pi-ds/tokens" as *;   // 路徑依實際 vendored 位置調整
  .prototype-hero {
    padding: $sp-4;
    background: $surface;
    border-radius: $radius-md;
    color: $fg;
  }
  ```
- **禁止**（照 ai-guide 硬規則）：
  - 禁寫 hex（`#333`）→ 用 `$fg` / `$fg-2` / `$cl-*`
  - 禁 `font-size: Npx` → 用 `.fz-*` class 或 `$fz-*`
  - 禁非 4 倍數間距 → 用 `$sp-*`
  - 禁自寫 button / modal / form → 用 `.gl_*`
  - 禁漸層背景（功能型產品不用漸層）
  - 禁 shadow + 1px border 並用（擇一）

## 字型（原型階段先忽略）
- 原型階段**不處理字型**：用系統 fallback 字即可（中文照樣顯示，字寬/行高會微跑版，可接受）。
- **不要**引 Google Fonts、不要掛 DS 的 `_fonts.scss`、不要自己塞 `@font-face`。
- 若 `@use` 整包 DS index 觸發 font 404 warning → 改成**只 `@use "../pi-ds/tokens"`** 避開。
- 字型 / icon 字型（symicon）等**原型定案、進 production 階段再處理**。

## icon（原型階段）
- icon 也是字型（symicon）。原型階段若未帶 `symicon.css` + 字型檔，icon 不會顯示 → 用文字或 emoji 佔位即可。
- 若要 icon 真的顯示，另外把 guideline 的 `assets/symicon.css` + `fonts/` 一起帶進來（進 production 階段再做也行）。

## 產出格式
- 畫面用 **blade** + **scss**（對齊 production 是 Laravel blade + scss）。
- scss 用 `@use` DS token；不要寫成純 css（純 css 斷開 token，進 production 要重工）。

## 複製進 production 時
- 搬的是**你產的 blade + page scss**；DS 本體 production 端應已有自己一份（同版本），不重複複製。
- 唯一要手動調的是 **`@use` 路徑**（原型的 `../pi-ds/...` → production 的 DS 實際位置）；class / token 名完全不動。
- 確認 production 的 DS 版本 = 原型當時複製的版本，否則 token 可能對不上。

## 修改範圍
- 使用者要求小修改時，只動該處，不要順手「改善」其他部分。
