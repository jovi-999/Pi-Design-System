# 三段式版面（註冊表單） — 後端 Handoff

> pm-to-preview skill 實戰驗證產出。對應前端 `frontend-handoff.md`。僅表單內容有資料意涵；AD banner / sidebar / footer 為純視覺組合件，不進後端。

## 資料欄位清單

| 欄位 | 型別 | 對應元件 | 備註 |
|------|------|----------|------|
| name | string | 表單 input#1（姓名） | 🔧 待補（長度上限？） |
| email | string | 表單 input#2（Email） | 🔧 待補 |
| privacy_consent | boolean | checkbox（同意隱私權） | 🔧 待補（是否需記錄同意時間/版本？） |

## 選項 / 資料來源

- 無下拉/選項欄位（本表單無 select）。

## 送出目標 / API 意圖

- 表單送出目標 endpoint / method：? — ⚠️ 待確認
- 是否需 email 唯一性檢查 / 建立帳號流程 — ⚠️ 待確認
- privacy_consent 未勾時是否阻擋送出（前端 disabled + 後端強制）— ⚠️ 待確認

## 驗證規則

| 欄位 | 規則 |
|------|------|
| name | 必填 — 🔧 待補 |
| email | 必填、email 格式、唯一性 — 🔧 待補 |
| privacy_consent | 必為 true 才可送出 — 🔧 待補 |
