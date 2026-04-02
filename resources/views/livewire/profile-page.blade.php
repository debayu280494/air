<div class="max-w-3xl mx-auto space-y-6">

    <!-- TITLE -->
    <h1 class="text-2xl font-bold">Profile</h1>

    <!-- SUCCESS ALERT -->
    @if (session()->has('success'))
        <div class="p-3 bg-green-100 text-green-700 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    <!-- ================= PROFILE ================= -->
    <div class="bg-white shadow rounded-xl p-6">

        <!-- AVATAR -->
        <div class="flex items-center gap-4 mb-6">
            <div class="w-14 h-14 rounded-full bg-blue-500 text-white flex items-center justify-center text-xl font-bold">
                {{ strtoupper(substr($name, 0, 1)) }}
            </div>

            <div>
                <div class="font-semibold text-lg">{{ $name }}</div>
                <div class="text-sm text-gray-500">{{ $email }}</div>
            </div>
        </div>

        <!-- FORM -->
        <form wire:submit.prevent="updateProfile" class="space-y-4">

            <div>
                <label class="text-sm text-gray-600">Nama</label>
                <input type="text" wire:model="name"
                    class="w-full mt-1 border rounded-lg px-3 py-2 focus:ring focus:ring-blue-200">
                @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="text-sm text-gray-600">Email</label>
                <input type="email" wire:model="email"
                    class="w-full mt-1 border rounded-lg px-3 py-2 focus:ring focus:ring-blue-200">
                @error('email') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            <div class="flex justify-end">
                <button
                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg shadow">
                    Simpan Perubahan
                </button>
            </div>

        </form>

    </div>

    <!-- ================= PASSWORD ================= -->
    <div class="bg-white shadow rounded-xl p-6">

        <h2 class="text-lg font-semibold mb-4">Ganti Password</h2>

        <form wire:submit.prevent="updatePassword" class="space-y-4">

            <div>
                <label class="text-sm text-gray-600">Password Lama</label>
                <input type="password" wire:model="current_password"
                    class="w-full mt-1 border rounded-lg px-3 py-2 focus:ring focus:ring-blue-200">
                @error('current_password') 
                    <span class="text-red-500 text-xs">{{ $message }}</span> 
                @enderror
            </div>

            <div>
                <label class="text-sm text-gray-600">Password Baru</label>
                <input type="password" wire:model="new_password"
                    class="w-full mt-1 border rounded-lg px-3 py-2 focus:ring focus:ring-blue-200">
                @error('new_password') 
                    <span class="text-red-500 text-xs">{{ $message }}</span> 
                @enderror
            </div>

            <div>
                <label class="text-sm text-gray-600">Konfirmasi Password</label>
                <input type="password" wire:model="new_password_confirmation"
                    class="w-full mt-1 border rounded-lg px-3 py-2 focus:ring focus:ring-blue-200">
            </div>

            <div class="flex justify-end">
                <button
                    class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg shadow">
                    Update Password
                </button>
            </div>

        </form>

    </div>

</div>