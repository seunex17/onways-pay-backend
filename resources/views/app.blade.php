<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="onways-light">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#fff8f7">
    <link rel="icon" type="image/png" href="{{ asset('ico/favicon-96x96.png') }}" sizes="96x96" />
    <link rel="icon" type="image/svg+xml" href="{{ asset('ico/favicon.svg') }}" />
    <link rel="shortcut icon" href="{{ asset('ico/favicon.ico') }}" />
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('ico/apple-touch-icon.png') }}" />
    <link rel="manifest" href="{{ asset('ico/site.webmanifest') }}" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <x-inertia::head />
</head>
<body class="min-w-0 overflow-x-hidden bg-base-100 text-base-content antialiased">
    <x-inertia::app />
</body>
</html>
