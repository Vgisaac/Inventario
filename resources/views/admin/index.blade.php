<x-layouts::app>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Admin Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg dark:bg-gray-800 dark:border-gray-700">
                <div
                    class="p-6 bg-white border-b border-gray-500 text-gray-900 dark:text-gray-100 dark:bg-gray-800 dark:border-gray-700">
                    {{ __('Vista de administrador!') }}
                </div>
            </div>
        </div>
    </div>
</x-layouts::app>
