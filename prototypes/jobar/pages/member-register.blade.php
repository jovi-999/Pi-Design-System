{{--
    Page prototype：會員註冊。

    本次需求（PM）：註冊表單新增「同意隱私權政策」勾選，表單下方新增一個
    CTA（標題 + 內文 + 一顆按鈕）。表單本身的欄位是為了讓 CTA 與勾選看得到
    真實脈絡而補齊的最小集合。

    交接進專案時只動兩行：
      1. 刪掉下面兩行 @piFixture —— 資料改由 controller 傳入
      2. @extends('pi::layouts.preview') 換成專案自己的 layout

    @section('content') 內的 body 一個字都不用改。
    對應 handoff：.scratch/member-register/{frontend,backend}-handoff.md
--}}
@piFixture($form, 'member-register-form')
@piFixture($cta, 'member-register-cta')

@extends('pi::layouts.preview')

@section('title', '會員註冊')

@section('content')
    {{--
        ⚠️ 元件缺口（已提報，非新提案）：設計系統沒有「欄位標籤」元件 ——
        _form.scss 內沒有任何 label 相關 class。這裡沿用 salary-report 的疊法
        （<label for> + fz-title-sm fz-tit），field-label 已在該頁的 handoff 提報。

        自訂樣式 6 行，未超過 CLAUDE.md 第 3 條的 30 行上限。
    --}}
    <style>
        .jb-form { max-width: 480px; margin: 40px auto; padding: 0 24px; }
        .jb-field { margin-bottom: 20px; }
        .jb-field__label { margin-bottom: 6px; }
        .jb-consent { margin: 24px 0; }
        .jb-actions { display: flex; gap: 12px; }
        .jb-cta { margin-top: 40px; }
    </style>

    @php
        // 必填未填的 hint：有 error 就給 state="invalid" + feedback（_form.scss 既有機制，
        // .is-invalid 掛在 group 上）。錯誤內容來自 fixture，blade 不寫死訊息。
        //
        // 變數刻意叫 $fieldErrors 而不是 $errors —— Laravel 的 ShareErrorsFromSession
        // middleware 會把 $errors（ViewErrorBag）塞進每個 view，同名會被蓋掉。
        $fieldErrors = $form['errors'] ?? [];
    @endphp

    <div class="jb-form">
        <h1 class="fz-headline-sm fz-tit">建立帳號</h1>

        <div class="jb-field">
            <label class="jb-field__label fz-title-sm fz-tit" for="name">姓名</label>
            <x-pi::form-control
                id="name"
                name="name"
                :value="$form['name']"
                placeholder="例：陳怡君"
                :state="isset($fieldErrors['name']) ? 'invalid' : null"
                :feedback="$fieldErrors['name'] ?? null"
            />
        </div>

        <div class="jb-field">
            <label class="jb-field__label fz-title-sm fz-tit" for="email">Email</label>
            <x-pi::form-control
                id="email"
                type="email"
                name="email"
                :value="$form['email']"
                placeholder="you@example.com"
                prompt="註冊後會寄一封驗證信到這個信箱"
                :state="isset($fieldErrors['email']) ? 'invalid' : null"
                :feedback="$fieldErrors['email'] ?? null"
            />
        </div>

        <div class="jb-field">
            <label class="jb-field__label fz-title-sm fz-tit" for="password">密碼</label>
            <x-pi::form-control
                id="password"
                type="password"
                name="password"
                placeholder="至少 8 個字元"
                :state="isset($fieldErrors['password']) ? 'invalid' : null"
                :feedback="$fieldErrors['password'] ?? null"
            />
        </div>

        {{--
            同意隱私權政策。checkbox 的標籤是 slot（input 包在 label 內），
            所以不需要另外的 <label for>。

            🔧 待補：「隱私權政策」要做成可點連結時，設計系統沒有 inline link
            樣式（已 grep 確認 resources/scss 內無對應 utility），會變成裸 <a>。
            這裡先用純文字，連結樣式待前端決定，不自創 class。
        --}}
        <div class="jb-consent">
            <x-pi::checkbox
                tone="success"
                name="agreePrivacy"
                value="1"
                :checked="$form['agreePrivacy']"
            >我已閱讀並同意隱私權政策</x-pi::checkbox>
        </div>

        <div class="jb-actions">
            <x-pi::button tone="success" icon-left="icon-checked">註冊</x-pi::button>
        </div>

        {{--
            表單下方 CTA。PM 選定用 x-pi::callout（命中現有元件，零自創）——
            長相是「左側圓形 icon + 標題 + 內文 + 右側按鈕」的橫向提示條，
            不是置中大 CTA。文案全部來自 fixture。
        --}}
        <div class="jb-cta">
            <x-pi::callout
                :tone="$cta['tone']"
                :icon="$cta['icon']"
                :title="$cta['title']"
            >
                {{ $cta['body'] }}
                <x-slot:action>
                    <x-pi::button
                        as="a"
                        :href="$cta['action']['href']"
                        size="sm"
                        variant="outline"
                        tone="success"
                    >{{ $cta['action']['label'] }}</x-pi::button>
                </x-slot:action>
            </x-pi::callout>
        </div>
    </div>
@endsection
