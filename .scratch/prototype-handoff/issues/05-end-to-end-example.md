# 05 端到端範例驗證

Type: prototype
Status: resolved
Blocked by: 01, 02, 03, 04

## Question

拿一個具體描述（例：頁面含 navbar + 一個表單，表單有 2 個 input、1 個 select）跑完整流程：依定好的輸入格式收描述 → 用現有 Pi DS component 產出可跑 HTML preview + 結構化描述 → 依定好的 handoff 格式輸出交棒規格。驗證整條流程可用，暴露格式缺口。用 `/prototype` skill。

## Answer

**流程跑通，整條可用。** 範例：「navbar + 表單（姓名/Email input + 產業別 select + 送出鈕）」。

### 走過的四步（對應 01→04）
1. **輸入（01）**：自由文字描述 → AI 回述「元件 + 版面」結構化摘要（見兩份 handoff 頂部）。
2. **元件對應**：input/select/button 全命中現有元件（門檻第 1 層）；「選單」歧義收斂為原生 `select.gl_form-control`（非 `gl_dropdown-item` 面板）。
3. **缺件（04）**：navbar 無正式元件 → **門檻第 2 層「組合件」**（`--cl-basic-900` + `--radius-md` + flex 疊得出）→ preview 直接跑、handoff 標 `⚠️ 待確認`「組合而非正式元件」，**未進提議 loop**。無真缺件。
4. **產物**：
   - 可跑 preview：`preview/example-signup.html`（已註冊 PAGES「Prototype」群組）。
   - handoff（02 兩份分開）：`.scratch/prototype-handoff/example/{frontend,backend}-handoff.md`。

### 驗證結果
- `npm run dev` → `/preview/example-signup.html` 回 **200**；`/src/index.scss` 編譯 **200** 無 error。
- **零自創**：全用現有 `gl_*` class + 現有 CSS var，無新 token/class/SCSS。

### 暴露的格式缺口（可接受，記錄不阻塞）
1. **「元件歧義如何收斂」無專屬欄位** —— dropdown vs select 的收斂結果目前塞在前端元件清單「說明」欄，可行但隱晦。
2. **「組合件」無獨立段** —— navbar 混在元件清單以 `⚠️` 標，可行；若組合件變多，前端模板可加獨立「組合件」段。
3. **前端模板只有欄位級狀態，缺頁面級狀態**（整頁 loading / 空狀態 / 權限）—— 本例未觸發，記錄。

三點皆屬「模板可加強」而非「流程不通」，本次終點（跑通 + 定格式）已達成。
