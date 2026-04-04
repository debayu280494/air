<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-100 flex items-center justify-center h-screen">

<div class="bg-white p-6 rounded shadow w-96">
    <h2 class="text-xl mb-4 font-bold text-center">Login</h2>

    @if (session('status'))
        <div class="mb-3 text-green-600 text-sm">
            {{ session('status') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-3 text-red-600 text-sm">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <input type="email" name="email" placeholder="Email"
            class="w-full border p-2 mb-3" required>

        <input type="password" name="password" placeholder="Password"
            class="w-full border p-2 mb-3" required>

        <button class="bg-blue-600 text-white px-4 py-2 w-full">
            Login
        </button>
    </form>

    <div class="mt-4 text-center">
        <a href="{{ route('register') }}" class="text-blue-500 underline">
            Belum punya akun? Daftar
        </a>
    </div>
</div>

</body>
</html>