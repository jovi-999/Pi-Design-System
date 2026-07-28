@piFragment([
    'target' => 'project-a:resources/views/members/index.blade.php',
    'slot' => 'member-list.filters',
    'host' => 'project-a/_hosts/members-index.html',
])

@piFixture($statuses, 'member-status')

{{--
    ⚠️ 元件缺口：設計系統沒有 filter-bar 這個元件，所以這裡用一個 wrapper div
    加 page-scoped 樣式排版（9 行，未超過 CLAUDE.md 第 3 條的 30 行上限）。

    這正是第 3 條要偵測的訊號：如果後續還有 3 個頁面重複寫這段，
    就該提報 filter-bar 元件缺口，而不是每頁各刻一次。
--}}
<style>
    .pa-filters {
        display: flex;
        align-items: flex-end;
        gap: 12px;
        padding: 16px 0;
    }
    .pa-filters > * { flex: 1; }
    .pa-filters > .pa-filters__actions { flex: none; }
</style>

<div class="pa-filters">
    <x-pi::form-control
        name="keyword"
        icon="icon-search"
        placeholder="姓名 · Email"
    />

    <x-pi::form-control
        type="select"
        name="status"
        placeholder="全部狀態"
        :options="$statuses"
    />

    <div class="pa-filters__actions">
        <x-pi::button tone="basic" icon-left="icon-search">篩選</x-pi::button>
    </div>
</div>
