<?php

/**
 * 會員列表的假資料。
 *
 * 資料契約：每筆需要 name / email / status / joinedAt。
 * status 的值域見 member-status.php。
 */

return [
    ['name' => '陳怡君', 'email' => 'yijun.chen@example.com', 'status' => 'active', 'joinedAt' => '2026-03-14'],
    ['name' => '林建宏', 'email' => 'jianhong.lin@example.com', 'status' => 'pending', 'joinedAt' => '2026-05-02'],
    ['name' => '黃詩涵', 'email' => 'shihan.huang@example.com', 'status' => 'suspended', 'joinedAt' => '2025-11-28'],
];
