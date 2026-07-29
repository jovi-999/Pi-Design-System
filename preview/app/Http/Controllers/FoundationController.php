<?php

namespace App\Http\Controllers;

use App\Support\IconCatalog;
use App\Support\TokenCatalog;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Foundation 頁：token 與 icon。
 *
 * 全部從套件的原始碼／assets 掃出來，沒有手維護的清單 ——
 * 這是取代舊 preview-static/tokens.html（26 KB 手維護 HTML）的理由：
 * 那份每加一個 token 就要記得同步改，而「記得」正是 drift 的來源。
 */
class FoundationController extends Controller
{
    /** 分頁的顯示順序與說明。icons 不是 token group，單獨處理。 */
    private const PAGES = [
        'colors' => '色階與語意別名（fg / bg / border）',
        'typography' => '字級 class、字重與字體家族',
        'spacing' => '間距階梯',
        'radius' => '圓角與 corner-shape',
        'shadow' => '陰影，以及與描邊疊成 Ring 的組合',
        'motion' => '時長與緩動',
        'breakpoints' => 'RWD 斷點',
    ];

    public function index(): View
    {
        return view('foundation.index', [
            'groups' => TokenCatalog::all(),
            'pages' => self::PAGES,
            'tokenCount' => TokenCatalog::count(),
            'iconCount' => count(IconCatalog::all()),
        ]);
    }

    /**
     * 全部 token 一頁列完（可搜尋）。取代舊的 tokens.html。
     */
    public function tokens(): View
    {
        return view('foundation.tokens', [
            'groups' => TokenCatalog::all(),
            'pages' => self::PAGES,
            'tokenCount' => TokenCatalog::count(),
        ]);
    }

    /**
     * 單一 token 群組。用同一支 view，靠 group 的 kind 決定預覽格的畫法。
     */
    public function group(string $group): View
    {
        $data = TokenCatalog::group($group);

        if ($data === null) {
            throw new NotFoundHttpException(
                "找不到 foundation 分頁 [{$group}]，可用的是：" . implode(' / ', TokenCatalog::groupKeys())
            );
        }

        return view('foundation.group', [
            'group' => $data,
            'description' => self::PAGES[$group] ?? null,
            'pages' => self::PAGES,
            // 字體頁多列 .fz-* class
            'typeClasses' => $group === 'typography' ? TokenCatalog::typeClasses() : [],
        ]);
    }

    public function icons(): View
    {
        return view('foundation.icons', [
            'icons' => IconCatalog::all(),
            'missing' => IconCatalog::missingCodepoints(),
            'pages' => self::PAGES,
        ]);
    }
}
