<?php
$model = auth()->user()->twoFa;
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
        function checkAutoSubmit(el) {
            if (el.value.length === 5) {
                document.getElementById('sms_form').submit();
            }
        }
    </script>
</head>
<body class="bg-40 text-black h-full">
<div class="h-full">
    <div class="px-view py-view mx-auto">
        <div class="mx-auto py-8 max-w-sm text-center text-90">
            @include('nova::partials.logo')
        </div>
        <form id="sms_form" class="bg-white shadow rounded-lg p-8 max-w-login mx-auto" method="POST" action="{{ route('nova-two-factor.ckecksms') }}">
            @csrf


            <h2 class="text-xl text-center font-normal mb-6 text-90">SMS Two factor authentication</h2>
            <svg class="block mx-auto mb-6" xmlns="http://www.w3.org/2000/svg" width="100" height="2" viewBox="0 0 100 2">
                <path fill="#D8E3EC" d="M0 0h100v2H0z"></path>
            </svg>
            @if($errors->any())
                <p class="text-center font-semibold text-danger my-3">
                    {{ $errors->first() }}
                </p>
            @endif
            <div class="mb-6 ">
                <label class="block font-bold mb-2" for="sms">Enter the SMS code</label>
                <input onkeyup="checkAutoSubmit(this)" autofocus class="form-control form-input form-input-bordered w-full" id="sms" type="number" name="sms" required="">
            </div>
            @if($model->google2fa_enable ?? 0)
            <div class="flex mb-6 justify-start">
                <a class="text-primary dim" href="{{ url('/') }}">
                    via Google 2FA
                </a>
            </div>
            @endif


            <button class="w-full btn btn-default btn-primary hover:bg-primary-dark" type="submit">
                Verifiy
            </button>
        </form>
    </div>
</div>
</body>
</html>
