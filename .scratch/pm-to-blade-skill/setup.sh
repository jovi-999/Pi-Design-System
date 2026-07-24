#!/usr/bin/env bash
# ============================================================
# pm-to-blade —— 新 Laravel 專案落地腳本
# ------------------------------------------------------------
# 在「新 Laravel 專案根目錄」執行，把 Pi DS vendored 進來 +
# 建好 prototype scaffold。需 JS/scss 手改的部分只印提示、不亂改。
#
# 用法：
#   (A) 讓腳本自己 vendor（host、看得到 Pi DS repo 時）：
#       bash .claude/skills/pm-to-blade/setup.sh <PI_DS_REPO_PATH>
#   (B) 已手動把 src 放到 resources/sass/pi-ds（如 Laradock 容器內看不到 Pi DS repo）：
#       bash .claude/skills/pm-to-blade/setup.sh
#       → PI_SRC 省略；腳本跳過複製，只做接線。此模式字型需自行放 public/fonts。
#
# 特性：idempotent（可重跑）、不覆蓋既有檔（只補缺的）、fail-fast。
# ============================================================
set -euo pipefail

# ---------- 參數 ----------
PI_SRC="${1:-}"
if [[ -n "$PI_SRC" ]]; then
  PI_SRC="${PI_SRC%/}"                     # 去尾斜線
  for d in src fonts assets; do
    [[ -d "$PI_SRC/$d" ]] || { echo "❌ $PI_SRC/$d 不存在，路徑可能錯" >&2; exit 1; }
  done
elif [[ ! -d resources/sass/pi-ds ]]; then
  echo "❌ 未給 PI_DS_REPO_PATH，且 resources/sass/pi-ds 也不存在" >&2
  echo "   → 給 Pi DS repo 路徑，或先手動把 src 複製成 resources/sass/pi-ds" >&2
  exit 1
fi

# ---------- 確認在 Laravel 專案根 ----------
if [[ ! -f artisan || ! -d resources ]]; then
  echo "❌ 目前目錄看起來不是 Laravel 專案根（缺 artisan / resources/）" >&2
  echo "   請 cd 到新專案根目錄再跑" >&2
  exit 1
fi

say()  { printf '\033[1;32m✔ %s\033[0m\n' "$*"; }
skip() { printf '\033[1;33m↷ %s（已存在，跳過）\033[0m\n' "$*"; }
note() { printf '\033[1;36m→ %s\033[0m\n' "$*"; }

# ---------- ① vendor Pi DS src ----------
if [[ -d resources/sass/pi-ds ]]; then
  skip "resources/sass/pi-ds"
else
  mkdir -p resources/sass
  cp -R "$PI_SRC/src" resources/sass/pi-ds
  say "vendored Pi DS → resources/sass/pi-ds/"
fi

# ---------- ② 字型（只 symicon 是本地檔；其餘走 CDN）----------
mkdir -p public/fonts
for f in symicon-6.4s.woff2 symicon-6.4s.woff; do
  if [[ -f "public/fonts/$f" ]]; then
    skip "public/fonts/$f"
  elif [[ -n "$PI_SRC" && -f "$PI_SRC/fonts/$f" ]]; then
    cp "$PI_SRC/fonts/$f" public/fonts/
    say "字型 → public/fonts/$f"
  else
    note "public/fonts/$f 缺 —— 手動模式請自行從 Pi DS fonts/ 放進來（不用 icon 可忽略）"
  fi
done

# ---------- ③ icon glyph css（用到 icon 才需，選用）----------
if [[ -n "$PI_SRC" && -f "$PI_SRC/assets/symicon.css" ]]; then
  if [[ -f public/symicon.css ]]; then
    skip "public/symicon.css"
  else
    cp "$PI_SRC/assets/symicon.css" public/symicon.css
    say "icon css → public/symicon.css（用 icon 才需，layout <link> 引入）"
  fi
fi

# ---------- ④ app.scss ----------
APP_SCSS="resources/sass/app.scss"
if [[ -f "$APP_SCSS" ]]; then
  skip "$APP_SCSS"
  if ! grep -q "pi-ds/index" "$APP_SCSS"; then
    note "手動加到 $APP_SCSS 最上方（順序重要，font-path 覆寫要在 index 之前）："
    cat <<'SNIP'
    ----------------------------------------------------------------
    @use 'pi-ds/base/fonts' with ($font-path: '/fonts');  // symicon → public/fonts
    @use 'pi-ds/index' as *;                              // tokens + base + components
    // 各 prototype 組合件：
    // @use 'prototypes/signup';
    ----------------------------------------------------------------
SNIP
  fi
else
  cat > "$APP_SCSS" <<'SCSS'
// Pi DS vendored 入口（pm-to-blade setup 產生）
@use 'pi-ds/base/fonts' with ($font-path: '/fonts');  // symicon → public/fonts
@use 'pi-ds/index' as *;                              // tokens + base + components

// 各 prototype 組合件樣式在此逐一 @use（setup 建立 prototypes/ 目錄）：
// @use 'prototypes/signup';
SCSS
  say "建 $APP_SCSS（含 font-path 覆寫）"
fi

# ---------- ⑤ prototype scaffold ----------
mkdir -p resources/sass/prototypes
[[ -f resources/sass/prototypes/.gitkeep ]] || : > resources/sass/prototypes/.gitkeep

LAYOUT="resources/views/prototypes/layout.blade.php"
if [[ -f "$LAYOUT" ]]; then
  skip "$LAYOUT"
else
  mkdir -p resources/views/prototypes
  cat > "$LAYOUT" <<'BLADE'
<!doctype html>
<html lang="zh-Hant">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Prototype — @yield('title', 'Prototype')</title>
  {{-- 用 icon 才需： <link rel="stylesheet" href="{{ asset('symicon.css') }}"> --}}
  @vite(['resources/sass/app.scss'])
</head>
<body>
  @yield('content')
</body>
</html>
BLADE
  say "建 prototype layout → $LAYOUT"
fi

# ---------- ⑥ sass 相依 ----------
if [[ -d node_modules/sass ]]; then
  skip "npm sass"
else
  note "安裝 sass（dev）…"
  npm i -D sass
  say "sass 已安裝"
fi

# ---------- 手動收尾（不自動改，避免破壞既有設定）----------
echo
note "======== 剩下 2 步手動確認（結構因專案而異）========"
cat <<'MANUAL'
1) vite.config.js 的 laravel({ input: [...] }) 要含 'resources/sass/app.scss'：
     laravel({ input: ['resources/sass/app.scss', 'resources/js/app.js'], refresh: true })

2) routes/web.php 加 prototype route（白名單式，勿直接吃 request 當 view 名）：
     Route::get('/prototypes/{name}', function (string $name) {
         abort_unless(view()->exists("prototypes.$name"), 404);
         return view("prototypes.$name");
     })->where('name', '[a-z0-9-]+');

完成後：  npm run dev  +  php artisan serve
觸發 pm-to-blade skill，產物開 http://127.0.0.1:8000/prototypes/<name>
MANUAL
say "setup 完成"
