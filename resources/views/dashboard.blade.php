<x-app-layout>
    <div class="p-6">
        <h1 class="text-2xl font-bold">Dashboard</h1>

        <p>Selamat datang, {{ auth()->user()->name }}</p>
    </div>
</x-app-layout>