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
            if (el.value.length === 6) {
                document.getElementById('authenticate_form').submit();
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
        <form class="bg-white shadow rounded-lg p-8 max-w-login mx-auto" method="POST" action="{{ route('nova-two-factor.recover') }}">
            @csrf


            <h2 class="text-2xl text-center font-normal mb-6 text-90">Recover from 2FA</h2>
            <svg class="block mx-auto mb-6" xmlns="http://www.w3.org/2000/svg" width="100" height="2" viewBox="0 0 100 2">
                <path fill="#D8E3EC" d="M0 0h100v2H0z"></path>
            </svg>
            @if($errors->any())
            <p class="text-center font-semibold text-danger my-3">
                {{ $errors->first() }}
            </p>
            @endif
            <div class="mb-6 ">
                <label class="block font-bold mb-2" for="password">Recovery Code</label>
                <input class="form-control form-input form-input-bordered w-full" id="password" type="text" name="recovery_code" required>
            </div>

            <div class="flex mb-6">
                <div class="ml-auto">
                    <a class="text-primary dim font-bold no-underline" href="{{ config('nova.path') }}">
                        Use OTP code
                    </a>
                </div>
            </div>

            <button class="w-full btn btn-default btn-primary hover:bg-primary-dark" type="submit">
                Recover my account
            </button>
        </form>
    </div>
</div>
</body>
</html>
