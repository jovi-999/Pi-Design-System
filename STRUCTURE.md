# 專案結構與修改指南（STRUCTURE.md）

> 給**人與 agent** 共用的 repo 地圖。先看樹狀圖定位檔案，再看「修改方式」決定動哪裡、會牽動什麼。
> 安裝 / 整合 / 發版細節請看 [README.md](README.md)；class 對照（給 AI）看 [docs/ai-guide.md](docs/ai-guide.md)；對話規則看 [CLAUDE.md](CLAUDE.md)。

---

## 1. 檔案樹狀圖

```text
Pi-Design-System/
├── resources/                ★ 套件資產（會隨 composer 套件出貨）
│   └── scss/                 #   真實設計系統原始碼（SCSS，單一真相源）
│       ├── index.scss        #     ★ 既有專案的入口：tokens + base + 元件契約 + components（不含 reset）
│       ├── reset.scss        #     ☆ 頁面級 reset，獨立檔，既有專案不要載（見檔內說明）
│       ├── preview-all.scss  #     ☆ preview 專用入口 = reset + index
│       ├── _layers.scss      #     ⚠ @layer 順序宣告（pi-reset < pi），必須最先載入
│       ├── _component-base.scss #  ⚠ 元件契約：corner-shape / font-weight（標準 reset 不提供）
│       ├── tokens/           #     設計 token（值的來源，禁自創）
│       │   ├── index.scss
│       │   ├── _colors.scss      #  顏色（$cl-* / --cl-* + 語意 alias $fg/$bg/$border）
│       │   ├── _typography.scss  #  字級 / 行高 / 字重 / 字體 + .fz-* class
│       │   ├── _spacing.scss     #  間距
│       │   ├── _radius.scss      #  圓角（$radius-xs…pill）
│       │   ├── _shadow.scss      #  陰影（$shadow-*）
│       │   ├── _motion.scss      #  動態（時長 / 緩動）
│       │   └── _breakpoints.scss #  RWD 斷點
│       ├── base/             #     全域基礎（reset 已移到上層獨立檔）
│       │   ├── _fonts.scss   #       @font-face（對應 fonts/，路徑由 $font-path 控制）
│       │   └── _utilities.scss #     文字顏色工具 class（.text-*）
│       └── components/       #     元件：每元件一檔，class 前綴 gl_
│           ├── index.scss    #       @forward 全部元件（新增元件要在此登記）
│           ├── _button.scss  _form.scss  _checkbox.scss  _radio.scss  _toggle.scss
│           ├── _alert.scss  _callout.scss  _content-switcher.scss  _dropdown.scss
│           ├── _pagination.scss  _notification.scss  _loading.scss  _modal.scss
│           └── _border.scss  _shadow.scss  _radius.scss
├── src/                      ★ PSR-4 PHP（`PiTw\PiDesignSystem\`，會出貨）
│   ├── PiDesignSystemServiceProvider.php  # Blade 命名空間 pi + 兩個 directive
│   └── Prototype/            #   prototype 流程用，專案端不執行但仍出貨
│       ├── FixtureLoader.php #     @piFixture 的實作
│       ├── Manifest.php      #     @piFragment manifest 解析（preview 與 CI 共用）
│       └── StyleBudget.php   #     ⚠ 30 行門檻的唯一計算來源（preview 與 CI 共用）
├── assets/                   ★ 靜態資源
│   ├── symicon.css           #   icon 字體 @font-face + class（icon-*）+ codepoint
│   └── icon-names.json / icon-cp-map.json  # icon 清單與 codepoint（preview 的
│                              #   /foundation/icons 掃這兩支生成）
├── fonts/                    ★ 字型檔（woff/woff2，含 symicon-X.s icon 字型）
├── preview/                  ☆ 預覽用 Laravel app（開發工具，不出貨）
│   ├── Dockerfile  docker-compose.yml  # PHP 在容器（8100）；Vite 跑在 host（5178）
│   ├── composer.json         #   path repository symlink 到 repo 根，裝套件本體
│   ├── app/Support/          #   TokenCatalog / IconCatalog / ComponentCatalog
│   │                         #   / PrototypeCatalog —— 三區清單全部掃檔生成
│   ├── resources/views/foundation|gallery|prototypes/  # 三區的頁面
│   ├── resources/scss/app.scss   # @use ../../../resources/scss/preview-all
│   ├── resources/scss/_chrome.scss  # preview 自己的版面（前綴 pv-，不出貨）
│   └── README.md             #   起動方式與設計取捨（先讀這支）
├── prototypes/               ☆ PM & AI 的討論區（不出貨）
│   └── <project>/{pages,fragments,fixtures,_hosts}/
├── docs/ai-guide.md          ☆ 給 AI：Figma 名稱 ↔ class 對照表
├── docs/prototype-flow.md    ☆ PM 需求 → preview → 交接的流程圖
├── scripts/check-build.mjs   ☆ build 後 smoke 檢查
├── scripts/sync-component-list.php  ☆ meta → CLAUDE.md 元件清單（--check 給 CI）
├── scripts/check-prototypes.php  ☆ prototype 的 CI 檢查（樣式門檻 + manifest）
├── scripts/fetch-host.php  scripts/apply.php  ☆ fragment 的宿主快照與交接
├── package.json              ☆ 只剩 SCSS build / lint（不發布 npm）
├── .nvmrc                    ☆ Node 版本（24 = 現行 LTS）。`cd` 進來後 `nvm use`
│                             #  preview/ 沒有自己的 .nvmrc —— nvm 會往上找到這支
├── README.md  CHANGELOG.md  TODO.md
├── CLAUDE.md  SKILL.md       # agent 規則
├── docs/ai-guide.md
├── .github/workflows/ci.yml  ☆ CI 四個 gate（SCSS / 元件清單 / prototype / composer）
├── .claude/skills/           # 專案 skill（如 figma-to-pi-ds）
└── dist/                     # SCSS build 產物（gitignore，勿手改/commit）

★ 核心可改  ☆ 開發輔助  ⚠ 動到會影響下游，需謹慎
```

---

## 2. 修改方式（要做什麼 → 動哪裡）

| 目的 | 動哪裡 | 連帶要做 / 注意 |
|---|---|---|
| 改 token（色/字/間距/圓角…） | `resources/scss/tokens/_*.scss` | 牽動所有引用該 token 的元件；**只改既有 token 值，禁自創新 token** |
| 改 / 新增元件樣式 | `resources/scss/components/_<元件>.scss` | **規則區必須包在 `@layer pi { }` 內**（見下方「分層規則」）；新元件要在 `components/index.scss` 加 `@forward`；同步 `resources/views/components/<元件>.blade.php` 與 `.meta.php`、`docs/ai-guide.md` |
| 依 Figma 重做元件 | 同上 | 走 skill **figma-to-pi-ds** 流程（讀 Figma→映射既有 token→確認範圍→改 SCSS→同步 blade 元件與 docs→build） |
| 改文字字型 | `fonts/` + `resources/scss/base/_fonts.scss` | 詳見 README「字型管理」章節 |
| 改 icon 字型 | `fonts/symicon-X.s.*` + `assets/symicon.css` | 該檔內容整包在 `@layer pi { }` 內，新增規則要放進去；詳見 README「icon 字型（symicon）維護」章節 |
| 看視覺預覽 | `cd preview && docker compose up -d && npm run dev` → http://localhost:8100 | 預覽吃 `resources/scss/preview-all.scss`（= reset + index），HMR 即時；不用先 build |
| 產 CSS 產物 | `npm run build` / `build:min` / `build:tokens` | 產物進 `dist/`（gitignore） |

### 分層規則（`@layer`）

三層優先權，後者勝：

| 層 | 內容 | 誰寫的 |
|---|---|---|
| ① `pi-reset` | `reset.scss` —— html/body、h1..h6、p、code 等頁面級意見 | Pi DS |
| ② `pi` | tokens 的 `:root`、`.fz-*` / `.text-*`、元件契約、`.gl_*` 元件、`assets/symicon.css` | Pi DS |
| ③ 未分層 | 專案自己的既有樣式 | 下游專案 |

**未分層樣式的優先權高於任何分層樣式**，所以專案既有 code 一行都不用改，衝突時專案永遠勝出，也不必煩惱 import 順序。

改 SCSS 時三件事要記住：

1. **元件規則一律包在 `@layer pi { }` 內。** 漏了就變成未分層，反而蓋掉專案樣式 —— 隔離失效。
2. **`@mixin` / `@function` 定義必須放在 `@layer` 之外。** Sass 的 mixin 有區塊作用域，寫在 `@layer` 內不會被 `@forward` 匯出，其他檔案 `@include` 會得到 `Undefined mixin`。
3. **不要用 `@extend`，用 `@include`。** `@extend` 會把宣告輸出在「被 extend 的原始規則位置」，若那個位置在 `@layer` 之外，樣式就逃出 layer 了。

### 元件契約 vs reset

`resources/scss/reset.scss` 與 `_component-base.scss` 的分工：

- **`reset.scss`（頁面級意見）**：各專案本來就有自己的一套（normalize.css / Tailwind preflight / Bootstrap reboot），**既有專案不要載**。不在 `index.scss` 內。
- **`_component-base.scss`（元件契約）**：`corner-shape` 與 `font-weight: 500` —— 沒有任何標準 reset 會提供這兩項，少了元件就長歪（圓角變正圓、字重掉回 400）。跟著元件出貨，永遠生效。

已用瀏覽器實測：不載 reset 時，元件的 `corner-shape` / `border-radius` / `font-weight` / 顏色**零差異**；唯一依賴 reset 的是 `box-sizing: border-box`（各專案的 reset 都有提供）。

### 最高原則
- **禁自創 token / class**：不確定先 `grep resources/scss/` 或讀 `resources/scss/tokens`、`resources/scss/components` 確認，絕不憑記憶發明。
- **小範圍修改**：只動該處，不順手改其他。
- **breaking change**（改 class 命名 / 結構）：先確認，並同步所有引用處（preview、docs、README）。

---

## 3. 定位

這個 repo 是 **token 與 component 的唯一真相來源**，出貨 SCSS 原始碼 + Blade 元件兩層。本機起 `preview/` 跑預覽（見 [preview/README.md](preview/README.md)）。**不發布 npm**（Node 只用於本 repo 的 SCSS build 與 preview）。

專案端透過 **Composer 版本化依賴**取得：`composer require pi-tw/pi-design-system`。
**不採 vendored（複製檔案進專案）** —— 那條路無法回答「專案 B 現在用哪一版」，理由見
[design-guideline-spec.md](design-guideline-spec.md) 的 **D1**。安裝步驟詳見 README。
