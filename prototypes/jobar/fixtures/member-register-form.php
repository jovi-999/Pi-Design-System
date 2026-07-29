<?php

/**
 * 會員註冊表單的預設值。
 *
 * 資料契約：controller 傳出同樣的 key 結構（回填舊值 / 驗證失敗重繪時用），
 * 前端 blade 一個字都不用改。
 *
 * agreePrivacy 是 bool —— 勾選狀態。實際送出時的必填驗證規則待後端補
 * （見 .scratch/member-register/backend-handoff.md）。
 *
 * errors 是「欄位名 => 提示訊息」。有值的欄位會以 form-control 的
 * state="invalid" + feedback 呈現紅色 hint。
 *
 * ⚠️ prototype 預設就帶滿三筆錯誤，是為了讓 PM 直接看到「必填未填的 hint 長相」——
 * 靜態 blade 沒有「點按鈕才出現」的行為（那是前端 JS / 後端 validator 的事）。
 * 要看乾淨的初始畫面，把 errors 改成空陣列即可，blade 不用動。
 *
 * 這個 key 的結構刻意對齊 Laravel validator 的輸出（欄位名 => 訊息），
 * 交接後 controller 直接把 $errors 餵進來就行。
 */

return [
    'name' => '',
    'email' => '',
    'password' => '',
    'agreePrivacy' => false,

    'errors' => [
        'name' => '請填寫姓名',
        'email' => '請填寫 Email',
        'password' => '請設定密碼',
    ],
];
