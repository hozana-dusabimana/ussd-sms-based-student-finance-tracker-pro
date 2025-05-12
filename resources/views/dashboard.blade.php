<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
           <div class="p-6 text-gray-900 text-center">
    <div class="flex flex-col items-center justify-center space-y-3">
   

        <!-- Welcome Message -->
        <h2 class="text-xl font-semibold">
            {{ __("Most welcome back, ") }} {{ Auth::user()->name }}
        </h2>

        <!-- Instruction Text -->
        <p class="text-sm text-gray-600">
            {{ __("Click the menu bar to continue your financial journey") }}
        </p>
    </div>
</div>

            </div>
        </div>
    </div>
</x-app-layout>
