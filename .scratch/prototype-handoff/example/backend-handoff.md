# 會員註冊頁 — 後端 Handoff

> 端到端範例（05 驗證用）。對應前端 `frontend-handoff.md`。

## 資料欄位清單

| 欄位 | 型別 | 對應元件 | 備註 |
|------|------|----------|------|
| name | string | 表單 input#1（姓名） | 🔧 待補（長度上限？） |
| email | string | 表單 input#2（Email） | 🔧 待補 |
| industry | enum/string | 表單 select（產業別） | 🔧 待補（存 code 還是 label？） |

## 選項 / 資料來源

- select「產業別」選項來源：目前 preview 為 hard-code（半導體/金融服務/電子商務）— 🔧 待補（是否改由 API / 常數表提供）

## 送出目標 / API 意圖

- 表單送出目標 endpoint / method：? — ⚠️ 待確認
- 是否需重複 email 檢查 / 建立帳號流程 — ⚠️ 待確認

## 驗證規則

| 欄位 | 規則 |
|------|------|
| name | 必填 — 🔧 待補 |
| email | 必填、email 格式、唯一性 — 🔧 待補 |
| industry | 必填、須為選項之一 — 🔧 待補 |
