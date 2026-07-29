<?php

namespace App\Support;

use RuntimeException;

/**
 * 從套件的 assets/ 讀 icon 清單。
 *
 * 兩個資料源都是套件出貨內容（沒有 export-ignore）：
 *   icon-names.json   250 個名稱的 list（不含 icon- 前綴）
 *   icon-cp-map.json  名稱 → codepoint 的 map，用來顯示 \eaxx
 *
 * 手維護的部分：零。加字型時重跑產生這兩支 JSON 的流程，這頁自己就多幾格。
 */
class IconCatalog
{
    public static function path(): string
    {
        $path = realpath(base_path('vendor/pi-tw/pi-design-system/assets'));

        if ($path === false) {
            throw new RuntimeException(
                '找不到套件的 assets 目錄。先在 preview/ 執行 `docker compose run --rm app composer install`。'
            );
        }

        return $path;
    }

    /**
     * @return array<int, array{name: string, class: string, codepoint: ?string}>
     */
    public static function all(): array
    {
        $names = static::json('icon-names.json');
        $codepoints = static::json('icon-cp-map.json');

        $icons = [];

        foreach ($names as $name) {
            if (! is_string($name)) {
                continue;
            }

            $icons[] = [
                'name' => $name,
                'class' => 'icon-' . $name,
                'codepoint' => isset($codepoints[$name]) ? (string) $codepoints[$name] : null,
            ];
        }

        return $icons;
    }

    /**
     * 沒有 codepoint 的 icon —— 兩支 JSON 不同步的訊號，要看得見而不是安靜略過。
     *
     * @return array<int, string>
     */
    public static function missingCodepoints(): array
    {
        return array_values(array_map(
            fn (array $icon) => $icon['name'],
            array_filter(static::all(), fn (array $icon) => $icon['codepoint'] === null)
        ));
    }

    protected static function json(string $file): array
    {
        $path = static::path() . '/' . $file;

        if (! is_file($path)) {
            throw new RuntimeException("找不到 {$file}（預期在 {$path}）");
        }

        $decoded = json_decode(file_get_contents($path) ?: '', true);

        if (! is_array($decoded)) {
            throw new RuntimeException("{$file} 不是合法的 JSON 陣列");
        }

        return $decoded;
    }
}
