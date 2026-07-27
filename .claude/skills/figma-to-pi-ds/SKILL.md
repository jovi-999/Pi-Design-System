---
name: figma-to-pi-ds
description: 將 Figma 設計樣式落地成 Pi Design System 的 SCSS 元件 / token 的標準流程。觸發：使用者提供 Figma 連結（figma.com）並要求「依 Figma 修改/重做某元件樣式」、「把 Figma 寫進 Pi DS」、「同步設計稿」，或要調整 resources/scss/components 樣式以對齊設計稿時。
---

# Figma → Pi DS 落地流程

把 Figma 樣式寫進 Pi DS 時，**逐步照做、逐項驗證**。最高原則：**只用實際存在的 token / class，嚴禁自創**（見專案 CLAUDE.md）。

## 流程（每步做完再下一步）

1. **讀 Figma 規格**（需 node 專屬 URL，含 `node-id`）
   - `mcp__figma__get_design_context`（nodeId + fileKey）→ 取 reference code + 截圖
   - 需要變數值再 `mcp__figma__get_variable_defs`
   - 從產出抓：尺寸、間距(gap/padding)、圓角、字級/行高/字重、顏色變數、各狀態（active/hover）、各尺寸變體（md/sm…）

2. **對照現有 DS**（禁自創）
   - `ls resources/scss/components/` 找對應 `_<元件>.scss`；`grep` 既有 class / token
   - 把每個 Figma 值**映射到既有 token**：
     - 圓角 → `resources/scss/tokens/_radius.scss`（如 8px = `$radius-sm`）
     - 顏色 → `resources/scss/tokens/_colors.scss`（如 `$cl-basic-900` / `$cl-basic-0` / `$cl-green-500`）
   - 任一值找不到對應 token → **停下問使用者來源**，不要自己編

3. **確認範圍（breaking change 須先問）**
   - 改 `resources/scss/components/*.scss` = 真實 DS 元件，下游 7 專案共用 → 屬 breaking change
   - 若需改 markup 結構 / class 命名 → 先用 AskUserQuestion 跟使用者確認範圍與命名
   - 命名慣例：外層 `gl_<name>-outer` > 內層 `gl_<name>`；尺寸修飾 `gl_<name>-md` / `-sm`；色彩變體 `cl-success` / `cl-info`（參考 `_pagination.scss`、`_content-switcher.scss`）

4. **改 SCSS**（`resources/scss/components/_<元件>.scss`）
   - 檔頭 `@use "../tokens" as *;`
   - 互動元素加 `box-sizing: border-box;`（避免 min-width + padding 疊加溢出）
   - 多尺寸度量用 `@mixin` 統一，勿重複
   - RWD：手機/平板尺寸用 `@media (max-width: 768px)`（tablet 斷點）讓 md 自動縮為 sm 度量
   - 顏色一律 token（`$cl-*`），尺寸 px 字面值可接受（對齊既有檔風格）

5. **同步更新**（缺一不可）
   - `preview/<元件>.html`：markup + `.usage` 的 `<pre>` 範例 + notes（尺寸/色彩/規則）
   - `docs/ai-guide.md`：Figma 名稱 ↔ class 對照表那一列
   - README / SKILL.md 若有引用該 class 也要改

6. **驗證**
   - `npm run build`（sass 編譯）→ 無誤
   - `grep` 產出 `dist/pi-ds.css` 確認新 class / 規則存在
   - `npm run dev` 開 preview 目視對照 Figma 截圖
   - 通過後才 `git commit`（訊息標明 breaking change 與下游需同步 markup）

## 常見錯誤（過去踩過）

- 自創 token（如 `$shadow-ring`）→ DS 沒有就不要用，先查再用
- 漏 `box-sizing: border-box` → `min-width:40 + padding:0 12px` 在 content-box 變 64px 寬，跑版
- 箭頭 icon 用錯：箭頭 `→` 是 `icon-arrow-left/right`，非 chevron `›`（`icon-chevron-*`）；用前 `grep assets/symicon.css` 確認
- 只改 preview 沒改 src（或反之）→ 預覽與真實元件漂移
- 改了 class 命名 / 結構卻沒同步 docs 與下游 → breaking 沒收尾

## 參考實例

`resources/scss/components/_pagination.scss` + `preview/pagination.html`：2026-06 依 Figma 重做的範例（`gl_pagination-outer > gl_pagination`、透明無底、md 40 / sm 32、≤768px 自動縮、box-sizing 修溢出）。
