<?php

namespace App\Http\Controllers;

use App\Support\PrototypeCatalog;
use PiTw\PiDesignSystem\Prototype\FixtureLoader;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\View as ViewFactory;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Prototype 的清單與預覽。
 *
 * Page 與 fragment 的 render 方式不同：
 *   page     → 直接 render（prototype 自己 @extends layout，是完整頁面）
 *   fragment → render 成 HTML 片段後，注入 host 快照的 slot marker 位置
 */
class PrototypeController extends Controller
{
    /** host 快照裡的注入錨點。HTML 註解，才能在 render 後存活。 */
    private const SLOT_MARKER_PATTERN = '/<!--\s*@pi-slot:\s*%s\s*-->/';

    public function index(): View
    {
        return view('prototypes.index', [
            'prototypes' => PrototypeCatalog::all(),
        ]);
    }

    public function show(string $project, string $name): Response
    {
        $prototype = PrototypeCatalog::find($project, $name);

        if ($prototype === null) {
            throw new NotFoundHttpException("找不到 prototype [{$project}/{$name}]");
        }

        // @piFixture 要知道去哪找 fixtures。由 render 端指定而不是讓 blade
        // 自己算路徑 —— 編譯後的 blade 沒有原始檔位置可用（見 FixtureLoader）。
        FixtureLoader::useDirectory(
            PrototypeCatalog::path() . '/' . $prototype['project'] . '/fixtures'
        );

        $bar = view('prototypes._bar', ['prototype' => $prototype])->render();

        try {
            // View::file() 而不是 view()：fragment 檔名含 dot（member-list.filters），
            // 用 view name 解析會被當成目錄分隔。
            //
            // $pvBar 傳給 pi::layouts.preview（page 才會 extends 它）。fragment
            // 不 extends layout，導覽列在下面注入宿主快照時另外加。
            $html = ViewFactory::file($prototype['file'], ['pvBar' => $bar])->render();
        } finally {
            // 不留殘留狀態：下一個 request 若忘了設定，要拿到明確的錯誤而不是
            // 悄悄讀到別的專案的 fixture。
            FixtureLoader::useDirectory(null);
        }

        if ($prototype['kind'] === 'fragment') {
            $html = $this->prependBar($this->injectIntoHost($prototype, $html), $bar);
        }

        return response($html);
    }

    /**
     * 把導覽列插在宿主快照的 <body> 之後。
     *
     * fragment 不 extends layout，所以拿不到 layout 的 $pvBar —— 導覽列必須直接
     * 塞進 rendered HTML。這只影響 preview 畫面：apply.php 讀的是 prototype 原始檔，
     * 不會經過這裡。
     *
     * 找不到 <body> 就整份前置（例如宿主快照是個片段而不是完整文件）——
     * 位置不完美但不會讓人失去導覽。
     */
    protected function prependBar(string $html, string $bar): string
    {
        $injected = preg_replace('/(<body\b[^>]*>)/i', '$1' . str_replace('$', '\\$', $bar), $html, 1, $count);

        if ($injected === null || $count === 0) {
            return $bar . $html;
        }

        return $injected;
    }

    /**
     * 把 fragment 塞進 host 快照的 slot marker 位置。
     *
     * 目的是讓 PM 看到 fragment 坐在真實脈絡裡的樣子 —— 能判斷跟上方標題的
     * 間距、跟下方表格的視覺重量搭不搭，而不是懸在空白頁上。
     */
    protected function injectIntoHost(array $prototype, string $fragmentHtml): string
    {
        $manifest = $prototype['manifest'];

        // 沒宣告 manifest 就退回「裸片段」，並講清楚為什麼 ——
        // 靜默 fallback 會讓人以為 host 壞了。
        if (empty($manifest['host']) || empty($manifest['slot'])) {
            return $this->bareFragment(
                $fragmentHtml,
                'Fragment 沒有宣告完整的 @piFragment manifest（需要 host 與 slot），'
                . '因此無法注入宿主快照，以下是裸片段。'
            );
        }

        $hostPath = PrototypeCatalog::path() . '/' . $manifest['host'];

        if (! is_file($hostPath)) {
            return $this->bareFragment(
                $fragmentHtml,
                "找不到宿主快照 [{$manifest['host']}]。先執行："
                . " php scripts/fetch-host.php {$prototype['project']} <path> <url>"
            );
        }

        $host = file_get_contents($hostPath) ?: '';
        $pattern = sprintf(self::SLOT_MARKER_PATTERN, preg_quote($manifest['slot'], '/'));

        if (! preg_match($pattern, $host)) {
            return $this->bareFragment(
                $fragmentHtml,
                "宿主快照裡找不到 slot marker `<!-- @pi-slot: {$manifest['slot']} -->`。"
                . '專案端要先埋 marker，且必須用 HTML 註解 —— blade 註解在 render 後會消失。'
            );
        }

        // 用 callback 版避免 fragment HTML 裡的 $ 或 \1 被當成反向參照
        return preg_replace_callback($pattern, fn () => $fragmentHtml, $host, 1);
    }

    /**
     * 無法注入時的退回畫面：片段照舊顯示，但把原因寫在最上面。
     */
    protected function bareFragment(string $fragmentHtml, string $reason): string
    {
        return view('prototypes.bare-fragment', [
            'fragmentHtml' => $fragmentHtml,
            'reason' => $reason,
        ])->render();
    }
}
