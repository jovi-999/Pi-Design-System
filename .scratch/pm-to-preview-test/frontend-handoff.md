# 三段式版面（AD banner + sidebar + 註冊表單 + footer） — 前端 Handoff

> pm-to-preview skill 實戰驗證產出。輸入描述：「1/(1+1)/1 版面：上 AD banner、中左 sidebar 右表單、下 footer；sidebar 窄 1 內容寬 3；AD 純色條；sidebar 導覽連結清單；表單含 name/email/同意隱私 checkbox/送出鈕；footer 版權連結條」。

## 版面結構

```
page（單欄，內容區三段）
├─ [1]   AD banner        整寬置頂，純色條
├─ [1+3] 中段 2 欄（flex, gap 24）
│        ├─ aside sidebar（flex:1，窄）— 導覽連結直列
│        └─ div 內容（flex:3，寬）— 註冊表單 max-width 480
└─ [1]   footer           整寬置底，版權 + 連結
```

## 元件清單

| 實例 | Pi DS class | 說明 |
|------|-------------|------|
| 姓名欄 | `gl_form-group` + `input.gl_form-control` | 命中現有元件 |
| Email 欄 | `gl_form-group` + `input.gl_form-control` | `type="email"`；命中現有元件 |
| 同意隱私 checkbox | `gl_checkbox-layout gl_checkbox-success` | 命中現有元件 |
| 註冊鈕 | `gl_btn gl_btn-md gl_btn-success` | 命中現有元件 |

## 歧義收斂

- 「AD banner」→ 收斂為**純色條**（`--cl-basic-900` 底 + 文字），非圖片輪播（PM 確認）。若日後改多張輪播 → 轉真缺件（carousel，走提議 loop）。
- 「sidebar 導覽連結」→ 收斂為**純 `<a>` 連結直列**（`--link`/`--fg` 上色），非 `gl_dropdown` 面板。

## 組合件（⚠️ 待確認）

- **AD banner** — `--cl-basic-900` 底 + `--radius-md` + 純 HTML — ⚠️ 待確認「組合而非正式元件」（門檻第 2 層，未進提議 loop）。
- **sidebar** — flex 直列 + `--bg-muted` 底 + `--border` + `--radius-md`，連結 `--link`/`--fg` — ⚠️ 待確認「組合而非正式元件」。
- **footer** — `--border` 上分隔線 + flex 兩端對齊 + `--fg-3` 版權字 + `--link` 連結 — ⚠️ 待確認「組合而非正式元件」。

## 狀態 + 互動（AI 推測）

- **頁面級**：整頁 loading / 空狀態 / 未登入權限 — ⚠️ 待確認（本頁未觸發）
- **欄位級**：input 空值/error/disabled 樣式走 `.gl_form-control` 內建；error 需搭 error 呈現 — ⚠️ 待確認
- **欄位級**：註冊鈕在「未勾同意隱私」時是否 disabled；送出 loading / 送出後狀態；驗證觸發時機（blur / submit）— ⚠️ 待確認
- **互動**：sidebar 連結目標路由、目前頁 active 樣式 — ⚠️ 待確認

## 待確認新件

- 無真缺件（AD banner / sidebar / footer 皆以組合件處理，見上「組合件」段）。

## Preview

- 路徑 `preview/example-layout-3col.html`
- `npm run dev` → `localhost:5173/preview/index.html#example-layout-3col`（已註冊 PAGES「Prototype」群組）
