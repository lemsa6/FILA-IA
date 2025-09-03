<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        
        <!-- Tailwind CSS via CDN -->
        <script src="https://cdn.tailwindcss.com"></script>

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        
        <!-- Alpine.js via CDN -->
        <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-gradient-to-br from-gray-50 to-white">
            <!-- Navigation -->
            <nav class="bg-white border-b border-gray-200 shadow-sm">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex justify-between h-16">
                        <div class="flex">
                            <!-- Logo -->
                            <div class="shrink-0 flex items-center">
                                <a href="{{ route('dashboard') }}" class="flex items-center space-x-2">
                                    <div class="w-8 h-8 bg-black rounded-lg flex items-center justify-center">
                                        <span class="text-white font-bold text-lg">F</span>
                                    </div>
                                    <span class="text-xl font-bold text-black">FILA-IA</span>
                                </a>
                            </div>

                            <!-- Navigation Links -->
                            <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex sm:items-center">
                                <a href="{{ route('dashboard') }}" class="px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('dashboard') ? 'bg-gray-100 text-gray-900' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                                    Dashboard
                                </a>
                                
                                <!-- Menus de Planos e Cobrança removidos - Sistema simplificado v2.4.0 -->
                                
                                <!-- Menu Tokens com Dropdown -->
                                <div class="relative" x-data="{ open: false }">
                                    <button @click="open = !open" class="px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('admin.token-usage.*') ? 'bg-gray-100 text-gray-900' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }} flex items-center">
                                        Tokens
                                        <svg class="ml-1 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </button>
                                    
                                    <!-- Dropdown Menu -->
                                    <div x-show="open" @click.away="open = false" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="transform opacity-0 scale-95" x-transition:enter-end="transform opacity-100 scale-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="transform opacity-100 scale-100" x-transition:leave-end="transform opacity-0 scale-95" class="absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 py-1 z-50">
                                        <a href="{{ route('admin.token-usage.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 {{ request()->routeIs('admin.token-usage.*') ? 'bg-gray-100' : '' }}">
                                            Logs de Uso
                                        </a>
                                        <a href="{{ route('admin.token-usage.stats') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                            Estatísticas de Uso
                                        </a>
                                        <a href="{{ route('admin.token-usage.alerts') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                            Alertas de Uso
                                        </a>
                                    </div>
                                </div>
                                
                                <a href="{{ route('admin.apikeys.index') }}" class="px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('admin.apikeys.*') ? 'bg-gray-100 text-gray-900' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                                    Chaves de API
                                </a>
                                <a href="{{ url('/horizon') }}" target="_blank" class="px-3 py-2 rounded-md text-sm font-medium text-gray-600 hover:bg-gray-50 hover:text-gray-900">
                                    Horizon
                                </a>
                                
                                <!-- Menu Monitoramento com Dropdown -->
                                <div class="relative" x-data="{ open: false }">
                                    <button @click="open = !open" class="px-3 py-2 rounded-md text-sm font-medium text-gray-600 hover:bg-gray-50 hover:text-gray-900 flex items-center">
                                        Monitoramento
                                        <svg class="ml-1 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </button>
                                    
                                    <!-- Dropdown Menu -->
                                    <div x-show="open" @click.away="open = false" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="transform opacity-0 scale-95" x-transition:enter-end="transform opacity-100 scale-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="transform opacity-100 scale-100" x-transition:leave-end="transform opacity-0 scale-95" class="absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 py-1 z-50">
                                        <a href="{{ route('admin.test-ai.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 {{ request()->routeIs('admin.test-ai.*') ? 'bg-gray-100' : '' }}">
                                            🧪 Teste de IA
                                        </a>
                                        <a href="{{ route('admin.routines.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 {{ request()->routeIs('admin.routines.*') ? 'bg-gray-100' : '' }}">
                                            🔄 Rotinas do Sistema
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Settings Dropdown -->
                        <div class="hidden sm:flex sm:items-center sm:ml-6">
                            <x-dropdown align="right" width="48">
                                <x-slot name="trigger">
                                    <button class="flex items-center text-sm font-medium text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none focus:text-gray-700 focus:border-gray-300 transition duration-150 ease-in-out">
                                        <div class="flex items-center space-x-2">
                                            <div class="w-8 h-8 bg-yellow-400 rounded-full flex items-center justify-center">
                                                <span class="text-black font-semibold text-sm">{{ substr(Auth::user()->name, 0, 1) }}</span>
                                            </div>
                                            <span class="text-gray-700">{{ Auth::user()->name }}</span>
                                        </div>
                                        <div class="ml-1">
                                            <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                    </button>
                                </x-slot>

                                <x-slot name="content">
                                    <x-dropdown-link href="{{ route('profile.edit') }}" class="dropdown-link-primary">
                                        {{ __('Perfil') }}
                                    </x-dropdown-link>

                                    <!-- Authentication -->
                                    <form method="POST" action="{{ route('logout') }}" x-data>
                                        @csrf
                                        <x-dropdown-link href="{{ route('logout') }}" class="dropdown-link-primary"
                                                @click.prevent="$root.submit();">
                                            {{ __('Sair') }}
                                        </x-dropdown-link>
                                    </form>
                                </x-slot>
                            </x-dropdown>
                        </div>

                        <!-- Hamburger -->
                        <div class="-mr-2 flex items-center sm:hidden">
                            <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                                <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                    <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                    <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Responsive Navigation Menu -->
                <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
                    <div class="pt-2 pb-3 space-y-1">
                        <x-responsive-nav-link href="{{ route('dashboard') }}" :active="request()->routeIs('dashboard')" class="responsive-nav-link-primary">
                            {{ __('Dashboard') }}
                        </x-responsive-nav-link>
                        <x-responsive-nav-link href="{{ route('admin.apikeys.index') }}" :active="request()->routeIs('admin.apikeys.*')" class="responsive-nav-link-primary">
                            {{ __('Chaves de API') }}
                        </x-responsive-nav-link>
                        <x-responsive-nav-link href="/horizon" target="_blank" class="responsive-nav-link-primary">
                            {{ __('Horizon') }}
                        </x-responsive-nav-link>
                        
                        <!-- Monitoramento no Mobile -->
                        <div class="border-l-4 border-transparent pl-3 pr-4 py-2">
                            <div class="text-base font-medium text-gray-600 mb-2">{{ __('Monitoramento') }}</div>
                            <div class="pl-4 space-y-1">
                                <a href="{{ route('admin.test-ai.index') }}" class="block py-1 text-sm text-gray-600 hover:text-gray-800 {{ request()->routeIs('admin.test-ai.*') ? 'text-indigo-700' : '' }}">
                                    🧪 Teste de IA
                                </a>
                                <a href="{{ route('admin.routines.index') }}" class="block py-1 text-sm text-gray-600 hover:text-gray-800 {{ request()->routeIs('admin.routines.*') ? 'text-indigo-700' : '' }}">
                                    🔄 Rotinas do Sistema
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Responsive Settings Options -->
                    <div class="pt-4 pb-1 border-t border-gray-200">
                        <div class="px-4">
                            <div class="font-medium text-base text-gray-800">{{ Auth::user()->name }}</div>
                            <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
                        </div>

                        <div class="mt-3 space-y-1">
                            <x-responsive-nav-link href="{{ route('profile.edit') }}" class="responsive-nav-link-primary">
                                {{ __('Perfil') }}
                            </x-responsive-nav-link>

                            <!-- Authentication -->
                            <form method="POST" action="{{ route('logout') }}" x-data>
                                @csrf
                                <x-responsive-nav-link href="{{ route('logout') }}" class="responsive-nav-link-primary"
                                        @click.prevent="$root.submit();">
                                    {{ __('Sair') }}
                                </x-responsive-nav-link>
                            </form>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Page Heading -->
            @if (isset($header))
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    {{ $header }}
                </div>
            @endif

            <!-- Page Content -->
            <main>
                @yield('content')
            </main>
        </div>
    </body>
</html>
