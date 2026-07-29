# 會員註冊頁（jobar） — 前端 Handoff

> 本次 PM 需求：註冊表單新增「同意隱私權政策」勾選；表單下方新增一個 CTA（標題 + 內文 + 一顆按鈕）。
> 表單欄位（姓名 / Email / 密碼）是為了讓上述兩項有真實脈絡而補齊的最小集合。

## 版面結構

```
.jb-form（max-width 480，置中）
├─ h1「建立帳號」                      fz-headline-sm fz-tit
├─ 欄位：姓名                          label + form-control
├─ 欄位：Email                        label + form-control（含 prompt 說明文字）
├─ 欄位：密碼                          label + form-control
├─ 同意隱私權政策                       checkbox（★ 本次新增）
├─ 動作區：註冊鈕
└─ CTA 區塊                            callout + 右側按鈕（★ 本次新增）
```

## 元件清單

| 實例 | Pi DS 元件 | props |
|---|---|---|
| 姓名 | `<x-pi::form-control>` | `id="name" name="name" placeholder="例：陳怡君" state/feedback（必填 hint）` |
| Email | `<x-pi::form-control>` | `id="email" type="email" name="email" prompt="註冊後會寄一封驗證信到這個信箱" state/feedback` |
| 密碼 | `<x-pi::form-control>` | `id="password" type="password" name="password" placeholder="至少 8 個字元" state/feedback` |
| 同意隱私權政策 | `<x-pi::checkbox>` | `tone="success" name="agreePrivacy" value="1"` |
| 註冊鈕 | `<x-pi::button>` | `tone="success" icon-left="icon-checked"` |
| CTA 區塊 | `<x-pi::callout>` | `tone="info" icon="icon-info" title="已經有帳號了？"` |
| CTA 按鈕 | `<x-pi::button>` | `as="a" size="sm" variant="outline" tone="success"` |

### 必填未填的 hint

用 form-control 既有機制，**不是自刻**：`state="invalid"` + `feedback="<訊息>"`
（`_form.scss` 的 `.is-invalid .form-feedback`，`.is-invalid` 掛在 group 上）。
訊息來源是 fixture 的 `errors`（欄位名 => 訊息），blade 不寫死文案：

```blade
:state="isset($errors['name']) ? 'invalid' : null"
:feedback="$errors['name'] ?? null"
```

⚠️ **prototype 預設帶滿三筆錯誤**，這樣 PM 開 preview 就直接看到 hint 長相。
靜態 blade 沒有「點註冊鈕才出現」的行為 —— 觸發是前端 JS（client 端即時驗證）
或後端 validator 回傳後重繪。要看乾淨初始畫面：把 fixture 的 `errors` 改成 `[]`。

各元件 props 值域已逐個對 `resources/views/components/<name>.meta.php` 確認：
checkbox 只有 basic / info / success / danger（無 warning / purple）；callout 6 個 tone（無 orange）。

## 歧義收斂

- 「CTA（標題 + 內文 + 按鈕）」→ `<x-pi::callout>` + `<x-slot:action>` 放按鈕。
  PM 已在兩個選項間選定 callout（橫向提示條，左側圓形 icon），**不是置中大 CTA 區塊** ——
  後者要自刻 wrapper（組合件），PM 選擇不走。
- 「勾選按鈕」→ `<x-pi::checkbox>`（非 `<x-pi::toggle>`）—— 同意條款是核取語意，不是開關。

## 組合件

無。本頁全部命中現有元件，零自創 class / token / SCSS。

## Page-scoped SCSS

- **6 行**，用途：表單容器寬度置中（`.jb-form`）、欄位與標籤間距（`.jb-field`、`.jb-field__label`）、
  勾選與 CTA 的上下留白（`.jb-consent`、`.jb-cta`）、動作區橫排（`.jb-actions`）。
- 未超過 `CLAUDE.md` 第 3 條的 30 行上限。

## 已知元件缺口（已提報，非本次新提案）

- **field-label** — 設計系統沒有欄位標籤元件（`_form.scss` 內無 label 相關 class）。
  本頁沿用 `salary-report` 的疊法：`<label class="fz-title-sm fz-tit" for="…">` + `form-control` 的 `id` prop 綁定。
  4 欄提議見 `.scratch/salary-report/frontend-handoff.md`，不重複提報。

## 待補

- 🔧 **「隱私權政策」的可點連結** — 設計系統沒有 inline link 樣式（已 grep `resources/scss` 確認），
  做成連結會是裸 `<a>`。prototype 目前是純文字。要嘛前端自行決定連結樣式，要嘛走缺口流程提報 inline-link。
- 🔧 CTA 按鈕的 `href` 目前是 `'#'`，登入頁實際路由待補。
- 🔧 **checkbox 沒有 feedback 機制** — `x-pi::checkbox` 只有 tone / labelSize，
  `_checkbox.scss` 內沒有錯誤訊息或錯誤色的結構（已確認）。所以「未勾同意隱私權政策」
  這個必填錯誤，**目前無法用欄位級 hint 呈現**。三個可能作法（要前端／設計決定，我沒自己刻）：
  1. 頁面級 `<x-pi::callout tone="danger">` 列出未完成項目
  2. 未勾時註冊鈕 `disabled`（button 吃 `disabled` 或 `class="gl_disabled"`），根本不讓按
  3. 走缺口流程提報 checkbox 的 error 狀態
- 🔧 必填欄位的視覺標記（星號 / 「必填」字樣）—— 設計系統沒有對應樣式，prototype 未加。

## 狀態 + 互動（AI 推測）

- **頁面級**：送出中的整頁 loading、註冊成功後導向哪裡、已登入者進入此頁的處理 — ⚠️ 待確認
- **欄位級**：
  - 未勾「同意隱私權政策」時註冊鈕是否 `disabled`（button 支援 `disabled` 屬性或 `class="gl_disabled"`）— ⚠️ 待確認
  - **必填 hint 的觸發時機** — PM 要求「點註冊鈕時顯示」。是純 client 端 JS 即時驗證，
    還是送出後由後端 validator 回傳訊息重繪？兩者的 hint 樣式相同（`state="invalid"` + `feedback`），
    差別在誰產生訊息 — ⚠️ 待確認（後端契約見 backend-handoff.md 的 `errors`）
  - 密碼強度提示是否要用 `prompt` / `prompt-tone` — ⚠️ 待確認
  - 送出後按鈕狀態（loading 用 `<x-pi::loading>` 疊在鈕內？）— ⚠️ 待確認

## 待確認新件

無。

## Prototype

- 路徑 `prototypes/jobar/pages/member-register.blade.php`
- 預覽 `http://localhost:8100/prototypes/jobar/member-register`
- 起動：`cd preview && docker compose up -d && npm run dev`

## 套用方式

```bash
php scripts/apply.php jobar member-register     # 印出可直接貼的 blade
```

這是 **page**，沒有 `--output=patch`（page 是整頁新檔，無插入點）。貼進專案後只動兩處：

1. 刪掉 `@piFixture` 那兩行 —— 資料改由 controller 傳入
2. `@extends('pi::layouts.preview')` 換成專案自己的 layout

`@section('content')` 內的 body 一個字都不用改 —— 專案與 preview 吃同一版元件套件。
