# 02 Handoff 規格格式：交前後端的 spec 欄位

Type: grilling
Status: resolved
Blocked by:

## Question

討論完 prototype 後，交給前後端的規格文件要包含哪些欄位才夠他們接力？候選：使用的 Pi DS component/class 清單、版面結構、互動行為、資料/API 需求、狀態（空/載入/錯誤）、待確認新件標記、preview 連結。要定出格式（markdown 模板？欄位順序？前端段 vs 後端段是否分開）。

## Answer

**兩份分開的 markdown 文件**（前端、後端各一），前後端都有真人接棒。

### 標記約定（兩份共用）
- `⚠️ 待確認` — AI 推測、需人確認的內容（狀態、互動、API 意圖）。
- `🔧 待補` — PM/AI 階段無法決定、由接棒者補（資料型別、選項來源、驗證規則）。
- `🆕 待確認` — AI 提議的新元件/新 token，尚未定案（詳細流程見 `04 缺件流程`）。

### 前端 handoff 模板
```markdown
# [頁面名] — 前端 Handoff

## 版面結構
<區塊樹 / 欄數 / 上下左右排列關係>

## 元件清單
| 實例 | Pi DS class | 說明 |
|------|-------------|------|
| 主送出鈕 | gl_btn gl_btn-md gl_btn-success | ... |

## 狀態 + 互動（AI 推測）
- <input error / disabled / loading / 送出後；點擊 / 驗證觸發> — ⚠️ 待確認

## 待確認新件
- <新提議元件> — 🆕 待確認

## Preview
- 路徑 `preview/<name>.html`；`npm run dev` → `localhost:5173/preview/index.html#<hash>`
```

### 後端 handoff 模板
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

**欄位範圍**：前端 = 元件清單+class、版面、狀態+互動、preview、待確認新件（全要）。後端 = 資料欄位、選項/資料來源、送出目標/API 意圖、驗證規則（全要）。

**耦合**：
- `01` 的 AI 回述確認摘要 = 前端模板的「版面結構 + 元件清單」兩段 —— 同一結構，回述時給 PM 看的即這兩段。
- `🆕 待確認` 的觸發與審批細節在 `04` 定；本票只定它在文件裡怎麼標。
