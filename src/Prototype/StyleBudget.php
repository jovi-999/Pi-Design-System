<?php

declare(strict_types=1);

namespace PiTw\PiDesignSystem\Prototype;

use RuntimeException;

/**
 * Prototype 的 page-scoped 樣式預算（CLAUDE.md 第 3 條的 30 行門檻）。
 *
 * 這條規則是整套流程的品質閥門：**自訂樣式寫太多就代表元件庫缺東西**。
 * PM 不會知道什麼做得到什麼做不到；如果 agent 遇到缺口就自己刻樣式，
 * 缺口就被藏起來了，drift 從這裡開始。
 *
 * 為什麼放在套件的 src/ 而不是各自實作：
 *   兩個消費端 —— preview 的清單頁（顯示行數）與 scripts/check-prototypes.php
 *   （CI 阻擋）—— 必須算出同一個數字。分開實作遲早一邊算 25 一邊算 32，
 *   然後沒人知道該信哪個。
 *
 * 這個類別刻意不依賴 Laravel：CI 只跑 `php scripts/check-prototypes.php`，
 * 不該為了數行數而 boot 一個 framework。
 */
class StyleBudget
{
    /** CLAUDE.md 第 3 條的上限。 */
    public const LIMIT = 30;

    /**
     * 算一份 prototype 原始碼的樣式用量。
     *
     * 回傳裡帶 limit，讓顯示端不必自己引用常數 —— 值仍來自 self::LIMIT，
     * 只有這一個來源。
     *
     * @return array{styleLines: int, inlineDeclarations: int, total: int, limit: int, overLimit: bool}
     */
    public static function measure(string $source): array
    {
        $styleLines = static::countStyleBlockLines($source);
        $inline = static::countInlineDeclarations($source);
        $total = $styleLines + $inline;

        return [
            'styleLines' => $styleLines,
            'inlineDeclarations' => $inline,
            'total' => $total,
            'limit' => self::LIMIT,
            'overLimit' => $total > self::LIMIT,
        ];
    }

    /**
     * `<style>` 區塊內的非空白行數。
     *
     * 未閉合的 `<style>` 也要算 —— 否則漏打一個 `</style>` 就能讓整個區塊
     * 不計入門檻。這種寫法在瀏覽器裡也是「後面全部當 CSS」，計到檔尾才符合實際。
     */
    public static function countStyleBlockLines(string $source): int
    {
        $blocks = static::styleBlocks($source);
        $lines = 0;

        foreach ($blocks as $block) {
            foreach (explode("\n", trim($block)) as $line) {
                if (trim($line) !== '') {
                    $lines++;
                }
            }
        }

        return $lines;
    }

    /**
     * inline `style="…"` 裡的宣告數（以 `;` 拆）。
     *
     * 為什麼要算這個：只數 `<style>` 區塊的話，把樣式搬進 style attribute
     * 就能繞過門檻，閥門直接失效。這個漏洞是 code review 實際抓到的
     * （`<div style="height: 24px">` 完全不計入）。
     *
     * 一個宣告視為一行 —— `style="a: 1; b: 2"` 算 2，與寫在 `<style>` 內
     * 分兩行的成本相同。
     *
     * 四個容易算錯的地方（都是 code review 抓到的）：
     *   1. 單引號 `style='…'` —— 不認的話它就是另一個繞道
     *   2. `data-style=` / `:style=`（blade 綁定）—— 不是 inline 樣式，不能算
     *   3. `<style>` 區塊內若出現 `style="…"` 字串會被重複計 —— 先剝掉區塊
     *   4. blade 註解裡的範例 —— 不會 render，不算
     */
    public static function countInlineDeclarations(string $source): int
    {
        $stripped = static::withoutStyleBlocks(static::withoutBladeComments($source));

        // (?<![-:\w]) 擋掉 data-style / :style / x-bind:style / myStyle
        $pattern = '/(?<![-:\w])style\s*=\s*([\'"])(.*?)\1/s';
        $count = preg_match_all($pattern, $stripped, $matches);

        if ($count === false) {
            throw new RuntimeException('解析 inline style 失敗：' . preg_last_error_msg());
        }

        if ($count === 0) {
            return 0;
        }

        $declarations = 0;

        foreach ($matches[2] as $value) {
            foreach (explode(';', $value) as $declaration) {
                if (trim($declaration) !== '') {
                    $declarations++;
                }
            }
        }

        return $declarations;
    }

    /**
     * 取出所有 `<style>` 區塊的內容（含未閉合的，計到檔尾）。
     *
     * public 而非 protected：ClassPrefix 要掃同樣這些區塊裡的選擇器。
     * 「哪些文字算 `<style>` 內容」（含未閉合區塊的處理）只能有一份定義 ——
     * 兩處各寫一份 regex 遲早分岔，行數與 class 檢查會對不同的範圍生效。
     *
     * @return array<int, string>
     */
    public static function styleBlocks(string $source): array
    {
        // 閉合的區塊
        $count = preg_match_all('/<style[^>]*>(.*?)<\/style>/is', $source, $matches);

        if ($count === false) {
            throw new RuntimeException('解析 <style> 區塊失敗：' . preg_last_error_msg());
        }

        $blocks = $count > 0 ? $matches[1] : [];

        // 未閉合的最後一個區塊：剝掉所有閉合區塊後，若還剩一個開頭就計到檔尾
        $remainder = preg_replace('/<style[^>]*>.*?<\/style>/is', '', $source) ?? '';

        if (preg_match('/<style[^>]*>(.*)$/is', $remainder, $m) === 1) {
            $blocks[] = $m[1];
        }

        return $blocks;
    }

    protected static function withoutStyleBlocks(string $source): string
    {
        $closed = preg_replace('/<style[^>]*>.*?<\/style>/is', '', $source) ?? $source;

        return preg_replace('/<style[^>]*>.*$/is', '', $closed) ?? $closed;
    }

    protected static function withoutBladeComments(string $source): string
    {
        return preg_replace('/\{\{--.*?--\}\}/s', '', $source) ?? $source;
    }
}
