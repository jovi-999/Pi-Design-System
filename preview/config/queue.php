<?php

/**
 * Pi DS preview 的 queue 設定。
 *
 * 寫死 'sync'（同步執行，不進佇列）。preview 沒有背景工作 ——
 * skeleton 預設 database 會需要 jobs 資料表。
 *
 * 其餘設定沿用 framework 預設（vendor/laravel/framework/config/queue.php）。
 */

return [
    'default' => 'sync',

    'connections' => [
        'sync' => [
            'driver' => 'sync',
        ],
    ],
];
