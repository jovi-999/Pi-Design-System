# 交棒規格模板（前端 + 後端）

**兩份分開的 markdown**（前端、後端各一），因為前後端都有真人接棒。

## 標記約定（兩份共用）

- `⚠️ 待確認` — AI 推測、需人確認的內容（狀態、互動、API 意圖）。
- `🔧 待補` — PM/AI 階段無法決定、由接棒者補（資料型別、選項來源、驗證規則）。
- `🆕 待確認` — AI 提議的新元件／新 token，尚未定案（觸發與審批見 [missing-component.md](missing-component.md)）。

## 前端 handoff 模板

必列：版面結構、元件清單、狀態+互動（頁面級+欄位級）、Preview、待確認新件。有才列：歧義收斂、組合件。

```markdown
# [頁面名] — 前端 Handoff

## 版面結構
<區塊樹 / 欄數 / 上下左右排列關係>

## 元件清單
| 實例 | Pi DS class | 說明 |
|------|-------------|------|
| 主送出鈕 | gl_btn gl_btn-md gl_btn-success | ... |

## 歧義收斂
<!-- 僅有元件歧義時列；記「PM 說法 → 收斂成哪個 Pi DS 元件 + 理由」。無歧義刪此段 -->
- 「選單」→ 原生 `select.gl_form-control`（非 `gl_dropdown-item` 面板）— 表單單選情境

## 組合件（⚠️ 待確認）
<!-- 缺件門檻第 2 層：無正式元件、用現有 token 疊出者。無則刪此段 -->
- <組合件名> — 用 <哪些 token> 疊出 — ⚠️ 待確認「組合而非正式元件」

## 狀態 + 互動（AI 推測）
- 頁面級：整頁 loading / 空狀態 / 權限 — ⚠️ 待確認
- 欄位級：<input error / disabled / loading / 送出後；點擊 / 驗證觸發> — ⚠️ 待確認

## 待確認新件
<!-- 缺件門檻第 3 層：真缺件、PM 同意的新元件 -->
- <新提議元件> — 🆕 待確認

## Preview
- 路徑 `preview/<name>.html`；`npm run dev` → `localhost:5173/preview/index.html#<hash>`
```

## 後端 handoff 模板

必列：資料欄位清單、選項/資料來源、送出目標/API 意圖、驗證規則。

```markdown
# [頁面名] — 後端 Handoff

## 資料欄位清單
| 欄位 | 型別 | 對應元件 | 備註 |
|------|------|----------|------|
| email | string | 表單 input#1 | 🔧 待補 |

## 選項 / 資料來源
- select「<名稱>」選項來源：? — 🔧 待補

## 送出目標 / API 意圖
- 表單送出目標 endpoint / method：? — ⚠️ 待確認

## 驗證規則
| 欄位 | 規則 |
|------|------|
| email | 必填、email 格式 — 🔧 待補 |
```

## 兩處耦合（別重做）

- 前端「版面結構 + 元件清單」= 第 1 步給 PM 看的回述摘要，同一結構。
- 擱置的新件（PM 審批選「擱置」）：兩份 handoff 底部各列一行「擱置項」，不進正式清單。

## 已驗證範例輸出

`.scratch/prototype-handoff/example/frontend-handoff.md` 與 `backend-handoff.md`（會員註冊頁）是照本模板產出的完整範例，可對照。
