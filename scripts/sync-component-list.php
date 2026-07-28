<?php

/**
 * 掃 resources/views/components/*.meta.php，把元件清單寫回 CLAUDE.md 的
 * <!-- COMPONENTS:START --> / <!-- COMPONENTS:END --> 區段之間。
 *
 * 為什麼要有這支：手寫的元件列表三個月後一定跟實際元件對不上，然後 agent
 * 開始產出不存在的元件。原則手寫、清單生成。
 *
 * 用法：
 *   php scripts/sync-component-list.php          # 寫入
 *   php scripts/sync-component-list.php --check  # 只檢查是否已同步（CI 用，不一致回 exit 1）
 *
 * 這支刻意不依賴 Laravel —— 用 host 的 php 或容器內的 php 都能跑。
 */

declare(strict_types=1);

const START_MARKER = '<!-- COMPONENTS:START -->';
const END_MARKER = '<!-- COMPONENTS:END -->';

$root = dirname(__DIR__);
$componentsDir = $root . '/resources/views/components';
$targetFile = $root . '/CLAUDE.md';

$checkOnly = in_array('--check', $argv, true);

// ---------- 1. 讀所有 meta ----------

$metaFiles = glob($componentsDir . '/*.meta.php');
sort($metaFiles);

if ($metaFiles === false || $metaFiles === []) {
    fwrite(STDERR, "找不到任何 *.meta.php，路徑：{$componentsDir}\n");
    exit(1);
}

$rows = [];
$warnings = [];

foreach ($metaFiles as $file) {
    $slug = basename($file, '.meta.php');
    $meta = require $file;

    // blade 檔不存在＝有 meta 沒元件，要出聲而不是安靜產出一個不能用的清單項
    if (! is_file($componentsDir . "/{$slug}.blade.php")) {
        $warnings[] = "{$slug}.meta.php 存在但找不到 {$slug}.blade.php";
    }

    $rows[] = [
        'tag' => "x-pi::{$slug}",
        'name' => $meta['name'] ?? $slug,
        'description' => $meta['description'] ?? '',
        // notes 裡以 ⚠️ 開頭的是元件缺口，要在清單裡讓 agent 直接看到
        'gaps' => array_values(array_filter(
            $meta['notes'] ?? [],
            fn (string $note) => str_starts_with($note, '⚠️')
        )),
    ];
}

// ---------- 2. 組 markdown ----------

$lines = [];
$lines[] = '';
$lines[] = '> 本區段由 `scripts/sync-component-list.php` 自動生成，請勿手動修改。';
$lines[] = '> 資料來源：`resources/views/components/*.meta.php`';
$lines[] = '';
$lines[] = sprintf('共 %d 支元件。各元件可用的 prop 值請看 preview 的元件頁（`/components/<name>`）或對應的 `.meta.php`。', count($rows));
$lines[] = '';
$lines[] = '| Tag | 名稱 | 說明 |';
$lines[] = '|---|---|---|';

foreach ($rows as $row) {
    $lines[] = sprintf(
        '| `%s` | %s | %s |',
        $row['tag'],
        $row['name'],
        str_replace('|', '\\|', $row['description'])
    );
}

// 缺口另立一段：這是「不要自己刻」的清單，比藏在表格裡有效
$gaps = [];
foreach ($rows as $row) {
    foreach ($row['gaps'] as $gap) {
        $gaps[] = sprintf('- `%s` — %s', $row['tag'], ltrim($gap, '⚠️ '));
    }
}

if ($gaps !== []) {
    $lines[] = '';
    $lines[] = '### 已知元件缺口（不要自己刻，走缺口流程提報）';
    $lines[] = '';
    $lines = array_merge($lines, $gaps);
}

$lines[] = '';

$generated = implode("\n", $lines);

// ---------- 3. 寫回 / 檢查 ----------

$original = file_get_contents($targetFile);

if ($original === false) {
    fwrite(STDERR, "讀不到 {$targetFile}\n");
    exit(1);
}

$startPos = strpos($original, START_MARKER);
$endPos = strpos($original, END_MARKER);

if ($startPos === false || $endPos === false || $endPos < $startPos) {
    fwrite(STDERR, "在 {$targetFile} 找不到成對的 COMPONENTS 標記\n");
    exit(1);
}

$updated = substr($original, 0, $startPos + strlen(START_MARKER))
    . $generated
    . substr($original, $endPos);

foreach ($warnings as $warning) {
    fwrite(STDERR, "⚠️  {$warning}\n");
}

if ($checkOnly) {
    if ($updated === $original) {
        echo "CLAUDE.md 的元件清單已同步（" . count($rows) . " 支）\n";
        exit(0);
    }

    fwrite(STDERR, "CLAUDE.md 的元件清單與 *.meta.php 不一致。請執行：php scripts/sync-component-list.php\n");
    exit(1);
}

if ($updated === $original) {
    echo "無變動（" . count($rows) . " 支元件）\n";
    exit(0);
}

file_put_contents($targetFile, $updated);
echo "已更新 CLAUDE.md 的元件清單（" . count($rows) . " 支元件";
echo $gaps === [] ? "" : "、" . count($gaps) . " 個缺口";
echo "）\n";
