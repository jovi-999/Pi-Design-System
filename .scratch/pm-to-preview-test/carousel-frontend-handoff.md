# 輪播圖 banner — 前端 Handoff

> ⚠️ **舊路線產物**（blade 化之前）。產出的是 `preview-static/*.html`、元件清單列 `gl_` class。
> 結構可參考，但**不要照抄** —— 現行流程見 `.claude/skills/pm-to-preview/`，範例見 `.scratch/salary-report/`。

> pm-to-preview skill 實戰驗證（提議 loop）。輸入描述：「一個自動輪播圖片 banner（多張圖輪替）」。門檻第 3 層真缺件 → PM 已同意做新件。

## 版面結構

```
page
└─ hero — carousel banner（整寬，多張圖自動輪替）
```

## 元件清單

| 實例 | Pi DS class | 說明 |
|------|-------------|------|
| （無命中件）| — | 唯一元件 carousel 為待確認新件，見下 |

## 待確認新件

- **`carousel`** — 🆕 待確認
  1. 暫定名稱 + 用途：`carousel` — 多張圖片自動輪播 + 可手動切換的橫幅
  2. 最接近現有件：`content-switcher`（已有 `is-active` 分頁切換），缺自動輪播、圖片 slide、箭頭/指示點
  3. 為何組不出：需 JS 定時輪替 + 過場動畫 + 指示點/箭頭狀態，現有 token+HTML 疊不出行為
  4. prototype 頂替：單張靜態圖 + `gl_border-outer` 框 + 「🆕 carousel（待確認）」標籤（見 preview）
  - 正式 class 待設計。

## 狀態 + 互動（AI 推測）

- **頁面級**：整頁 loading / 圖片載入失敗 fallback — ⚠️ 待確認
- **carousel 互動**：自動輪替間隔、hover 暫停、左右箭頭、指示點點擊跳頁、觸控滑動 — ⚠️ 待確認（正式件設計時定）

## Preview

- 路徑 `preview-static/example-carousel.html`
- `npm run dev` → `localhost:5173/preview-static/index.html#example-carousel`（已註冊 PAGES「Prototype」群組）
