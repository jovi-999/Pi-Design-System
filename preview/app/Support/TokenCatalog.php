<?php

namespace App\Support;

use Illuminate\Support\Collection;
use RuntimeException;

/**
 * 從套件的 SCSS 原始碼解析 design token 的「名稱」。
 *
 * 為什麼只取名稱、不取值：
 *
 *   SCSS 裡的宣告長這樣 —— `--cl-basic-900: #{$_cl-basic-900-raw};`
 *   值是 Sass 插值，靜態解析拿到的是 `#{$_cl-basic-900-raw}` 這串字，不是 #1F2123。
 *
 *   要拿到解析後的值有兩條路：
 *     (a) 解析編譯產物 dist/pi-ds-tokens.css —— 但 dist/ 是 gitignore 的建置產物，
 *         沒先跑 npm run build:tokens 這頁就掛掉
 *     (b) 讓瀏覽器用 getComputedStyle 讀 —— 那就是瀏覽器實際看到的值
 *
 *   選 (b)：名稱來自原始碼（永遠存在、不需 build），值來自瀏覽器（不可能與實際
 *   渲染不一致）。表格顯示的值與畫面上的色塊必然同源，少掉一整類「表上寫 A、
 *   實際是 B」的 bug。
 *
 * 驗證過：原始碼解析出的名稱集合與 dist/pi-ds-tokens.css 完全相同（170 個）。
 */
class TokenCatalog
{
    /**
     * 各 token 檔的顯示名稱與用途。key = 檔名（去掉底線與 .scss）。
     *
     * 手維護的只有這張表（7 筆，等同 token 檔的數量）—— token 本身全部自動掃出。
     */
    private const GROUPS = [
        'colors' => ['label' => '色彩', 'kind' => 'color'],
        'typography' => ['label' => '字體', 'kind' => 'text'],
        'spacing' => ['label' => '間距', 'kind' => 'length'],
        'radius' => ['label' => '圓角', 'kind' => 'radius'],
        'shadow' => ['label' => '陰影', 'kind' => 'shadow'],
        'motion' => ['label' => '動態', 'kind' => 'raw'],
        'breakpoints' => ['label' => 'RWD 斷點', 'kind' => 'length'],
        'grid' => ['label' => '格線', 'kind' => 'length'],
    ];

    /**
     * 走 vendor/ 而不是 ../resources —— 與 ComponentCatalog 一致，讓 preview 讀到的
     * 跟專案端 composer require 之後讀到的是同一條路徑。
     */
    public static function path(): string
    {
        $path = realpath(base_path('vendor/pi-tw/pi-design-system/resources/scss/tokens'));

        if ($path === false) {
            throw new RuntimeException(
                '找不到套件的 tokens 目錄。先在 preview/ 執行 `docker compose run --rm app composer install`。'
            );
        }

        return $path;
    }

    /**
     * 全部 token，依 GROUPS 的順序分組。
     *
     * @return Collection<string, array{label: string, kind: string, tokens: array}>
     */
    public static function all(): Collection
    {
        $groups = collect();

        foreach (self::GROUPS as $file => $meta) {
            $tokens = static::parse(static::path() . "/_{$file}.scss");

            // 檔案存在但沒有 CSS 變數宣告（例如只有 Sass 變數）不算錯，跳過
            if ($tokens === []) {
                continue;
            }

            $groups->put($file, $meta + ['tokens' => $tokens]);
        }

        return $groups;
    }

    public static function group(string $file): ?array
    {
        if (! isset(self::GROUPS[$file])) {
            return null;
        }

        $tokens = static::parse(static::path() . "/_{$file}.scss");

        return self::GROUPS[$file] + ['file' => $file, 'tokens' => $tokens];
    }

    /** @return array<int, string> 可用的 group key */
    public static function groupKeys(): array
    {
        return array_keys(self::GROUPS);
    }

    public static function count(): int
    {
        return static::all()->sum(fn (array $group) => count($group['tokens']));
    }

    /**
     * 解析一支 token 檔裡的 CSS 自訂屬性宣告。
     *
     * 保留宣告順序（色階由淺到深是有意義的排列，字母排序會打亂），
     * 並抓行尾的 `//` 註解當說明。
     *
     * @return array<int, array{name: string, note: ?string}>
     */
    protected static function parse(string $file): array
    {
        if (! is_file($file)) {
            return [];
        }

        $source = file_get_contents($file);

        if ($source === false) {
            return [];
        }

        $tokens = [];
        $seen = [];

        foreach (explode("\n", $source) as $line) {
            if (! preg_match('/^\s*(--[a-z0-9-]+)\s*:\s*(.*?)\s*;?\s*(?:\/\/\s*(.*))?$/', $line, $m)) {
                continue;
            }

            $name = $m[1];

            // dark mode 之類的區塊會重複宣告同一個 token，只留第一次
            if (isset($seen[$name])) {
                continue;
            }

            $seen[$name] = true;
            $tokens[] = [
                'name' => $name,
                'note' => ($m[3] ?? '') !== '' ? trim($m[3]) : null,
            ];
        }

        return $tokens;
    }

    /**
     * `_typography.scss` 裡的 `.fz-*` class（字級 utility）。
     *
     * 這些不是 CSS 變數而是 class，但屬於 foundation 的字體層，
     * 所以一併從同一支檔案掃出來。
     *
     * @return array<int, array{class: string, declarations: string}>
     */
    public static function typeClasses(): array
    {
        $file = static::path() . '/_typography.scss';

        if (! is_file($file)) {
            return [];
        }

        $source = file_get_contents($file) ?: '';
        $classes = [];

        // 只認單行宣告的簡單規則（.fz-x { … }）—— 字級 class 全部是這個形狀
        if (preg_match_all('/^\s*(\.fz-[a-z0-9-]+)\s*\{\s*([^}]*?)\s*\}/m', $source, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $m) {
                $classes[] = [
                    'class' => $m[1],
                    'declarations' => preg_replace('/\s+/', ' ', trim($m[2])),
                ];
            }
        }

        return $classes;
    }
}
