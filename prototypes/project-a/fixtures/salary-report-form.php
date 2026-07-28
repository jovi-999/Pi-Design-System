<?php

/**
 * 薪資回報表單的預填值。
 *
 * 資料契約：這幾個 key 就是表單送出的欄位。型別待後端確認
 * （monthlySalary 是整數還是字串？見後端 handoff 的「型別待補」）。
 */

return [
    'jobTitle' => '',
    'industry' => null,
    'monthlySalary' => '',
    'note' => '',
    'publicProfile' => true,
];
