{{--
    Page prototype：薪資回報表單。

    這份是 blade 路線（pm-to-preview skill 改版後）的回歸基準 —— 涵蓋 page 型
    prototype、@piFixture、組合/缺口標記、標籤與 control 的 for/id 綁定。
    對應 handoff：.scratch/salary-report/{frontend,backend}-handoff.md

    交接進專案時只動兩行：
      1. 刪掉下面兩行 @piFixture —— 資料改由 controller 傳入
      2. @extends('pi::layouts.preview') 換成專案自己的 layout

    @section('content') 內的 body 一個字都不用改。
--}}
@piFixture($industries, 'salary-industries')
@piFixture($form, 'salary-report-form')

@extends('pi::layouts.preview')

@section('title', '回報薪資')

@section('content')
    {{--
        ⚠️ 元件缺口（已提報）：設計系統沒有「欄位標籤」元件 —— _form.scss 內
        沒有任何 label 相關 class（已 grep 確認）。

        本頁同一個標籤結構重複四次，已超出「一次性組合件」的範圍，因此在
        前端 handoff 以正式缺口提報 field-label（4 欄提議見
        .scratch/salary-report/frontend-handoff.md）。這裡的疊法是 placeholder。

        自訂樣式 6 行，未超過 CLAUDE.md 第 3 條的 30 行上限。
    --}}
    <style>
        .pa-form { max-width: 560px; margin: 40px auto; padding: 0 24px; }
        .pa-field { margin-bottom: 20px; }
        .pa-field__label { margin-bottom: 6px; }
        .pa-field--gap { margin-bottom: 32px; }
        .pa-row { display: flex; align-items: center; gap: 12px; }
        .pa-actions { display: flex; gap: 12px; margin-top: 28px; }
    </style>

    <div class="pa-form">
        <h1 class="fz-headline-sm fz-tit">回報薪資</h1>

        <div class="pa-field pa-field--gap">
            <x-pi::callout tone="success" icon="icon-shield-check" title="你的資料會匿名處理">
                公司看不到你的姓名與 Email，只會看到整體薪資分佈。
            </x-pi::callout>
        </div>

        {{--
            標籤用 <label for> 綁 id：placeholder 也要能被讀螢幕軟體正確關聯，
            否則交接後 a11y 的債會留給前端。id 由 name 對應，不另發明命名規則。
        --}}
        <div class="pa-field">
            <label class="pa-field__label fz-title-sm fz-tit" for="jobTitle">職稱</label>
            <x-pi::form-control
                id="jobTitle"
                name="jobTitle"
                :value="$form['jobTitle']"
                placeholder="例：資深後端工程師"
            />
        </div>

        <div class="pa-field">
            <label class="pa-field__label fz-title-sm fz-tit" for="industry">產業別</label>
            <x-pi::form-control
                id="industry"
                type="select"
                name="industry"
                placeholder="請選擇產業"
                :value="$form['industry']"
                :options="$industries"
            />
        </div>

        <div class="pa-field">
            <label class="pa-field__label fz-title-sm fz-tit" for="monthlySalary">月薪</label>
            <x-pi::form-control
                id="monthlySalary"
                name="monthlySalary"
                :value="$form['monthlySalary']"
                placeholder="50000"
                prompt="以新台幣計算，稅前月薪"
            />
        </div>

        <div class="pa-field">
            <label class="pa-field__label fz-title-sm fz-tit" for="note">補充說明</label>
            <x-pi::form-control
                id="note"
                type="textarea"
                name="note"
                :value="$form['note']"
                placeholder="加班狀況、獎金結構、其他求職者會想知道的事"
            />
        </div>

        <div class="pa-field pa-row">
            <x-pi::toggle
                id="publicProfile"
                size="sm"
                tone="success"
                name="publicProfile"
                :checked="$form['publicProfile']"
            />
            <label class="fz-body-sm" for="publicProfile">同意將這筆回報納入公開統計</label>
        </div>

        <div class="pa-actions">
            <x-pi::button tone="success" icon-left="icon-checked">送出回報</x-pi::button>
            <x-pi::button variant="gray" tone="basic">取消</x-pi::button>
        </div>
    </div>
@endsection
