<!DOCTYPE html>
<html
    lang="{{ str_replace('_', '-', app()->getLocale()) }}"
>
    <head>
        <meta charset="utf-8">

        <meta
            name="viewport"
            content="width=device-width, initial-scale=1"
        >

        <meta
            name="csrf-token"
            content="{{ csrf_token() }}"
        >

        <meta
            name="theme-color"
            content="#1e3a8a"
        >

        <meta
            name="application-name"
            content="BEMS"
        >

        <meta
            name="apple-mobile-web-app-capable"
            content="yes"
        >

        <meta
            name="apple-mobile-web-app-status-bar-style"
            content="default"
        >

        <meta
            name="apple-mobile-web-app-title"
            content="BEMS"
        >

        <link
            rel="manifest"
            href="/manifest.webmanifest"
        >

        <link
            rel="icon"
            type="image/png"
            href="/images/pwa-192.png"
        >

        <link
            rel="apple-touch-icon"
            href="/images/pwa-192.png"
        >

        <title inertia>
            {{ config('app.name', 'BEMS') }}
        </title>

        @routes

        @vite([
            'resources/js/app.js',
            "resources/js/Pages/{$page['component']}.vue"
        ])

        @inertiaHead
    </head>

    <body class="font-sans antialiased">
        @inertia
    </body>
</html>