<?php

return [
    'name' => 'Pagination',
    'scss' => 'resources/scss/components/_pagination.scss',
    'description' => '頁碼列。當前頁深色實心，首/末頁時箭頭停用。≤768px 自動縮為 32px。',

    'props' => [
        'current' => '當前頁（整數）',
        'last' => '最後一頁（整數）',
        'urlTemplate' => "連結樣板，:page 會被頁碼取代（預設 '?page=:page'）",
    ],

    'notes' => [
        '前綴是 iw_ 不是 gl_（.iw_pagination-outer-v3 / .iw_active）—— 對齊生產環境既有命名，改掉是 breaking change。',
        '.iw_active 掛在 <li> 上而不是 <a> 上：SCSS 的選擇器是 `.iw_active .gl_page-link`。',
        '目前是「列出全部頁碼」，沒有省略號（…）邏輯 —— SCSS 沒有對應樣式，頁數多時需要走元件缺口流程。',
        '.iw_pagination-wrap 的 max-width 是 408px，外層寬度由使用端決定。',
    ],

    'examples' => [
        [
            'label' => '第一頁（prev 停用）',
            'code' => <<<'BLADE'
            <x-pi::pagination :current="1" :last="5" style="width: 360px;" />
            BLADE,
        ],
        [
            'label' => '中間頁',
            'code' => <<<'BLADE'
            <x-pi::pagination :current="3" :last="5" style="width: 360px;" />
            BLADE,
        ],
        [
            'label' => '最後一頁（next 停用）',
            'code' => <<<'BLADE'
            <x-pi::pagination :current="5" :last="5" style="width: 360px;" />
            BLADE,
        ],
    ],
];
