# 輪播圖 banner — 後端 Handoff

> ⚠️ **舊路線產物**（blade 化之前）。產出的是 `preview-static/*.html`、元件清單列 `gl_` class。
> 結構可參考，但**不要照抄** —— 現行流程見 `.claude/skills/pm-to-preview/`，範例見 `.scratch/salary-report/`。

> pm-to-preview skill 實戰驗證（提議 loop）。對應 `carousel-frontend-handoff.md`。

## 資料欄位清單

- carousel 為**純視覺新件**，本次描述無資料意涵（圖片來源/連結未提）→ 依 04 規則「純視覺新件不進後端」，暫無後端資料欄位。
- 🆕 待確認：若正式 carousel 需由後台管理輪播圖（圖片 URL / 連結 / 排序 / 檔期），屆時再補資料欄位；元件未定案，型別連帶未定。

## 送出目標 / API 意圖

- 無表單送出。若改為後台可維護的輪播內容 → 需一組 banner CRUD API — ⚠️ 待確認
