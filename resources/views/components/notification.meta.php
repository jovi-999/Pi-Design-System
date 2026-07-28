<?php

return [
    'name' => 'Notification item',
    'scss' => 'resources/scss/components/_notification.scss',
    'description' => '通知列表的一列。底部有分隔線，多列直接疊放即成列表。',

    'props' => [
        'tone' => ['basic', 'info', 'success', 'warning', 'danger', 'purple'],
        'icon' => 'icon class',
        'title' => '標題（fz-title-sm fz-tit）',
        'time' => '右側時間字串',
    ],

    'slots' => ['default' => '內文（fz-body-sm）'],

    'notes' => [
        '這是「一列」而不是整個列表 —— 列表由多個 item 疊起來，分隔線靠 item 自己的 border-bottom。',
        'icon 容器 class 是 .gl_icon-wrap（沒有 notification 前綴），與其他元件共用這個名稱。',
    ],

    'examples' => [
        [
            'label' => '通知列表',
            'code' => <<<'BLADE'
            <x-pi::notification tone="success" icon="icon-checked" title="面試心得已發布" time="3 分鐘前">
                你針對「台積電 · 軟體工程師」的面試心得已通過審核。
            </x-pi::notification>

            <x-pi::notification tone="info" icon="icon-user" title="3 位使用者覺得你的心得有幫助" time="1 小時前">
                累計已被 252 人標記有幫助。
            </x-pi::notification>

            <x-pi::notification tone="warning" icon="icon-alert-triangle" title="薪資回報待驗證" time="昨天">
                補上薪資證明可提升可信度。
            </x-pi::notification>
            BLADE,
        ],
    ],
];
