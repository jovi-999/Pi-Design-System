<?php

/**
 * Pi DS preview 的 cache 設定。
 *
 * store 寫死 'file'，理由同 config/session.php —— skeleton 預設 database
 * 會為了一個開發工具養資料庫。
 *
 * 其餘設定沿用 framework 預設（vendor/laravel/framework/config/cache.php）。
 */

return [
    'default' => 'file',

    'stores' => [
        'file' => [
            'driver' => 'file',
            'path' => storage_path('framework/cache/data'),
        ],
    ],
];
