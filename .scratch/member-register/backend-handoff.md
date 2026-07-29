# 會員註冊頁（jobar） — 後端 Handoff

## 資料契約

Fixture 檔就是契約 —— controller 傳出同樣結構即可，前端 blade 不用改：

| Fixture | 對應變數 | 結構 |
|---|---|---|
| `prototypes/jobar/fixtures/member-register-form.php` | `$form` | `['name' => string, 'email' => string, 'password' => string, 'agreePrivacy' => bool, 'errors' => array]` |
| `prototypes/jobar/fixtures/member-register-cta.php` | `$cta` | `['tone' => string, 'icon' => string, 'title' => string, 'body' => string, 'action' => ['label' => string, 'href' => string]]` |

`$form` 的用途是**回填**（驗證失敗重繪時把使用者填過的值放回去），不是初始資料來源。

### `errors` —— 必填未填的 hint 契約

```php
'errors' => [
    'name'     => '請填寫姓名',
    'email'    => '請填寫 Email',
    'password' => '請設定密碼',
],
```

`欄位名 => 訊息`，刻意對齊 Laravel validator 的輸出形狀。有值的欄位會被 blade 轉成
`state="invalid"` + `feedback`（紅底提示）。空陣列 = 乾淨初始態。

交接後可直接用 Laravel 的 `$errors` MessageBag（`$errors->first('name')`）餵進來，
或在 controller 轉成上面這個陣列。**訊息文案由後端出**（前端 blade 不寫死）—— 🔧 待補實際文案。

⚠️ prototype 預設帶滿三筆錯誤，只是為了讓 PM 看到 hint 長相，不代表初始畫面該長這樣。

`$cta` 是純文案 —— 若 CTA 文案要進 CMS / 語系檔，這份結構就是欄位清單；
若寫死在 view 或 config 也可以，由後端決定。⚠️ 待確認。

## 型別待補

| 欄位 | 目前 fixture 值 | 實際型別 |
|---|---|---|
| `password` | `''`（字串） | 回填時**不應該**回傳密碼原文 —— 這個 key 是否要從 controller 移除？— 🔧 待補 |
| `agreePrivacy` | `false`（bool） | 送出值是 `"1"`（checkbox 的 `value`），需 cast 成 bool — 🔧 待補 |
| `cta.action.href` | `'#'` | 登入頁 route name / URL — 🔧 待補 |

## 選項 / 資料來源

- 本頁沒有 select 欄位，無選項來源需求。
- 隱私權政策的條文版本：註冊時是否要記錄「同意的是哪一版政策」（版本號 / 同意時間戳）？
  法遵常見要求，但 prototype 沒有這個欄位 — ⚠️ 待確認。

## 送出目標 / API 意圖

- 表單送出 endpoint / method：？— ⚠️ 待確認（推測 `POST /register`）
- 註冊成功後行為：直接登入並導向 / 導向「請收驗證信」頁面？— ⚠️ 待確認
  （Email 欄位的 prompt 文字寫「註冊後會寄一封驗證信到這個信箱」，代表有驗證信流程）
- 驗證信寄送是同步還是進 queue — 🔧 待補

## 驗證規則

| 欄位 | 規則 |
|---|---|
| `name` | 必填、長度上限？— 🔧 待補 |
| `email` | 必填、email 格式、**unique**（已註冊要回什麼訊息？）— 🔧 待補 |
| `password` | 必填、最少 8 字元（prototype 的 placeholder 寫「至少 8 個字元」）、複雜度規則？是否要確認密碼欄位？— 🔧 待補 |
| `agreePrivacy` | 必填且必須為 true（`accepted` rule）— ⚠️ 待確認。注意：**checkbox 元件沒有 feedback 機制**，這個錯誤前端無法用欄位級 hint 呈現，呈現方式見 frontend-handoff.md 的三個選項 |

驗證失敗時前端呈現方式：`<x-pi::form-control state="invalid" feedback="…">`。
每個欄位的錯誤訊息文案 — 🔧 待補。

## 擱置項

無。
