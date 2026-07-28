{{--
    Page prototype：會員列表。

    交接進專案時只動兩行：
      1. 刪掉下面兩行 @piFixture —— 資料改由 controller 傳入
      2. @extends('pi::layouts.preview') 換成專案自己的 layout

    @section('content') 內的 body 一個字都不用改 —— 因為專案與 preview
    吃的是同一個版本的元件套件。
--}}
@piFixture($members, 'member-list')
@piFixture($statuses, 'member-status')

@extends('pi::layouts.preview')

@section('title', '會員列表')

@section('content')
    <div style="max-width: 960px; margin: 40px auto; padding: 0 24px;">
        <h1 class="fz-headline-sm fz-tit">會員列表</h1>

        <x-pi::content-switcher
            tone="basic"
            :items="[
                ['label' => '全部', 'count' => count($members), 'active' => true],
                ['label' => '啟用中'],
                ['label' => '已停權'],
            ]"
        />

        {{--
            Slot marker。用 HTML 註解而不是 blade 註解（`{{-- --}}`）：
            blade 註解在 render 時會被移除，抓回來的 host 快照裡就沒有錨點，
            fragment 也就無處可插。HTML 註解會留在 rendered HTML 裡。

            這一行同時是三件事的錨點：
              1. scripts/apply.php 插入 fragment 的位置
              2. scripts/fetch-host.php 抓回的快照裡，fragment 的注入點
              3. 「此位置有 prototype 在跑」的標記
        --}}
        <!-- @pi-slot: member-list.filters -->

        <div style="padding: 8px 0;">
            @foreach ($members as $member)
                <x-pi::notification
                    :tone="$member['status'] === 'suspended' ? 'danger' : ($member['status'] === 'pending' ? 'warning' : 'success')"
                    icon="icon-user"
                    :title="$member['name']"
                    :time="$member['joinedAt']"
                >
                    {{ $member['email'] }} ·
                    {{ collect($statuses)->firstWhere('value', $member['status'])['label'] ?? $member['status'] }}
                </x-pi::notification>
            @endforeach
        </div>

        <x-pi::pagination :current="1" :last="3" />
    </div>
@endsection
