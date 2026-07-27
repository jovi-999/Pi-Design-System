# 專案結構與修改指南（STRUCTURE.md）

> 給**人與 agent** 共用的 repo 地圖。先看樹狀圖定位檔案，再看「修改方式」決定動哪裡、會牽動什麼。
> 安裝 / 整合 / 發版細節請看 [README.md](README.md)；class 對照（給 AI）看 [docs/ai-guide.md](docs/ai-guide.md)；對話規則看 [CLAUDE.md](CLAUDE.md)。

---

## 1. 檔案樹狀圖

```text
Pi-Design-System/
├── resources/                ★ 套件資產（會隨 composer 套件出貨）
│   └── scss/                 #   真實設計系統原始碼（SCSS，單一真相源）
│       ├── index.scss        #     總入口：@forward tokens + base + components
│       ├── tokens/           #     設計 token（值的來源，禁自創）
│       │   ├── index.scss
│       │   ├── _colors.scss      #  顏色（$cl-* / --cl-* + 語意 alias $fg/$bg/$border）
│       │   ├── _typography.scss  #  字級 / 行高 / 字重 / 字體 + .fz-* class
│       │   ├── _spacing.scss     #  間距
│       │   ├── _radius.scss      #  圓角（$radius-xs…pill）
│       │   ├── _shadow.scss      #  陰影（$shadow-*）
│       │   ├── _motion.scss      #  動態（時長 / 緩動）
│       │   └── _breakpoints.scss #  RWD 斷點
│       ├── base/             #     全域基礎
│       │   ├── _reset.scss   #       CSS reset
│       │   ├── _fonts.scss   #       @font-face（對應 fonts/，路徑由 $font-path 控制）
│       │   └── _utilities.scss #     文字顏色工具 class（.text-*）
│       └── components/       #     元件：每元件一檔，class 前綴 gl_
│           ├── index.scss    #       @forward 全部元件（新增元件要在此登記）
│           ├── _button.scss  _form.scss  _checkbox.scss  _radio.scss  _toggle.scss
│           ├── _alert.scss  _callout.scss  _content-switcher.scss  _dropdown.scss
│           ├── _pagination.scss  _notification.scss  _loading.scss  _modal.scss
│           └── _border.scss  _shadow.scss  _radius.scss
├── assets/                   ★ 靜態資源
│   ├── symicon.css           #   icon 字體 @font-face + class（icon-*）+ codepoint
│   ├── icon-names.json / icon-cp-map.json / icons-preview.html
│   └── noise.svg
├── fonts/                    ★ 字型檔（woff/woff2，含 symicon-X.s icon 字型）
├── preview/                  ☆ 開發預覽頁（Vite，吃 /resources/scss/index.scss）
│   ├── index.html            #   殼層：左目錄 + 右 iframe
│   ├── color/type/shadow/tokens/icons.html        # foundation 對照頁
│   └── button/form/alert/callout/content-switcher/ # 元件對照頁（拆自舊 components）
│       dropdown/pagination/notification/loading/modal.html
├── docs/ai-guide.md          ☆ 給 AI：Figma 名稱 ↔ class 對照表
├── scripts/check-build.mjs   ☆ build 後 smoke 檢查
├── vite.config.js            ☆ 預覽用 Vite 設定
├── package.json              ☆ 預覽 / build 設定（不發布 npm）
├── README.md  CHANGELOG.md  TODO.md
├── CLAUDE.md  SKILL.md       # agent 規則
├── docs/ai-guide.md
├── .claude/skills/           # 專案 skill（如 figma-to-pi-ds）
└── dist/  dist-preview/      # build 產物（gitignore，勿手改/commit）

★ 核心可改  ☆ 開發輔助  ⚠ 動到會影響下游，需謹慎
```

---

## 2. 修改方式（要做什麼 → 動哪裡）

| 目的 | 動哪裡 | 連帶要做 / 注意 |
|---|---|---|
| 改 token（色/字/間距/圓角…） | `resources/scss/tokens/_*.scss` | 牽動所有引用該 token 的元件；**只改既有 token 值，禁自創新 token** |
| 改 / 新增元件樣式 | `resources/scss/components/_<元件>.scss` | 新元件要在 `components/index.scss` 加 `@forward`；同步 `preview/<元件>.html` 與 `docs/ai-guide.md` |
| 依 Figma 重做元件 | 同上 | 走 skill **figma-to-pi-ds** 流程（讀 Figma→映射既有 token→確認範圍→改 SCSS→同步 preview/docs→build） |
| 改文字字型 | `fonts/` + `resources/scss/base/_fonts.scss` | 詳見 README「字型管理」章節 |
| 改 icon 字型 | `fonts/symicon-X.s.*` + `assets/symicon.css` | 詳見 README「icon 字型（symicon）維護」章節 |
| 看視覺預覽 | 改完直接 `npm run dev` | 預覽吃 `resources/scss/index.scss`，HMR 即時；不用先 build |
| 產 CSS 產物 | `npm run build` / `build:min` / `build:tokens` | 產物進 `dist/`（gitignore） |

### 最高原則
- **禁自創 token / class**：不確定先 `grep resources/scss/` 或讀 `resources/scss/tokens`、`resources/scss/components` 確認，絕不憑記憶發明。
- **小範圍修改**：只動該處，不順手改其他。
- **breaking change**（改 class 命名 / 結構）：先確認，並同步所有引用處（preview、docs、README）。

---

## 3. 定位

這個 repo 是 **token 與 component 的唯一真相來源**，目前用途：**給前端切版時對照目前的元件內容與樣式**，本機 `npm run dev` 跑預覽即可。**不發布 npm**。
下游專案實際使用時，採 vendored 方式：複製需要的 `resources/scss/*.scss` / `assets/symicon.css` / `fonts/` 進自己專案，以本 repo 為唯一真相（詳見 README）。
