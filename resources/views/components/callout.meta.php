<?php

/**
 * Pi DS — Callout 的元件 metadata。
 */

return [
    'name' => 'Callout',
    'scss' => 'resources/scss/components/_callout.scss',
    'description' => '頁面內的提示區塊。左側圓形 icon 底色隨 tone 變化。',

    'props' => [
        'tone' => ['basic', 'info', 'success', 'warning', 'danger', 'purple'],
        'icon' => 'icon class，如 icon-info',
        'title' => '標題（fz-title-md fz-tit）',
    ],

    'slots' => [
        'default' => '內文',
        'action' => '右側行動區（選用），通常放一顆 button',
    ],

    'notes' => [
        'callout 沒有 orange —— 跟 button 的 tone 清單不同，傳了會丟例外。',
        '內文顏色用 inline style var(--fg-2)：設計系統沒有對應的 utility class，照既有 markup 搬，不自創。',
    ],

    'examples' => [
        [
            'label' => '各色系',
            'code' => <<<'BLADE'
            <x-pi::callout tone="success" icon="icon-shield-checked" title="你的資料會匿名處理">
                公司看不到你的姓名、Email，只能看到整體薪資分佈。
            </x-pi::callout>

            <x-pi::callout tone="info" icon="icon-info" title="這份職缺更新於 3 天前">
                超過 14 天未更新的職缺會自動下架。
            </x-pi::callout>

            <x-pi::callout tone="warning" icon="icon-alert-triangle" title="此薪資回報尚未驗證">
                驗證後會標上「已驗證」徽章，讀者較容易信任。
            </x-pi::callout>

            <x-pi::callout tone="danger" icon="icon-forbidden" title="這家公司已被多位使用者檢舉">
                主要原因：面試無故取消、薪資與招募描述不符。
            </x-pi::callout>

            <x-pi::callout tone="purple" icon="icon-send" title="AI 為你整理了面試可能的問題">
                基於這家公司過去 48 則面試心得彙整。
            </x-pi::callout>
            BLADE,
        ],
        [
            'label' => '帶行動按鈕',
            'code' => <<<'BLADE'
            <x-pi::callout tone="info" icon="icon-info" title="這份職缺更新於 3 天前">
                超過 14 天未更新的職缺會自動下架。
                <x-slot:action>
                    <x-pi::button size="sm" variant="outline" tone="info">查看</x-pi::button>
                </x-slot:action>
            </x-pi::callout>
            BLADE,
        ],
    ],
];
