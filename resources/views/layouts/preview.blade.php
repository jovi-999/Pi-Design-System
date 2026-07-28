{{--
    Pi DS — Preview layout

    Prototype 用 `@extends('pi::layouts.preview')` 掛上這層殼。
    交接進專案時，這一行換成專案自己的 layout，body 一個字不用改
    —— 這是 prototype「零轉換」的關鍵，所以這層殼刻意只做三件事：
    載樣式、載 icon、開一個 content section。不放任何版面決策。

    @vite 指向的是 preview app 的 manifest。這支 layout 雖然放在套件裡
    （spec 的 namespace 約定），但實際上只會在 preview app 內被 render；
    專案端不會用到它。
--}}
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Pi DS Preview')</title>

    @vite('resources/scss/app.scss')

    {{-- symicon 的 icon-* class；不走 Vite，因為 url() 是 /fonts 絕對路徑 --}}
    <link rel="stylesheet" href="/assets/symicon.css">

    @stack('head')
</head>
<body>
    @yield('content')

    @stack('scripts')
</body>
</html>
