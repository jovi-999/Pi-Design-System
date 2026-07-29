<?php

/**
 * Prototype 的 CI 檢查。
 *
 * 兩項：
 *   1. page-scoped 樣式用量 ≤ 30（CLAUDE.md 第 3 條）
 *      —— `<style>` 區塊行數 + inline `style="…"` 的宣告數
 *   2. fragment 必須宣告完整的 `@piFragment` manifest（target / slot / host）
 *      —— 少了任何一個，preview 無法注入宿主快照、apply.php 也決定不了插入位置
 *
 * 第 1 項是整套流程的品質閥門：自訂樣式寫太多代表元件庫缺東西。超標時
 * **不是把數字調高**，而是提報元件缺口。
 *
 * 用法：
 *   php scripts/check-prototypes.php                # 檢查 prototypes/
 *   php scripts/check-prototypes.php --path=<dir>   # 檢查別的目錄
 *   php scripts/check-prototypes.php --quiet        # 只印失敗項
 *
 * exit code：0 = 全部通過，1 = 有違規或環境問題（CI 靠這個擋）
 *
 * 這支不依賴 Laravel —— CI 不該為了數行數而 boot 一個 framework。
 * 計算邏輯在 src/Prototype/{StyleBudget,Manifest}.php，與 preview 共用同一份。
 */

declare(strict_types=1);

require __DIR__ . '/../src/Prototype/StyleBudget.php';
require __DIR__ . '/../src/Prototype/Manifest.php';

use PiTw\PiDesignSystem\Prototype\Manifest;
use PiTw\PiDesignSystem\Prototype\StyleBudget;

/** 讓所有失敗走同一個出口，訊息格式一致。 */
function fail(string $message): never
{
    fwrite(STDERR, $message . "\n");
    exit(1);
}

// ---------- 參數 ----------

$root = dirname(__DIR__);
$options = ['path' => $root . '/prototypes', 'quiet' => false];

foreach (array_slice($argv, 1) as $arg) {
    if (preg_match('/^--path=(.+)$/', $arg, $m) === 1) {
        $options['path'] = $m[1];
    } elseif ($arg === '--quiet') {
        $options['quiet'] = true;
    } else {
        fail("未知參數：{$arg}");
    }
}

$base = realpath($options['path']);

if ($base === false) {
    fail("找不到目錄：{$options['path']}");
}

// ---------- 收集 prototype ----------

$projectDirs = glob($base . '/*', GLOB_ONLYDIR);

if ($projectDirs === false) {
    fail("讀不到目錄內容：{$base}");
}

$prototypes = [];

foreach ($projectDirs as $projectDir) {
    $project = basename($projectDir);

    // _archive 之類的底線目錄不是專案
    if (str_starts_with($project, '_')) {
        continue;
    }

    foreach (['pages' => 'page', 'fragments' => 'fragment'] as $dir => $kind) {
        $files = glob("{$projectDir}/{$dir}/*.blade.php");

        if ($files === false) {
            fail("讀不到目錄內容：{$projectDir}/{$dir}");
        }

        foreach ($files as $file) {
            $source = file_get_contents($file);

            // 讀不到就中止，不要當成空字串 —— 空字串會讓所有檢查「通過」，
            // CI 綠燈但什麼都沒檢查到，比直接失敗危險得多。
            if ($source === false) {
                fail("讀不到檔案：{$file}");
            }

            $prototypes[] = [
                'label' => $project . '/' . basename($file, '.blade.php'),
                'kind' => $kind,
                'file' => $file,
                'source' => $source,
            ];
        }
    }
}

if ($prototypes === []) {
    // 沒有 prototype 不是錯誤（例如全部上線後移除了）
    echo "沒有找到任何 prototype（{$base}）\n";
    exit(0);
}

// 明確用 label 排序。先前寫 sort() 是拿整個 assoc array 比大小，
// 靠 PHP 的隱含比較語意才剛好像是按 label 排。
usort($prototypes, static fn (array $a, array $b): int => $a['label'] <=> $b['label']);

// ---------- 檢查 ----------

$failures = [];
$rows = [];

foreach ($prototypes as $prototype) {
    $budget = StyleBudget::measure($prototype['source']);
    $problems = [];

    if ($budget['overLimit']) {
        $problems[] = sprintf(
            '樣式 %d 行超過上限 %d（<style> %d 行 + inline %d 宣告）',
            $budget['total'],
            $budget['limit'],
            $budget['styleLines'],
            $budget['inlineDeclarations']
        );
    }

    if ($prototype['kind'] === 'fragment') {
        if (! Manifest::isDeclared($prototype['source'])) {
            $problems[] = 'fragment 沒有宣告 @piFragment manifest';
        } else {
            $missing = Manifest::missingKeys($prototype['source']);

            if ($missing !== []) {
                $problems[] = '@piFragment manifest 缺少：' . implode(' / ', $missing);
            }
        }
    }

    $rows[] = [
        'label' => $prototype['label'],
        'kind' => $prototype['kind'],
        'total' => $budget['total'],
        'detail' => sprintf('%d+%d', $budget['styleLines'], $budget['inlineDeclarations']),
        'ok' => $problems === [],
    ];

    foreach ($problems as $problem) {
        $failures[] = ['label' => $prototype['label'], 'problem' => $problem];
    }
}

// ---------- 輸出 ----------

if (! $options['quiet']) {
    printf("%-40s %-9s %-10s %s\n", 'Prototype', '類型', '樣式', '');
    printf("%s\n", str_repeat('-', 72));

    foreach ($rows as $row) {
        printf(
            "%-40s %-9s %-10s %s\n",
            $row['label'],
            $row['kind'],
            $row['total'] . ' (' . $row['detail'] . ')',
            $row['ok'] ? 'ok' : 'FAIL'
        );
    }

    echo "\n";
}

if ($failures !== []) {
    $affected = count(array_unique(array_column($failures, 'label')));

    fwrite(STDERR, "{$affected} 個 prototype 未通過檢查：\n\n");

    foreach ($failures as $failure) {
        fwrite(STDERR, "  {$failure['label']}\n    {$failure['problem']}\n\n");
    }

    fwrite(STDERR, <<<TXT
    ── 怎麼處理 ──────────────────────────────────────────────

    樣式超標：**不要把門檻調高**。超標本身就是「元件庫缺東西」的訊號，
    要走缺件流程把缺口提報出來，讓它變成一張新增元件的工單 ——
    這是元件庫持續完整的唯一機制（design-guideline-spec.md 的 8.5 / 8.6）。

      三層門檻與提議格式：.claude/skills/pm-to-preview/references/missing-component.md
      流程第 ⑥ 步的中斷規則：design-guideline-spec.md 的 7.1

    manifest 不完整：三個欄位的意義見
    .claude/skills/pm-to-preview/references/prototype-setup.md 的「Fragment prototype」。

    規則原文：CLAUDE.md 的「Prototype 硬性規則」。

    TXT);

    exit(1);
}

printf(
    "%d 個 prototype 全部通過（樣式上限 %d 行 = <style> 行數 + inline 宣告數）\n",
    count($rows),
    StyleBudget::LIMIT
);
