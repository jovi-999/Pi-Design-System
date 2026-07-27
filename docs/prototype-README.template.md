# 原型專案（Prototype）

PM 與 AI agent 討論功能 → 產出畫面與流程。所有樣式以 vendored 的 **Pi Design System** 為基準。
原型定案後，blade + scss 複製進 production 專案。

詳細產出規則見 [`CLAUDE.md`](CLAUDE.md)。

---

## 前置：把 Pi DS 複製進來（vendored）

Pi DS 不發 npm，採 vendored（複製檔案進本 repo 一起 commit）。

### 複製清單（最小集）

| 從 guideline repo | 複製到本 repo | 用途 |
|---|---|---|
| `resources/scss/`（tokens/base/components/index.scss） | `resources/pi-ds/resources/scss/` | token 與 `.gl_*` 元件（@use 來源） |
| `docs/ai-guide.md` | `resources/pi-ds/docs/ai-guide.md` | agent 的產頁規則書 |

### 選用：要 icon 顯示才加

| 從 guideline repo | 複製到本 repo | 備註 |
|---|---|---|
| `assets/symicon.css` | `resources/pi-ds/assets/symicon.css` | symicon.css 內部用 `../fonts/`，故 assets 與 fonts 要維持同層 |
| `fonts/` | `resources/pi-ds/fonts/` | symicon 字型檔 |

> 不加 icon 也能做原型：icon 用文字 / emoji 佔位即可（icon 是字型，原型階段可先忽略）。

### 指令

```bash
# 兩個 repo 並排放，依實際路徑改
GUIDE=~/path/to/Pi-Design-System
PROTO=.

mkdir -p "$PROTO/resources/pi-ds/docs"
cp -R "$GUIDE/src" "$PROTO/resources/pi-ds/"
cp "$GUIDE/docs/ai-guide.md" "$PROTO/resources/pi-ds/docs/"

# 選用：icon
cp -R "$GUIDE/assets" "$PROTO/resources/pi-ds/"   # 至少含 symicon.css
cp -R "$GUIDE/fonts"  "$PROTO/resources/pi-ds/"
```

> DS 更新不會自動同步（vendored 是死檔）。要更新就重複製一次。
> 對「原型凍結版本」反而是好事——版本天然對齊 production。

---

## 開發：看原型

原型寫 scss（`@use` DS token），需編譯才看得到畫面：

```bash
npm run dev      # 開發伺服器 + HMR，即時看原型
```

**不用 `npm run build`**——那是 production 打包（上線用）。原型階段只需 `npm run dev`。

page scss 引 DS token 範例：

```scss
@use "../pi-ds/resources/scss/tokens" as *;   // 路徑依實際擺放調整

.prototype-hero {
  padding: $sp-4;
  background: $surface;
  border-radius: $radius-md;
  color: $fg;
}
```

> 字型原型階段忽略：只 `@use` `tokens`，不 `@use` 整包 `index`（避開 `_fonts` 的 font 404 warning）。

---

## 定案 → 進 production

- 搬**你產的 blade + page scss**；DS 本體 production 端已有自己一份，不重複複製。
- 唯一手動調整：page scss 的 `@use` 路徑（原型 `../pi-ds/...` → production DS 實際位置）。class / token 名不動。
- 確認 production 的 DS 版本 = 原型複製當時的版本。
