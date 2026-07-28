<?php

return [
    'name' => 'Dropdown item',
    'scss' => 'resources/scss/components/_dropdown.scss',
    'description' => '浮出選單內的單一項目。兩種尺寸，可帶前置 icon。',

    'props' => [
        'size' => ['md', 'sm'],
        'icon' => 'icon class（會放在文字左側）',
        'href' => '給了就 render <a>，否則 <button>',
    ],

    'slots' => ['default' => '項目文字'],

    'notes' => [
        '⚠️ 設計系統只有「選單項目」的樣式，沒有浮出面板本身（定位 / 陰影 / 開關行為）—— 這是已知元件缺口。需要完整 dropdown 請走缺口流程提報，不要自行新增 .gl_dropdown。',
        'hover 樣式的選擇器是 a.gl_dropdown-item / button.gl_dropdown-item，所以標籤只能是這兩種，不能用 div。',
        '危險操作的紅字在既有 markup 是 inline style（var(--cl-red-600)），設計系統沒有對應的 modifier class。',
    ],

    'examples' => [
        [
            'label' => 'MD',
            'code' => <<<'BLADE'
            <x-pi::dropdown-item icon="icon-bookmark">收藏這則職缺</x-pi::dropdown-item>
            <x-pi::dropdown-item icon="icon-share">分享</x-pi::dropdown-item>
            <x-pi::dropdown-item icon="icon-flag">檢舉</x-pi::dropdown-item>
            <x-pi::dropdown-item icon="icon-delete" style="color: var(--cl-red-600);">刪除</x-pi::dropdown-item>
            BLADE,
        ],
        [
            'label' => 'SM',
            'code' => <<<'BLADE'
            <x-pi::dropdown-item size="sm" icon="icon-bookmark">收藏這則職缺</x-pi::dropdown-item>
            <x-pi::dropdown-item size="sm" icon="icon-share">分享</x-pi::dropdown-item>
            BLADE,
        ],
    ],
];
