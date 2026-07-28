<?php

return [
    'name' => 'Alert',
    'scss' => 'resources/scss/components/_alert.scss',
    'description' => '浮出式提示條。深色底、圓形 icon 底色隨 tone 變化，最小寬 352px。',

    'props' => [
        'tone' => ['basic', 'info', 'success', 'warning', 'danger', 'purple'],
        'icon' => 'icon class',
        'description' => '副文（fz-body-xs）',
    ],

    'slots' => [
        'default' => '主文（fz-body-sm）',
        'action' => '右側行動區（選用）',
    ],

    'notes' => [
        'alert 沒有 orange。',
        '.gl_alert-body 是 inline-flex + min-width 352px，放在窄容器內會撐開。',
    ],

    'examples' => [
        [
            'label' => '各色系',
            'code' => <<<'BLADE'
            <x-pi::alert tone="success" icon="icon-checked" description="審核通過後會通知你">已送出面試心得</x-pi::alert>
            <x-pi::alert tone="info" icon="icon-info">你的履歷被 3 家公司查看</x-pi::alert>
            <x-pi::alert tone="warning" icon="icon-alert-triangle">此薪資回報尚待驗證</x-pi::alert>
            <x-pi::alert tone="danger" icon="icon-cross">上傳失敗，請稍後再試</x-pi::alert>
            <x-pi::alert tone="purple" icon="icon-send">AI 已為你整理面試重點</x-pi::alert>
            BLADE,
        ],
        [
            'label' => '帶行動按鈕',
            'code' => <<<'BLADE'
            <x-pi::alert tone="info" icon="icon-info">
                你的履歷被 3 家公司查看
                <x-slot:action>
                    <x-pi::button size="xs" variant="ghost" tone="info">查看</x-pi::button>
                </x-slot:action>
            </x-pi::alert>
            BLADE,
        ],
    ],
];
