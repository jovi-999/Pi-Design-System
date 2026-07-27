#!/usr/bin/env bash
# ============================================================
# pm-to-blade —— host 端 vendor 複製腳本
# ------------------------------------------------------------
# 在 host（macOS，看得到 Pi DS repo + 目標專案）執行，一次把 3 樣
# 放到目標 Laravel 專案：① skill ② Pi DS resources/scss ③ symicon 字型。
# 之後進 Laradock 容器跑 setup.sh 接線即可。
#
# 用法：
#   bash vendor-copy.sh <PI_DS_REPO_PATH> <TARGET_PROJECT_PATH>
#     PI_DS_REPO_PATH     = Pi-Design-System repo 根（含 resources/scss/ fonts/ assets/）
#     TARGET_PROJECT_PATH = 目標 Laravel 專案根
#
# 特性：idempotent、不覆蓋既有目錄/檔（已存在則跳過）、fail-fast。
# ============================================================
set -euo pipefail

# skill 來源 = 本腳本所在目錄（pm-to-blade-skill）
SKILL_SRC="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

PI_SRC="${1:-}"
TARGET="${2:-}"
if [[ -z "$PI_SRC" || -z "$TARGET" ]]; then
  echo "❌ 用法：bash vendor-copy.sh <PI_DS_REPO_PATH> <TARGET_PROJECT_PATH>" >&2
  exit 1
fi
PI_SRC="${PI_SRC%/}"; TARGET="${TARGET%/}"

# 驗來源
for d in resources/scss fonts assets; do
  [[ -d "$PI_SRC/$d" ]] || { echo "❌ 來源缺 $PI_SRC/$d，Pi DS 路徑可能錯" >&2; exit 1; }
done
[[ -f "$SKILL_SRC/SKILL.md" ]] || { echo "❌ 找不到 $SKILL_SRC/SKILL.md" >&2; exit 1; }
# 驗目標（存在即可；不強制是 Laravel，容器內才驗）
[[ -d "$TARGET" ]] || { echo "❌ 目標專案不存在：$TARGET" >&2; exit 1; }

say()  { printf '\033[1;32m✔ %s\033[0m\n' "$*"; }
skip() { printf '\033[1;33m↷ %s（已存在，跳過）\033[0m\n' "$*"; }

# ---------- ① skill → .claude/skills/pm-to-blade ----------
SKILL_DST="$TARGET/.claude/skills/pm-to-blade"
if [[ -d "$SKILL_DST" ]]; then
  skip "$SKILL_DST"
else
  mkdir -p "$TARGET/.claude/skills"
  cp -R "$SKILL_SRC" "$SKILL_DST"
  say "skill → $SKILL_DST"
fi

# ---------- ② Pi DS resources/scss → resources/sass/pi-ds ----------
DS_DST="$TARGET/resources/sass/pi-ds"
if [[ -d "$DS_DST" ]]; then
  skip "$DS_DST"
else
  mkdir -p "$TARGET/resources/sass"
  cp -R "$PI_SRC/resources/scss" "$DS_DST"
  say "Pi DS resources/scss → $DS_DST"
fi

# ---------- ③ symicon 字型 → public/fonts ----------
mkdir -p "$TARGET/public/fonts"
for f in symicon-6.4s.woff2 symicon-6.4s.woff; do
  if [[ -f "$TARGET/public/fonts/$f" ]]; then
    skip "public/fonts/$f"
  elif [[ -f "$PI_SRC/fonts/$f" ]]; then
    cp "$PI_SRC/fonts/$f" "$TARGET/public/fonts/"
    say "字型 → public/fonts/$f"
  fi
done

# ---------- ③b icon glyph css（選用）----------
if [[ -f "$PI_SRC/assets/symicon.css" ]]; then
  if [[ -f "$TARGET/public/symicon.css" ]]; then
    skip "public/symicon.css"
  else
    cp "$PI_SRC/assets/symicon.css" "$TARGET/public/symicon.css"
    say "icon css → public/symicon.css（用 icon 才需）"
  fi
fi

echo
say "host 複製完成。下一步（進 Laradock 容器接線）："
cat <<MANUAL
  cd laradock
  docker-compose exec workspace bash -c "cd /var/www && bash .claude/skills/pm-to-blade/setup.sh"
  # /var/www 換成容器內你專案根路徑；省略 PI_SRC = 手動模式（resources/scss 已放好）
MANUAL
