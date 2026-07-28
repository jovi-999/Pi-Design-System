<?php

return [
    'name' => 'Content switcher',
    'scss' => 'resources/scss/components/_content-switcher.scss',
    'description' => '分頁式切換導覽。作用中的項目下方有短底線指示器。',

    'props' => [
        'tone' => ['basic', 'info', 'success'],
        'items' => "陣列：[['label'=>…, 'count'=>…, 'active'=>bool, 'href'=>…], …]。有 href 就 render <a>，否則 <button>",
    ],

    'slots' => ['default' => '自行組 item 時用（此時 items 留空）'],

    'notes' => [
        'tone 只有 basic / info / success —— 作用色是由 `.gl_content-switcher-{tone} .is-active` 定義的，其他色系沒有。',
        '這支元件收整個 items 陣列，不提供單獨的 item 元件：作用色掛在 outer 上，item 單獨存在沒有意義。',
        '切換行為要自己接（純 CSS 只提供 .is-active 的外觀）。',
    ],

    'examples' => [
        [
            'label' => '分頁導覽',
            'code' => <<<'BLADE'
            <x-pi::content-switcher
                tone="success"
                :items="[
                    ['label' => '面試心得', 'count' => 48, 'active' => true],
                    ['label' => '薪資', 'count' => '1,247'],
                    ['label' => '福利'],
                    ['label' => '職缺'],
                ]"
            />
            BLADE,
        ],
        [
            'label' => 'Info 色',
            'code' => <<<'BLADE'
            <x-pi::content-switcher
                tone="info"
                :items="[
                    ['label' => '全部'],
                    ['label' => '未讀', 'count' => 3, 'active' => true],
                ]"
            />
            BLADE,
        ],
    ],
];
