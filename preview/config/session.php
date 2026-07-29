<?php

/**
 * Pi DS preview 的 session 設定。
 *
 * driver 寫死 'file'，**刻意不留 env() 逃生口**。
 *
 * Laravel 12 skeleton 的預設是 database（sqlite），那會為了一個「只 render
 * blade」的工具養一個資料庫與三支 migration。preview 沒有登入、沒有使用者、
 * 沒有任何需要持久化的狀態 —— session 只是 framework 的 middleware 需要一個
 * 地方放東西而已。
 *
 * 不留 env() 的理由：`.env` 是 gitignored，改它對別人 clone 下來的環境無效，
 * 而 `.env.example` 仍寫著 SESSION_DRIVER=database。寫死在這裡是唯一能讓
 * 「preview 不需要 DB」這個決定跟著 repo 走的方式。
 *
 * 其餘設定沿用 framework 預設（vendor/laravel/framework/config/session.php）。
 */

return [
    'driver' => 'file',

    'lifetime' => 120,
    'expire_on_close' => false,

    'files' => storage_path('framework/sessions'),
];
