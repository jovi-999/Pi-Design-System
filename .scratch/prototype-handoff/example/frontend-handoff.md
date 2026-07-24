# 會員註冊頁 — 前端 Handoff

> 端到端範例（05 驗證用）。輸入描述：「頁面最上一條 navbar，下面一個表單，2 個 input（姓名 / Email）+ 1 個下拉選單（產業別）+ 送出鈕」。

## 版面結構

```
page (單欄置中)
├─ navbar        ← 置頂橫幅，深底，左 brand / 右「登入」鈕
└─ form          ← 置中，max-width 480，欄位由上而下：
   ├─ input 姓名
   ├─ input Email
   ├─ select 產業別
   └─ button 送出註冊
```

## 元件清單

| 實例 | Pi DS class | 說明 |
|------|-------------|------|
| 姓名欄 | `gl_form-group` + `input.gl_form-control` | 命中現有元件 |
| Email 欄 | `gl_form-group` + `input.gl_form-control` | `type="email"`；命中現有元件 |
| 產業別選單 | `gl_form-group` + `select.gl_form-control` | 「選單」歧義收斂為原生 `select`（非 `gl_dropdown-item` 選單面板） |
| 送出鈕 | `gl_btn gl_btn-md gl_btn-success` | 命中現有元件 |
| navbar 登入鈕 | `gl_btn gl_btn-sm gl_btn-ghost-basic` | 命中現有元件 |

## 歧義收斂

- 「下拉選單」→ 原生 `select.gl_form-control`（非 `gl_dropdown-item` 面板）— 表單單選情境。

## 組合件（⚠️ 待確認）

- **navbar 本體** — 用現有 token（`--cl-basic-900` 底、`--radius-md`）+ 純 HTML flex 疊出 — ⚠️ 待確認「組合而非正式元件」（04 門檻第 2 層，未進提議 loop）。

## 狀態 + 互動（AI 推測）

- **頁面級**：整頁 loading / 空狀態 / 未登入權限 — ⚠️ 待確認（本頁未觸發）
- **欄位級**：input 空值/error/disabled 樣式 `.gl_form-control` 內建 `:focus` / `[disabled]`；error 需搭 `.is-invalid .form-feedback` — ⚠️ 待確認
- **欄位級**：送出鈕 loading / 送出後狀態；驗證觸發時機（blur / submit）— ⚠️ 待確認

## 待確認新件

- 無真缺件（navbar 以組合件處理，見上「組合件」段）。

## Preview

- 路徑 `preview/example-signup.html`
- `npm run dev` → `localhost:5173/preview/index.html#example-signup`（已註冊 PAGES「Prototype」群組）
