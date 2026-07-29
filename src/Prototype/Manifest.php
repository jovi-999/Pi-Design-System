<?php

declare(strict_types=1);

namespace PiTw\PiDesignSystem\Prototype;

/**
 * `@piFragment([...])` manifest 的靜態解析。
 *
 * 為什麼靜態解析而不是 runtime：manifest 是給 preview controller 與
 * scripts/{apply,check-prototypes}.php 讀的中介資料，三者都需要「不 render
 * 就知道內容」，而後兩者完全在 Laravel 之外執行。
 *
 * 為什麼放在套件的 src/ 而不是各自實作：先前 preview 與 CI script 各寫一份，
 * CI 那份的 regex 跑在**整份原始碼**而不是 manifest 區塊內 —— blade 註解裡
 * 出現 `'slot' => 'x'` 就會被當成宣告過，漏檢的 fragment 照樣通過 CI。
 * 這正是同一份判斷分兩處實作的典型後果。
 */
class Manifest
{
    /** spec 5.3 定義的三個必要欄位。 */
    public const KEYS = ['target', 'slot', 'host'];

    /**
     * 解析 manifest。
     *
     * @return array<string, string> 空陣列＝沒有 `@piFragment` 宣告
     */
    public static function parse(string $source): array
    {
        $block = static::block($source);

        if ($block === null) {
            return [];
        }

        $manifest = [];

        foreach (self::KEYS as $key) {
            // 只認單／雙引號的字面值 —— manifest 不該有運算式
            if (preg_match('/[\'"]' . $key . '[\'"]\s*=>\s*[\'"]([^\'"]*)[\'"]/', $block, $m) === 1) {
                $value = trim($m[1]);

                if ($value !== '') {
                    $manifest[$key] = $value;
                }
            }
        }

        return $manifest;
    }

    /**
     * 有宣告 `@piFragment` 嗎（不管內容完不完整）。
     *
     * 與 `parse()` 分開是因為「沒宣告」與「宣告了但缺欄位」要給不同的錯誤訊息。
     */
    public static function isDeclared(string $source): bool
    {
        return static::block($source) !== null;
    }

    /**
     * 缺少的必要欄位。
     *
     * @return array<int, string>
     */
    public static function missingKeys(string $source): array
    {
        $manifest = static::parse($source);

        return array_values(array_diff(self::KEYS, array_keys($manifest)));
    }

    /**
     * 取出 `@piFragment([ … ])` 中括號內的字串。
     *
     * 先剝掉 blade 註解：prototype 的說明註解常引用 manifest 當範例
     * （`@piFragment([...])` 的用法示範），那不是真的宣告。
     */
    protected static function block(string $source): ?string
    {
        $withoutComments = preg_replace('/\{\{--.*?--\}\}/s', '', $source);

        // preg_replace 失敗回 null —— 退回原始碼而不是靜默當成空字串
        $searchIn = $withoutComments ?? $source;

        if (preg_match('/@piFragment\s*\(\s*\[(.*?)\]\s*\)/s', $searchIn, $m) !== 1) {
            return null;
        }

        return $m[1];
    }
}
