#!/usr/bin/env bash
# ============================================================
# pm-to-blade —— 容器內（Laradock workspace）接線腳本
# ------------------------------------------------------------
# 在專案根（容器內，如 /var/www）執行。本專案是 Vite + 一頁一支 scss，
# 且 sass 已具備 —— 故此腳本很輕：只驗 vendored Pi DS、建 prototypes
# 目錄、印慣例提示。**不碰 app.scss、不裝 sass、不建共用 layout。**
#
# 用法：
#   bash .claude/skills/pm-to-blade/setup.sh
#   （src 需已在 resources/sass/pi-ds；host 端用 vendor-copy.sh 放好）
# ============================================================
set -euo pipefail

if [[ ! -d resources ]]; then
  echo "❌ 找不到 resources/（請 cd 到 Laravel 專案根再跑）" >&2
  exit 1
fi
if [[ ! -d resources/sass/pi-ds ]]; then
  echo "❌ resources/sass/pi-ds 不存在 —— 先在 host 端跑 vendor-copy.sh 放 Pi DS src" >&2
  exit 1
fi

say()  { printf '\033[1;32m✔ %s\033[0m\n' "$*"; }
note() { printf '\033[1;36m→ %s\033[0m\n' "$*"; }

say "vendored Pi DS 已就位：resources/sass/pi-ds/"

# prototype blade 目錄
mkdir -p resources/views/prototypes
say "prototype view 目錄：resources/views/prototypes/"

# sass loadPaths 提醒（決定 @use 'pi-ds/index' 解不解得到）
if grep -rqE "loadPaths.*resources/sass" vite.config.* 2>/dev/null; then
  say "vite sass loadPaths 含 resources/sass → @use 'pi-ds/index' 可直接用"
else
  note "vite.config.* 未見 loadPaths: ['resources/sass'] —— preview scss 改用相對路徑 @use，或補此 loadPath"
fi

echo
note "======== 每個 prototype 由 skill 依 PM 指定名 preview-<name> 產出 ========"
cat <<'MANUAL'
skill 產物（三件，同名）：
  resources/sass/preview-<name>.scss              @use 'pi-ds/index' as *; + 組合件樣式
  resources/views/prototypes/preview-<name>.blade.php   @vite(['resources/sass/preview-<name>.scss'])

每產一支需：
  1) vite.config.* 的 input 陣列加 'resources/sass/preview-<name>.scss'（改完重啟 npm run watch）
  2) routes/web.php 有 prototype route（一次性；白名單式）：
       Route::get('/prototypes/{name}', function (string $name) {
           abort_unless(view()->exists("prototypes.$name"), 404);
           return view("prototypes.$name");
       })->where('name', 'preview-[a-z0-9-]+');

跑：Laradock 內  npm run watch  →  開 http://<host>/prototypes/preview-<name>
MANUAL
say "setup 完成（輕量：未動 app.scss / 未裝 sass）"
