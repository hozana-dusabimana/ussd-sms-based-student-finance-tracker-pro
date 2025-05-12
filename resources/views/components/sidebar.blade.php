

<div style='margin-left:5%' x-data="{ sidebarOpen: false }" class="relative">
    <!-- Sidebar Toggle Button -->
    <button @click="sidebarOpen = !sidebarOpen" class="fixed top-4 left-4 z-50">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"></path>
        </svg>
    </button>

    <!-- Backdrop -->
    <div x-show="sidebarOpen" 
         @click="sidebarOpen = false" 
         class="fixed inset-0 z-20 bg-black bg-opacity-50 transition-opacity lg:hidden"></div>

    <!-- Sidebar -->
    <div x-show="sidebarOpen" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="-translate-x-full"
         x-transition:enter-end="translate-x-0"
         x-transition:leave="transition ease-in duration-300"
         x-transition:leave-start="translate-x-0"
         x-transition:leave-end="-translate-x-full"
         class="fixed inset-y-0 left-0 z-30 w-64 transform overflow-y-auto bg-white border-r border-gray-200 lg:translate-x-0 lg:static lg:inset-0">
        
        <div class="flex items-center justify-center h-16 bg-white border-b border-gray-200">
            <h2 class="text-2xl font-semibold text-gray-800">Student Finance Tracker</h2>
        </div>

        <nav class="mt-6">
            <div class="px-4 py-2">
                <a href="{{ route('dashboard') }}" 
                   class="flex items-center px-4 py-2 text-gray-700 bg-gray-100 rounded-lg {{ request()->routeIs('dashboard') ? 'bg-gray-200' : '' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                    </svg>
                    <span class="mx-3">Dashboard</span>
                </a>
            </div>

            <div class="px-4 py-2">
                <a href="{{ route('transactions.index') }}" 
                   class="flex items-center px-4 py-2 text-gray-700 rounded-lg {{ request()->routeIs('transactions.*') ? 'bg-gray-200' : '' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span class="mx-3">Transactions</span>
                </a>
            </div>

            <div class="px-4 py-2">
                <a href="{{ route('categories.index') }}" 
                   class="flex items-center px-4 py-2 text-gray-700 rounded-lg {{ request()->routeIs('categories.*') ? 'bg-gray-200' : '' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                    </svg>
                    <span class="mx-3">Categories</span>
                </a>
            </div>

            <div class="px-4 py-2">
                <a href="{{ route('reports.index') }}" 
                   class="flex items-center px-4 py-2 text-gray-700 rounded-lg {{ request()->routeIs('reports.*') ? 'bg-gray-200' : '' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <span class="mx-3">Reports</span>
                </a>
            </div>
        </nav>
    </div>
</div> 