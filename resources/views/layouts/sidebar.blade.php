<aside class="w-64 bg-white shadow-md min-h-screen flex flex-col">

    <!-- LOGO -->
    <div class="p-4 border-b">
        <a href="{{ route('dashboard') }}">
            <img src="{{ asset('images/sampul.jpg') }}" {{ $attributes }}>
        </a>
    </div>

    <!-- MENU -->
    <nav class="flex-1 p-4 space-y-2">

        <a href="{{ route('dashboard') }}"
           class="block px-4 py-2 rounded-lg hover:bg-blue-50 
           {{ request()->routeIs('dashboard') ? 'bg-blue-100 text-blue-600 font-semibold' : '' }}">
            Dashboard
        </a>

        <a href="{{ route('layanan') }}"
           class="block px-4 py-2 rounded-lg hover:bg-blue-50 
           {{ request()->routeIs('layanan') ? 'bg-blue-100 text-blue-600 font-semibold' : '' }}">
            Layanan
        </a>

        <a href="{{ route('pelanggan') }}"
           class="block px-4 py-2 rounded-lg hover:bg-blue-50 
           {{ request()->routeIs('pelanggan') ? 'bg-blue-100 text-blue-600 font-semibold' : '' }}">
            Pelanggan
        </a>

        <a href="{{ route('pemakaian') }}"
           class="block px-4 py-2 rounded-lg hover:bg-blue-50 
           {{ request()->routeIs('pemakaian') ? 'bg-blue-100 text-blue-600 font-semibold' : '' }}">
            Pemakaian
        </a>

        <a href="{{ route('tagihan') }}"
           class="block px-4 py-2 rounded-lg hover:bg-blue-50 
           {{ request()->routeIs('tagihan') ? 'bg-blue-100 text-blue-600 font-semibold' : '' }}">
            Tagihan
        </a>

    </nav>

    <!-- USER -->
    <div class="p-4 border-t">
        
        <!-- USER CARD -->
        <div class="flex items-center gap-3 mb-4">
            
            <!-- AVATAR -->
            <div class="w-10 h-10 rounded-full bg-blue-500 text-white flex items-center justify-center font-bold">
                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
            </div>

            <!-- INFO -->
            <div class="flex-1">
                <div class="text-sm font-semibold text-gray-800 leading-tight">
                    {{ Auth::user()->name }}
                </div>
                <div class="text-xs text-gray-500">
                    {{ Auth::user()->email }}
                </div>
            </div>

        </div>

        <!-- ACTION BUTTON -->
        <div class="space-y-2">

            <!-- PROFILE -->
            <a href="{{ route('profile.edit') }}"
            class="flex items-center gap-2 px-3 py-2 text-sm rounded-lg hover:bg-gray-100 transition">
                
                <!-- ICON -->
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M5.121 17.804A9 9 0 1118.879 6.196 9 9 0 015.12 17.804z" />
                </svg>

                Profile
            </a>

            <!-- LOGOUT -->
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button
                    class="w-full flex items-center gap-2 px-3 py-2 text-sm rounded-lg text-red-500 hover:bg-red-50 transition">
                    
                    <!-- ICON -->
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 16l4-4m0 0l-4-4m4 4H7" />
                    </svg>

                    Logout
                </button>
            </form>

        </div>

    </div>

</aside>