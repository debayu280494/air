<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Alpine -->
    <!-- <script src="//unpkg.com/alpinejs" defer></script> -->

    <!-- SweetAlert -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    @livewireStyles
</head>

<body class="font-sans antialiased">
<div class="min-h-screen flex bg-gray-100">

    <!-- SIDEBAR -->
    @include('layouts.sidebar')

    <!-- CONTENT -->
    <div class="flex-1 flex flex-col">

        @isset($header)
            <header class="bg-white shadow">
                <div class="max-w-7xl mx-auto py-6 px-4">
                    {{ $header }}
                </div>
            </header>
        @endisset

        <!-- MAIN -->
        <main class="p-6 flex-1">
            {{ $slot }}
        </main>

        <!-- FOOTER -->
        <footer class="bg-white border-t py-4 text-center text-sm text-gray-400">
            © {{ date('Y') }} KTS Monitoring — All rights reserved - Made By Karya Satria Advertising
        </footer>

    </div>

</div>

@livewireScripts

<!-- ================= SWEETALERT DELETE ================= -->
<script>
    document.addEventListener('livewire:init', () => {

    Livewire.on('show-delete-confirm', ({ id }) => {

        Swal.fire({
            title: 'Yakin hapus?',
            text: "Data tidak bisa dikembalikan!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, hapus!'
        }).then((result) => {

            if (result.isConfirmed) {
                Livewire.dispatch('delete-confirmed', { id: id });
            }

        });

    });

});
</script>

</body>
</html>