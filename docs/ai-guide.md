# AI Agent Guide — Pi Design System

> **給 AI 切版 agent 讀的 cheatsheet**（Claude Code、Cursor、Copilot、Aider...）。
> 切任何新頁面前，先讀這份文件。規則照抄即可，不要自由發揮。

---

## 0. TL;DR（三句話）

1. **顏色** 只能用 `$cl-*` 或語意別名（`$fg`, `$bg`, `$border`, `$brand`...）；**禁止** 寫 hex code。
2. **字級** 只能用 `.fz-*` class 或 `$fz-*` 變數；**禁止** 直接寫 `font-size: 14px`。
3. **元件** 優先用 `.gl_*` class；真的沒有再自己寫，但命名沿用 `.gl_` 前綴並對齊既有 pattern。

---

## 1. 切版前的 Checklist

每個新頁面開工前，按順序做：

1. `cat preview-static/tokens.html` — 知道有哪些 token
2. `ls resources/scss/components/` — 知道有哪些現成 component
3. 看 Figma 設計 → 對照下方「Figma ↔ DS 對照表」把元件名對起來
4. 寫 markup（用 `.gl_*` + utility class）
5. 真的需要自訂樣式時，**import tokens 再寫**：
   ```scss
   @use "pi-design-system/tokens" as *;
   .my-feature { padding: $sp-4; color: $fg; }
   ```

---

## 2. 嚴禁行為（Hard rules）

| ❌ 不要 | ✅ 改成 |
|---|---|
| `color: #333` | `color: $fg;`（或 `$fg-2`、`$fg-3`） |
| `color: #10b981` | `color: $cl-green-500;` 或 `$brand` |
| `background: white` | `background: $surface;` |
| `border: 1px solid #eee` | `border: 1px solid $border;` |
| `font-size: 14px` | `@extend .fz-body-sm;` 或 `font-size: $fz-body-sm;` |
| `padding: 16px 20px` | `padding: $sp-4 $sp-5;` |
| `border-radius: 8px` | `border-radius: $radius-sm;` |
| `box-shadow: 0 2px 4px rgba(0,0,0,.1)` | `box-shadow: $shadow-sm;` |
| 自己寫 `<button class="my-btn">` | `<button class="gl_btn gl_btn-success gl_btn-md">` |
| 自己寫 modal / toast / checkbox | 用 `.gl_modal` / `.gl_alert-body` / `.gl_checkbox-layout` |

---

## 3. 顏色決策樹

```
要表達什麼？
├─ 純文字顏色           → $fg（主）/ $fg-2（次）/ $fg-3（placeholder）/ $fg-4（disabled）
├─ 頁面背景             → $bg / $bg-muted / $bg-subtle
├─ 卡片 / 浮層背景      → $surface / $surface-raised
├─ 邊框                 → $border / $border-strong
├─ 連結                 → $link / $link-hover
├─ CTA / 品牌主色       → $brand（= $cl-green-500）
├─ 語意色
│  ├─ 成功 / 正向        → $cl-green-500
│  ├─ 資訊 / 連結        → $cl-blue-500
│  ├─ 警示 / 提醒        → $cl-yellow-500
│  ├─ 錯誤 / 刪除        → $cl-red-500
│  ├─ VIP / 付費         → $cl-orange-500  ⚠️ 只能用在付費相關
│  └─ AI / 智慧推薦      → $cl-purple-500  ⚠️ 只能用在 AI 相關
└─ 需要更淺 / 更深      → 同 family 的 50 / 100（淺）或 600 / 700（深）
```

**不要** 用 `$cl-orange-*` 當一般 accent；**不要** 用 `$cl-purple-*` 當裝飾。

---

## 4. Figma ↔ DS 對照表

設計師在 Figma 用的是中文命名，工程要記得對應：

### Button

| Figma 名稱 | HTML class |
|---|---|
| Button / Primary / MD | `.gl_btn .gl_btn-success .gl_btn-md` |
| Button / Secondary / MD | `.gl_btn .gl_btn-secondary-success .gl_btn-md` |
| Button / Ghost / MD | `.gl_btn .gl_btn-ghost-basic .gl_btn-md` |
| Button / Outline / MD | `.gl_btn .gl_btn-outline-basic .gl_btn-md` |
| Button / Danger | `.gl_btn .gl_btn-danger .gl_btn-md` |
| Icon Button | `.gl_btn .gl_btn-icon .gl_btn-ghost-basic .gl_btn-md` |

Size token：`xs`(32px) / `sm`(40px) / `md`(48px, **預設**) / `lg`(56px) / `xl`(64px)
顏色 token：`basic` / `info` / `success`(= brand) / `warning` / `danger` / `orange` / `purple`

### Typography

| Figma 名稱 | class | 用途 |
|---|---|---|
| Headline / XXL | `.fz-headline-xxl` + `.fz-tit` | Hero 主視覺 |
| Headline / XL | `.fz-headline-xl` + `.fz-tit` | 頁面主標 |
| Headline / LG | `.fz-headline-lg` + `.fz-tit` | section lead |
| Title / LG | `.fz-title-lg` + `.fz-tit` | 卡片主標 |
| Title / MD | `.fz-title-md` + `.fz-tit` | 表單 label |
| Title / XS | `.fz-title-xs` + `.fz-tit` | 小型標籤 |
| Body / MD | `.fz-body-md` | 預設內文 |
| Body / SM | `.fz-body-sm` | 密集資訊 |
| Body / XS | `.fz-body-xs` | 時間戳、metadata |

### Form

| Figma 名稱 | HTML |
|---|---|
| Input / Text | `<div class="gl_form-group"><input class="gl_form-control">...</div>` |
| Input with icon | `<div class="gl_icon-input-wrap">...</div>` 包住 `.gl_form-control` |
| Select | `<select class="gl_form-control">` |
| Textarea | `<textarea class="gl_form-control">` |
| Checkbox | `.gl_checkbox-layout.gl_checkbox-success` |
| Radio | `.gl_radio-layout.gl_radio-success` |
| Toggle | `.gl_toggle.gl_toggle-success.gl_toggle-md` |
| Hint text | `.form-prompt-text` |
| Validation ok | `.is-valid > .form-feedback` |
| Validation err | `.is-invalid > .form-feedback` |

### Others

| Figma 名稱 | class |
|---|---|
| Alert / Toast | `.gl_alert-body.gl_alert-success` |
| Callout | `.gl_callout-wrap.gl_callout-info` |
| Tabs | `.gl_content-switcher-outer` > `.gl_content-switcher.is-active` |
| Pagination | `.gl_pagination-outer.gl_pagination-md` > `.gl_pagination` |
| Modal | `.gl_modal` + `.gl_modal-header` + `.gl_modal-content` + `.gl_modal-footer` |
| Dropdown menu item | `.gl_dropdown-item.gl_dropdown-item-md` |
| Notification row | `.gl_notification-item.gl_notification-info` |
| Loading spinner | `.gl_loading` 內放 `<i class="icon icon-_loading">` |

---

## 5. 間距 / 圓角 / 陰影

**間距**：永遠用 4 的倍數。`$sp-1`(4) / `-2`(8) / `-3`(12) / `-4`(16) / `-6`(24) / `-8`(32)...

**圓角**：
- 小 chip、tag → `$radius-xs` (4)
- input、小按鈕 → `$radius-sm` (8)
- ⭐ 預設卡片 / 按鈕 → `$radius-md` (12)
- feature card、modal → `$radius-lg` (16)
- hero、marketing → `$radius-xl` (24)
- avatar、pill → `$radius-pill` (999)

**陰影**：
- 卡片預設 → `$shadow-sm`
- hover 抬起 → `$shadow-md`
- modal → `$shadow-xl`
- ⚠️ **不要** 同時用 shadow + 1px border，擇一

---

## 6. 圖示（Icon）

全部從 `symicon` 字體。用法：

```html
<i class="icon icon-search" aria-hidden="true"></i>
```

- 顏色用 `currentColor`（繼承父元素 `color`）
- 不要用 SVG 自己畫
- icon-only 按鈕一定要有 `aria-label`
- 找不到合適圖示 → 開 `preview-static/icons.html` 搜尋；還是沒有 → 問人，不要自己造

---

## 6.5 字型（Font）⚠️

字型是最容易被 AI 寫壞的部分。請嚴守以下規則：

### 引用字型只能用變數

```scss
// ❌ 永遠不要這樣
font-family: "Inter", sans-serif;
font-family: "Microsoft JhengHei";

// ✅ 一律用 token
font-family: $font-sans;       // 內文（Noto Sans TC + system fallback）
font-family: $font-display;    // 數字大標（Google Sans Flex）
font-family: $font-icon;       // 圖示
```

### 不要重新宣告 @font-face

```scss
// ❌ 不要在 component / 頁面 SCSS 裡寫
@font-face { font-family: "Inter"; ... }
@import url("https://fonts.googleapis.com/...");
```

字型 **只在 `resources/scss/base/_fonts.scss` 一個地方** 宣告。如果你看到要載新的字型，先去問 DS owner，不要在自己的檔案裡偷加。

### Figma 標的字型 → DS 對照

| Figma 標的 font-family | 你該用的 token | 備註 |
|---|---|---|
| Google Sans Flex | `$font-sans` | 英數與 UI 文字 |
| Noto Sans TC | `$font-sans` | 中文主字體，**不用** 自己寫 |
| Microsoft JhengHei / 微軟正黑 | `$font-sans` | 同上，DS 已處理 |
| Google Sans Flex display variants | `.ft-semicondensed` / `.ft-condensed` | **僅限數字 / 統計**，不要拿來寫文字 |
| symicon-fill | `$font-icon` | 用 `<i class="icon icon-*">` 不用直接設 |

### Blade / HTML 端

不要在 `<head>` 裡硬塞 `<link rel="stylesheet" href="https://fonts.googleapis.com/...">`。Design system 的字型由 SCSS 透過 `@font-face` 載，HTML 只要把字型檔放在 `public/fonts/` 對應位置即可。

如果你正在做 Figma → Blade 的轉換，遇到設計稿用了 DS 之外的字型：

1. **先暫停**，這是設計變更，不是工程任務
2. 確認設計師確實要換字型而不是工具自動填的 fallback
3. 確定要換 → 去 `resources/scss/base/_fonts.scss` 走 [README 「替換字型」場景 B] 流程
4. 不要在頁面層偷偷塞新字型

### 字型路徑問題（FOUT / 404）

如果 console 出現字型 404：**不是你頁面的問題**，是 design system 的 `$font-path` 沒對齊使用端的字型存放位置。回 README「字型管理」章節找對應場景設定 `$font-path`。

不要為了避開 404 就改成 Google Fonts CDN —— 那會破壞 design system 的字型一致性，請先解決路徑問題。

---

## 7. 常見錯誤（AI 最愛犯的）

### ❌ 自己發明漸層背景
```scss
background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
```
✅ 我們是功能型產品，**不用漸層**。

### ❌ 亂加陰影 + border
```scss
box-shadow: $shadow-sm;
border: 1px solid $border;
```
✅ 擇一：要抬起用 shadow，要分隔用 border。

### ❌ 亂縮間距
```scss
gap: 15px;   /* 非 4 的倍數 */
padding: 13px 22px;
```
✅ 只能用 4 的倍數。

### ❌ 直接用 primary 當 CTA
```html
<button style="background: #009688">  <!-- primary 是 logo 色 -->
```
✅ CTA 用 `$cl-green-500`（success），primary 只用在 logo / hero mark。

### ❌ 為中文字元手動調整字距
```scss
letter-spacing: 0.5px;
```
✅ 中文字距已由 `.fz-*` class 設定好，不要再加。

### ❌ 用 em 或 % 設間距
```scss
margin-top: 1.5em;
padding: 1rem;
```

---

## 8. 新 component 提案

發現需要的元件 DS 沒有？**不要自己做完就收工**：

1. 在 repo 開 issue：`[Proposal] New component: XXX`
2. 附上 Figma 連結 + 使用情境 + 建議 class name（沿用 `.gl_` 前綴）
3. 等 DS owner review 再動手；合併後會進 `resources/scss/components/`
4. 在原專案先用 **臨時 class**（例如 `.local_xxx`）標示，合併後再替換

---

## 9. 快速自檢

提交 PR 前跑一遍這個清單：

- [ ] 沒有任何 hex code（除了 tokens 本身）
- [ ] 沒有 `font-size: Npx`（改用 `.fz-*` 或 `$fz-*`）
- [ ] 所有 padding / margin / gap 是 4 的倍數
- [ ] 按鈕用 `.gl_btn` 而不是自寫
- [ ] 表單用 `.gl_form-group` 包住 `.gl_form-control`
- [ ] 圖示用 `<i class="icon icon-*">` 而不是 SVG
- [ ] 沒有漸層背景（除非設計稿明確要求且有 DS owner 核可）
- [ ] 成對開啟 `preview-static/tokens.html` 和 `preview-static/components.html` 對照過

---

## 10. 範例：一個標準卡片

```html
<article class="my-card">
  <h3 class="fz-title-lg fz-tit">台積電</h3>
  <p class="fz-body-sm">新竹科學園區 · 月薪 45,000–62,000</p>
  <div class="my-card__actions">
    <button class="gl_btn gl_btn-success gl_btn-md">投遞履歷</button>
    <button class="gl_btn gl_btn-ghost-basic gl_btn-md">收藏</button>
  </div>
</article>
```

```scss
@use "pi-design-system/tokens" as *;

.my-card {
  padding: $sp-4;
  background: $surface;
  border-radius: $radius-md;
  box-shadow: $shadow-sm;
  color: $fg;

  &__actions {
    margin-top: $sp-4;
    display: flex;
    gap: $sp-2;
  }

  &:hover { box-shadow: $shadow-md; }
}
```

---

有疑問 → 先看 `preview-static/tokens.html` 找 token，再看 `preview-static/components.html` 找 pattern，都找不到再問人。
