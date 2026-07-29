{{--
    Token 表格。

    「值」欄留空，由 @push('scripts') 的 JS 用 getComputedStyle 填 ——
    那是瀏覽器實際解析後的值，跟左邊預覽格必然同源。靜態解析 SCSS 拿不到
    （值在原始碼裡是 Sass 插值），解析 dist/ 又要求先跑 build。

    $group 需要 label / kind / tokens 三個 key。
--}}
@php
    /** kind → 預覽格的畫法。沒有對應的就不畫預覽（例如 motion）。 */
    $preview = function (string $kind, string $name): ?string {
        return match ($kind) {
            'color' => '<span class="pv-swatch" style="background: var(' . $name . ');"></span>',
            'radius' => '<span class="pv-swatch pv-swatch--radius" style="border-radius: var(' . $name . ');"></span>',
            'shadow' => '<span class="pv-swatch pv-swatch--shadow" style="box-shadow: var(' . $name . ');"></span>',
            'length' => '<span class="pv-bar" style="width: var(' . $name . ');"></span>',
            'text' => '<span class="pv-sample" style="font-family: var(' . $name . ');">Ag 字體</span>',
            default => null,
        };
    };
@endphp

<table class="pv-props pv-tokens fz-body-sm">
    <thead>
        <tr>
            <th style="width: 96px;">預覽</th>
            <th>Token</th>
            <th style="width: 30%;">值</th>
            <th>說明</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($group['tokens'] as $token)
            <tr data-search="{{ $token['name'] }} {{ $token['note'] }}">
                <td>{!! $preview($group['kind'], $token['name']) !!}</td>
                <td>
                    <button type="button" class="pv-copy" data-copy="var({{ $token['name'] }})"
                            title="點擊複製 var({{ $token['name'] }})">{{ $token['name'] }}</button>
                </td>
                <td><code data-token="{{ $token['name'] }}">…</code></td>
                <td>{{ $token['note'] }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
