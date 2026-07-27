# SKILL.md — Pi Design System 規則手冊（給 AI agent）

> 這份文件給 Claude / Cursor / Copilot 等 AI 工具看的。  
> 在這個 repo 裡寫程式時，**先讀完這份**再動手。如果規則和你直覺衝突，以這份為準。  
> 人類請看 `README.md`；這份是寫給 agent 的「短而硬」版本。

---

## 一、心智模型：這個 repo 是什麼

```
Pi-Design-System/         ← token 與 component 的唯一真相來源
├── resources/scss/       ← 你寫的 SCSS（唯一可改的地方）
├── preview-static/              ← 視覺對照頁（Vite，npm run dev 即時看）
├── assets/symicon.css    ← icon 字型 @font-face + class + codepoint
└── fonts/                ← 字型檔（含 icon 字型 symicon-X.s）
```

**這個 repo 不發布 npm。** 用途：給前端切版時對照目前的元件樣式與 class，`npm run dev` 本機跑預覽即可。下游專案實際使用時用 vendored 方式（複製需要的 `resources/scss/*.scss` / `assets/symicon.css` / `fonts/` 進自己專案），以本 repo 為唯一真相。

---

## 二、Class 命名規則（最重要）

### 1. `resources/scss/components/_*.scss` 是**唯一真相**

所有 `.gl_*` class 必須在 `resources/scss/components/_xxx.scss` 裡看到定義。
- ❌ 不要在 preview 自己發明 class（例如 `.gl_field`、`.gl_input`）
- ✅ 改 demo 前先 `grep -r "\.gl_xxx" resources/scss/components/` 確認 class 存在
- ✅ 找不到對應 class → 那是 DS 缺的，去 `resources/scss/components/_*.scss` 補上

### 2. 已存在的 class 結構（**不要記錯**）

| 元件 | 正確寫法 |
|---|---|
| Input | `<div class="gl_form-group"><input class="gl_form-control"></div>` |
| Textarea | `<div class="gl_form-group"><textarea class="gl_form-control"></textarea></div>` |
| Select | `<div class="gl_form-group"><select class="gl_form-control">…</select></div>` |
| Checkbox | `<div class="gl_checkbox-layout gl_checkbox-success"><label><input type="checkbox"><span class="fz-body-sm">…</span></label></div>` |
| Button | `<button class="gl_btn gl_btn-success gl_btn-md">…</button>` |
| Icon-prefixed input | `<div class="gl_icon-input-wrap"><i class="icon icon-search"></i><input class="gl_form-control"></div>` |

如果你不確定，**讀對應 `_*.scss` 檔**確認結構，不要憑記憶。

### 3. 命名慣例

- `.gl_<name>` — 主元件（`.gl_btn`、`.gl_form-control`）
- `.gl_<name>-<color>` — 色彩 modifier（`.gl_btn-success`、`.gl_checkbox-danger`）
- `.gl_<name>-<size>` — 尺寸 modifier（`.gl_btn-md`）
- `.gl_<name>-layout` — wrapper / 佈局容器（`.gl_checkbox-layout`）
- `.gl_<name>-wrap` — 配件容器（`.gl_icon-input-wrap`）
- `.fz-*` / `.bg-*` / `.text-*` — utility class（不是元件）

---

## 三、Token 規則

### 1. 顏色 / 間距 / 圓角 一律用 SCSS 變數

```scss
// ✅ 對
.my-card {
  background: $cl-green-500;
  padding: $sp-4;
  border-radius: $radius-md;
}

// ❌ 錯
.my-card {
  background: #00aa90;        // 不要寫 hex
  padding: 16px;              // 不要寫死數字（除非 < 4px 的 1/2 px 微調）
  border-radius: 12px;        // 不要寫死數字
}
```

### 2. Token 來源

| 想用 | `@use` 哪個 | 變數命名 |
|---|---|---|
| 顏色 | `tokens/colors` | `$cl-<hue>-<step>`、`$cl-basic-alpha-<step>` |
| 間距 | `tokens/spacing` | `$sp-1` ~ `$sp-16`（4/8/12/16/20/24/32/40/48/64） |
| 圓角 | `tokens/radius` | `$radius-xs/sm/md/lg/xl/pill` |
| 陰影 | `tokens/shadow` | `$shadow-xs` ~ `$shadow-xxl`（無 ring；ring = `.gl_shadow-*` + `.gl_border-outer/inner`） |
| 字體 | `tokens/typography` | `$font-sans`、`$font-display`、`$font-icon` |
| 動畫 | `tokens/motion` | `$dur-fast/base/slow`、`$ease-standard` |
| 斷點 | `tokens/breakpoints` | `$bp-sm/md/lg/xl` |

在 component 檔最上面寫：
```scss
@use "../tokens" as *;
```
就可以直接用 `$cl-green-500`、不用前綴。

### 3. 顏色語意

- **Basic** = 中性灰 — body text、border、surface
- **Success** (green) = 主 CTA / 正面確認 — **大部分按鈕用這個**
- **Primary** (teal) = 品牌 mark only — 不要拿來當 CTA
- **Info** (blue) = 中性提示
- **Warning** (yellow) = 黃色提醒
- **Danger** (red) = 錯誤 / 破壞性
- **Orange** = VIP / premium 專用，不要拿來當一般 accent
- **Purple** = AI / 智能推薦專用，不要拿來當一般 accent

---

## 四、字型規則

### 絕對不要做的

```scss
// ❌ 不要在 component 檔加 @import
@import url('https://fonts.googleapis.com/...');

// ❌ 不要在 component 檔加 @font-face
@font-face { font-family: "Inter"; src: ... }

// ❌ 不要寫死 family 名
.title { font-family: "Inter", sans-serif; }
```

### 應該做的

```scss
// ✅ 字型只在 resources/scss/base/_fonts.scss 一個地方宣告
// ✅ 引用一律用變數
.title { font-family: $font-sans; }
.price { font-family: $font-display; }
.icon  { font-family: $font-icon; }
```

文字字型路徑透過 `$font-path !default` 控制，**不要在 base/_fonts.scss 裡寫死路徑**。icon 字型走 `assets/symicon.css`（不在 `_fonts.scss`）。

詳見 README「字型管理（Fonts）」與「icon 字型（symicon）維護」章節。

---

## 五、加新 Component 的 SOP

加一個新 component（例如 `_breadcrumb.scss`）時，**3 個地方都要動到**，缺一不可：

1. ✅ 建立 `resources/scss/components/_breadcrumb.scss`
   - 第一行 `@use "../tokens" as *;`
   - class 用 `.gl_breadcrumb` 開頭
2. ✅ `resources/scss/components/index.scss` 加一行 `@forward "breadcrumb";`
3. ✅ `preview-static/` 加對照頁（給人類看視覺），並在 `preview-static/index.html` 左目錄登記

只做 1 沒做 2，`npm run dev` 預覽會載不到新元件。

---

## 六、版本規則（semver）

| 改了什麼 | bump |
|---|---|
| 修字型內容（同 family 同檔名） | patch（`0.1.0 → 0.1.1`） |
| 加新 token / 新 component / 新字重 | minor（`0.1.0 → 0.2.0`） |
| 改 class 名 / 移除 token / 換字型 family | **major**（`0.x → 1.0.0`），CHANGELOG 必須寫遷移指引 |

repo 不發布 npm，version 僅作內部標記；改動前先 `npm run dev` 在預覽頁驗證、寫好 CHANGELOG。

---

## 七、檔案編輯規則

### 可以改的
- `resources/scss/**/*.scss` — DS 主程式
- `preview-static/*.html` — 視覺對照頁
- `README.md`、`SKILL.md`、`CHANGELOG.md`、`STRUCTURE.md` — 文件
- `package.json` 的 metadata、dependencies、scripts

### 動之前要想清楚的
- `dist/` — build 產物，`.gitignore` 中，勿手改
- `node_modules/`
- `fonts/` — 字型原始檔，只在「替換文字/icon 字型」的場景動
- `assets/symicon.css`、`assets/icon-cp-map.json` — icon 字型 class 與 codepoint，升級 icon 字型時才動（流程見 README）

---

## 八、Component 規則速查（之後會擴充）

> 這節之後會隨 component 完善逐步補；目前先列幾個已經寫好的關鍵點。

### Button (`.gl_btn`)
- 必須同時有 color modifier（`-success`/`-info` 等）+ size modifier（`-xs/-sm/-md/-lg/-xl`）
- variant：預設 = filled；`-outline` / `-ghost` / `-secondary` 三種變體
- CTA 用 `gl_btn-success`，**不要**用 `gl_btn-primary`（primary 留給品牌 mark）

### Form (`.gl_form-group` + `.gl_form-control`)
- input 一定要包在 `.gl_form-group` 裡，不然不會有 focus 樣式
- label 自己用 `.fz-body-sm` + 行間距，不要用 `.gl_label`（沒這個 class）

### Checkbox (`.gl_checkbox-layout`)
- 結構：`.gl_checkbox-layout > label > input[type="checkbox"] + span`
- 顏色 modifier 加在 layout 上：`.gl_checkbox-layout.gl_checkbox-success`
- 4 種顏色：`-success`、`-info`、`-danger`、`-basic`

### Alert / Callout / Notification
- 三個不同元件，**不要混用**：
  - `.gl_alert` — 頁面級提示，可關閉
  - `.gl_callout` — 內容區塊內的引導框
  - `.gl_notification` — 浮動通知（toast）

---

## 九、出錯時去哪查

| 症狀 | 看哪裡 |
|---|---|
| `Undefined variable $xxx` | `resources/scss/tokens/index.scss` 是否 `@forward` 了該檔 |
| `.gl_xxx` 沒套到樣式 | `resources/scss/components/_xxx.scss` 確認 class 真的存在 |
| 預覽元件載不到 | `resources/scss/components/index.scss` 是否 `@forward` 了新元件 |
| 文字字型 404 | `vite.config` 的 `publicDir` / `server.fs.allow`、`$font-path` 路徑 |
| icon 不顯示 / 變方框 | `assets/symicon.css` 的 `@font-face` url 是否指到 `fonts/` 既有檔、codepoint 是否對 |

---

## 十、最後 — 行為準則

- **改之前先讀**：碰到一個檔案前，先把它讀完；碰到一個 class 前，先 `grep` 確認它存在。
- **不要憑記憶寫 class 名**：永遠去 `resources/scss/components/_*.scss` 確認結構。
- **小步提交**：一次 commit 一件事，CHANGELOG 寫清楚。
- **不確定就問**：寧可問人類「這個 component 應該怎麼命名」，不要自己發明。
