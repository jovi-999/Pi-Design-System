# Pi Design System

這個 repo 是 **token 與 component 的唯一真相來源**，用途：

1. **前端切版對照**：本機跑預覽頁，對照元件內容與樣式。
2. **原型專案的樣式基準**：PM 與 AI agent 討論功能產出原型時，vendored 本 repo 的 `resources/scss/` 作為元件 guideline（見 [docs/prototype-README.template.md](docs/prototype-README.template.md)）。

不發布 npm；下游一律採 **vendored**（複製需要的檔案進自己專案）。

---

## 對象與導覽

本 README 給**前端 / 維護者**（設計原則、本機預覽、開發與字型管理）。其他文件分工：

| 文件 | 對象 | 內容 |
|---|---|---|
| [STRUCTURE.md](STRUCTURE.md) | 人 + agent | 檔案樹狀圖、各區用途、修改方式與注意 |
| [docs/ai-guide.md](docs/ai-guide.md) | agent | Figma 名稱 ↔ class 對照表、產頁硬規則 |
| [docs/prototype-README.template.md](docs/prototype-README.template.md) | 原型專案 | 原型 repo 的 README 範本（vendored 清單 + 流程） |
| [docs/prototype-CLAUDE.template.md](docs/prototype-CLAUDE.template.md) | 原型專案 | 原型 repo 的 CLAUDE.md 範本（agent 產頁規則） |
| [CLAUDE.md](CLAUDE.md) | agent | 對話自動載入的專案規則（禁自創 token 等） |
| [SKILL.md](SKILL.md) | agent | 結構化使用規則 |
| `.claude/skills/` | agent | 專案 skill（如 figma-to-pi-ds：Figma→Pi DS 流程） |
| [CHANGELOG.md](CHANGELOG.md) | 全體 | 版本變更記錄 |

> 想快速定位「要改什麼動哪裡」→ 直接看 **[STRUCTURE.md](STRUCTURE.md)**。

---

## 目錄

1. [字體排版](#字體排版)
2. [色彩](#色彩)
3. [圖示](#圖示)
4. [間距、圓角與層次](#間距圓角與層次)
5. [安裝與使用](#安裝與使用)
6. [開發本系統](#開發本系統)
7. [字型管理（Fonts）](#字型管理fonts)
8. [icon 字型（symicon）維護](#icon-字型symicon維護)
9. [授權 / 引用](#授權--引用)

---

## 字體排版

**主字體：** Noto Sans TC（400 / 500 / 600 / 700）。
**英數字體：** Google Sans Flex（variable，400–700 / stretch 48%–150%）。
**Display（僅數字用）：** `.ft-semicondensed` / `.ft-condensed` —— 只用於統計、價格、面試次數、薪資範圍。**永遠不要** 用 display 設定內文。
**圖示：** `symicon`（自家 icon font，172 個 glyph，見 [圖示](#圖示)）。

**Scale。** 詳見 `resources/scss/tokens/_typography.scss`、預覽 `type` 對照頁。三大家族：

- **Headline**（`fz-headline-*`，xxl→xs）：主視覺標題、區塊主標。搭配 `.fz-tit`；xl / xxl 微負字距。
- **Title**（`fz-title-*`，lg/md/sm）：卡片標題、表單標籤、行內強調。搭配 `.fz-tit`。
- **Body**（`fz-body-*`，lg/md/sm/xs）：段落、描述、metadata。預設繼承全域 500，行高寬鬆（1.7–1.8）。

舊有的 `fz-h1`/`fz-s1`/`fz-t1` 別名為了漸進遷移保留 —— 新功能請用語意化命名。

---

## 色彩

**色階結構。** 每個色相都有 50→900 的色階。500 是品牌／語意主色；400 是淺色背景上的 hover/active；600 是深色背景上的 hover。50 與 100 用於塊狀背景（badge、callout）。**永遠不要** 自創中間值（例如 550）—— 需要的話用 500 配 alpha overlay，或改用語意 alias。

---

## 圖示

**字體家族。** `symicon-fill` —— 172 個 glyph、單一線重的 filled 風格。幾何形狀對齊 24×24 畫板、2px 視覺筆畫；每個 icon 視覺量約佔 18×18。整套刻意只服務本產品族群：求職動詞為主（`interview-logo`、`interview-luckybag`、`allowance-book`、`atm`、`receipt`、`factory`），不收一般通用 dev / file 圖示。

**用法。**

```html
<!-- 偏好寫法 -->
<i class="icon icon-search" aria-hidden="true"></i>

<!-- 純 icon 按鈕一定要加 aria-label -->
<button aria-label="搜尋"><i class="icon icon-search" aria-hidden="true"></i></button>
```

**顏色。** Icon 透過 `currentColor` 繼承。**永遠不要** 在 icon 上寫死 hex —— 跟父層文字色繼承才會跟著主題切換。

完整 class 清單見 `assets/symicon.css`，視覺索引見 `preview/icons.html`。

---

## 間距、圓角與層次

**Space scale**（`--sp-*` / `$sp-*`）：4、8、12、16、20、24、32、40、48、64。

**圓角**（`--radius-*` / `$radius-*`）：

- `--radius-xs` : 4px
- `--radius-sm` : 8px
- `--radius-md` : 12px
- `--radius-lg` : 16px
- `--radius-xl` : 24px
- `--radius-xxl` : 32px
- `--radius-pill` : 999px

---

## 安裝與使用

前端切版時用法：clone 下來，本機跑預覽頁，對照元件樣式與 class 名稱即可。

### 1. 啟動預覽

```bash
git clone <repo-url>
cd Pi-Design-System
npm install            # 只裝 sass / vite 等 build 工具
npm run dev            # 啟動預覽，開瀏覽器看左側目錄 + 各元件對照頁
```

預覽頁吃 `resources/scss/index.scss`，改 SCSS 後 HMR 即時更新，不用先 build。

### 2. 切版怎麼對照

- **看元件樣式**：`npm run dev` 後，左側目錄點各元件（button / form / alert…），右側 iframe 即是該元件實際樣式。
- **查 class 名稱**：class 前綴 `gl_`，真相在 `resources/scss/components/_<元件>.scss`；Figma 名稱 ↔ class 對照見 [docs/ai-guide.md](docs/ai-guide.md)。
- **查 token / 色票 / 圖示**：foundation 對照頁（color / type / shadow / tokens / icons）同樣在預覽左側目錄。
- **禁自創 token / class**：切版只能用設計系統已存在的 token / class，不確定先 `grep resources/scss/` 或讀 `resources/scss/tokens`、`resources/scss/components` 確認。

### 3. 給原型專案使用（vendored）

PM + AI agent 的原型專案把本 repo 的 `resources/scss/` 與 `docs/ai-guide.md` 複製（vendored）進去，agent 依 guideline 產出畫面。完整複製清單、指令與規則範本：

- [docs/prototype-README.template.md](docs/prototype-README.template.md) —— 原型 repo 的 README 範本
- [docs/prototype-CLAUDE.template.md](docs/prototype-CLAUDE.template.md) —— 原型 repo 的 CLAUDE.md 範本

---

## 開發本系統

Repo 結構與「要改什麼動哪裡」見 **[STRUCTURE.md](STRUCTURE.md)**（單一真相，此處不重複）。

### 日常開發

改 SCSS（顏色、間距、單一元件樣式）：

```bash
# 1. 開預覽頁即時看視覺（HMR，不用 build）
npm run dev

# 2. 改完跑一次 build + smoke test，確認沒語法錯
npm run build
npm test                  # 檢查產出 css 含關鍵 token / class

# 3. 寫 CHANGELOG → commit → push
```

### 編輯規則

- 只改 `resources/scss/` 內檔案；CSS 一律由 `npm run build` 從 SCSS 產出，不手寫 CSS。
- 加新元件 → `resources/scss/components/_xxx.scss` + `resources/scss/components/index.scss` 加一行 `@forward` + `preview/` 加對照頁並在 `preview/index.html` 左目錄登記。
- 改 token → 先看 `preview/tokens.html` 評估衝擊面，CHANGELOG 寫清楚。
- **禁自創 token / class**：不確定先 `grep resources/scss/` 或讀 `resources/scss/tokens`、`resources/scss/components` 確認，絕不憑記憶發明。
- rename / 移除 token、改 class 名屬 breaking change → CHANGELOG 寫清楚並同步所有引用處（preview、docs、README）。

---

## 字型管理（Fonts）

字型是 design system 裡最容易出問題的部分 —— **檔案在哪、SCSS 怎麼引用、路徑怎麼解析** 一旦不一致就會 404 / FOUT / build 失敗。這節是唯一的真相來源，所有字型相關的決策都從這裡開始。

### 架構

```
fonts/                     ← 字型原始檔（woff / woff2）
resources/scss/base/_fonts.scss       ← @font-face 宣告，路徑由 $font-path 控制
resources/scss/tokens/_typography.scss ← --font-sans / --font-display CSS variable
```

`_fonts.scss` 用 `$font-path !default` 留了**覆寫鉤子**：

```scss
$font-path: "../../fonts" !default;

@font-face {
  font-family: "symicon";
  src: url("#{$font-path}/symicon-6.4s.woff2") format("woff2"),
       url("#{$font-path}/symicon-6.4s.woff") format("woff");
}
```

預設 `"/fonts"`（web root 起算的絕對路徑）。預覽頁與下游專案（字型放 `public/fonts/`）都吃這個預設值即可，不用改；只有字型放 CDN 時才需覆寫 `$font-path`。

> 為什麼用絕對路徑：Sass 不會 rebase `url()`，會原樣輸出；Vite 是相對於「entry CSS 檔的目錄」去解，不是相對於 `_fonts.scss` 這支 partial。因此任何相對路徑都只能對其中一個消費端正確（`sass` CLI 輸出到 `dist/`、Vite entry 在 `resources/scss/`、專案端 entry 在 `resources/sass/`，三者位置各不相同）。

### 換 / 升級字型怎麼做

| 情況 | 動哪裡 |
|---|---|
| 字型版本升級（family / 檔名不變，只換內容） | 把新檔覆蓋進 `fonts/`，`npm run dev` 確認還能載 |
| 加新字型檔（例如升級 symicon 版本） | `fonts/` 加檔 → 更新對應 `@font-face` 的檔名與版本號 |
| 替換字型（改 family / token） | `fonts/` 換檔 → 改 `resources/scss/base/_fonts.scss` 的 `@font-face` → 改 `resources/scss/tokens/_typography.scss` 的 `--font-sans` → `preview/` 對照視覺有無走鐘（中文寬度可能不同）|

改完一律 `npm run dev` 在預覽頁確認字型載入（DevTools Network 看字型 200），並寫 CHANGELOG。

### 給 AI agent 的提醒

- ❌ **不要** 在 component 檔裡寫 `@import url('https://fonts.googleapis.com/...')`
- ❌ **不要** 在 component 檔裡再加 `@font-face`
- ✅ 字型只在 `resources/scss/base/_fonts.scss` 一個地方宣告
- ✅ 引用字型一律用 `$font-sans` / `$font-display` / `$font-icon` 變數，不要寫死 `font-family: "symicon"`

---

## icon 字型（symicon）維護

icon 字型跟文字字型機制不同：它**不走 `resources/scss/base/_fonts.scss`**，而是獨立在 `assets/symicon.css` —— 一個檔同時管 `@font-face` 與每個 `.icon-*` 的 codepoint（`content: "\eXXX"`）。版本號直接寫進**檔名**（`symicon-6.4s.woff2`）當作 cache-bust，升級時連檔名一起換。

```
fonts/symicon-6.4s.woff2 / .woff   ← icon 字型本體（檔名帶版本號）
assets/symicon.css                 ← @font-face（url 指向 ../fonts/）+ .icon-* codepoint
assets/icon-cp-map.json            ← icon 名稱 ↔ codepoint 對應
assets/icon-names.json             ← icon 名稱清單
assets/icons-preview.html          ← glyph 視覺索引
```

> ⚠️ **codepoint 對應**：icon 字型工具（IcoMoon / Fontello）匯出時附 `selection.json`（每個 icon 的 codepoint）。**glyph 順序一變，所有 `content: "\eXXX"` 都要重新對應** —— 升級前務必拿到對照表確認。

### 情境 1：在本預覽專案升級 icon 字型

本 repo 用 Vite 預覽。`assets/` 與 `fonts/` 是靜態檔，Vite 直接服務，**不用改 `vite.config`**。

```bash
# 1. 放新字型檔進 fonts/，檔名帶新版本號（如 6.4s → 7.0s），舊檔先留著
cp ~/Downloads/symicon-7.0s.woff2 fonts/
cp ~/Downloads/symicon-7.0s.woff  fonts/

# 2. 改 assets/symicon.css：
#    - 檔頭註解版本號 symicon-6.4s → symicon-7.0s
#    - @font-face 的 src url：../fonts/symicon-7.0s.woff2 / .woff
#    - 若 glyph 順序變了 → 依新 selection.json 重新對應所有 .icon-* 的 content
#      （連帶更新 assets/icon-cp-map.json、icon-names.json、icons-preview.html）

# 3. 啟動預覽驗證
npm run dev
#    開 icons 對照頁 / 各元件頁，確認 icon 正常顯示
#    重點檢查有用到 icon 的元件：form 的 valid/invalid、loading、alert、callout

# 4. 全部 OK → 刪舊 symicon-6.4s.*，寫 CHANGELOG
```

### 情境 2：在其他專案（Vite）實際使用

下游專案不 link 整包，而是**把 icon 字型檔與 class 表 vendored 進自己專案**。典型 Vite 專案做法：

**a. 放字型檔**：把 `fonts/symicon-X.woff2 / .woff` copy 到該專案 `public/fonts/`（Vite 對 `public/` 靜態服務，不經打包）。

**b. 宣告 `@font-face`**（該專案某支 scss，url 用絕對路徑指向 `public/`）：

```scss
@font-face {
  font-family: "symicon";
  src: url("/fonts/symicon-X.woff2") format("woff2"),
       url("/fonts/symicon-X.woff")  format("woff");
  font-display: swap;
}
```

**c. icon class 表**：把 `assets/symicon.css` 的 `.icon` / `.icon-*` 規則 vendored 進該專案（或只挑用到的 glyph），class 名與 codepoint 以本 repo 的 `symicon.css` 為唯一真相。

**d. （建議）preload 首屏會用到的 icon 字型**，降 FOUT：

```html
<link rel="preload" href="/fonts/symicon-X.woff2" as="font" type="font/woff2" crossorigin>
```

**e. 該專案升級 icon 版本流程**：

```
1. 新 symicon-N.woff2/woff 放 public/fonts/（新版本號檔名，舊檔先留）
2. 改 @font-face 的 src url → 新檔名
3. 改 preload href → 新檔名
4. 若 glyph 順序變 → 依新 selection.json 重對 content codepoint
   ↳ 連動檢查寫死 codepoint 的地方：表單 valid/invalid feedback icon、
     loading 動畫 icon、layout icon
5. npm run build / dev 驗證 → icon 全正常 → 刪舊檔
```

> 兩種情境共通鐵則：**版本號寫進檔名**（cache-bust）、**舊檔驗證通過才刪**、**glyph 順序變動務必重對 codepoint**。

---

## 授權 / 引用

- **Noto Sans TC** —— SIL Open Font License 1.1，© Google。
- **Google Sans Flex** —— SIL Open Font License 1.1，© Google。
- **symicon-fill** —— 內部資產，© Pi。
