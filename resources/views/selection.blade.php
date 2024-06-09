<?php
    $model = \Illuminate\Support\Facades\Auth::user()->twoFa->confirmed ?? 0;
?>
    <!DOCTYPE html>
<html lang="en" class="h-full font-sans">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ Nova::name() }}</title>

    <!-- Styles -->
    <link rel="stylesheet" href="{{ mix('app.css', 'vendor/nova') }}">

    <style>
        body {
            background: linear-gradient(45deg, rgb(15, 72, 138), rgb(0, 155, 222));
        }
    </style>
    <script>

    </script>
</head>
<body class="bg-40 text-black h-full">
<div class="h-full">
    <div class="px-view py-view mx-auto">
        <div class="mx-auto py-8 max-w-sm text-center text-90">
            @include('nova::partials.logo')
        </div>
        @if($model === 1)
            <div class="mx-auto py-8 max-w-sm text-center text-90">
                <label style="color: black" class="block font-bold mb-2" for="google">Google 2FA</label>
                <button id="google" class="btn btn-default btn-primary hover:bg-primary-dark"
                        onclick="javascript:window.location.href='{{route('nova-two-factor.setgoogle')}}'">
                    Enable
                </button>
            </div>
            <div class="mx-auto py-8 max-w-sm text-center text-90">
                <label style="color: black" class="block font-bold mb-2">Or</label>
            </div>
        @endif

        <div class="mx-auto py-8 max-w-sm text-center text-90">
            <label style="color: black" class="block font-bold mb-2" for="sms">SMS 2FA</label>
            <button id="sms" class="btn btn-default btn-primary hover:bg-primary-dark"
                    onclick="javascript:window.location.href='{{route('nova-two-factor.setsms')}}'">
                Enable
            </button>
        </div>

    </div>
</div>
</body>
</html>
