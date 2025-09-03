<nav class="bg-white border-b border-gray-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex items-center">
                <!-- Logo -->
                <div class="flex-shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}" class="text-xl font-bold text-gray-800">
                        FILA-IA
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="ml-10 flex items-center space-x-4">
                    <a href="{{ route('dashboard') }}" class="px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('dashboard') ? 'bg-gray-100 text-gray-900' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                        Dashboard
                    </a>
                    
                    <!-- Menus de Planos e Cobrança removidos - Sistema simplificado v2.4.0 -->
                    
                    <!-- Menu Tokens com Dropdown -->
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" class="px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('admin.token-usage.*') ? 'bg-gray-100 text-gray-900' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }} flex items-center">
                            🧮 Tokens
                            <svg class="ml-1 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        
                        <!-- Dropdown Menu -->
                        <div x-show="open" @click.away="open = false" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="transform opacity-0 scale-95" x-transition:enter-end="transform opacity-100 scale-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="transform opacity-100 scale-100" x-transition:leave-end="transform opacity-0 scale-95" class="absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 py-1 z-50">
                            <a href="{{ route('admin.token-usage.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 {{ request()->routeIs('admin.token-usage.*') ? 'bg-gray-100' : '' }}">
                                📝 Logs de Uso
                            </a>
                            <a href="{{ route('admin.token-usage.stats') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                📊 Estatísticas de Uso
                            </a>
                            <a href="{{ route('admin.token-usage.alerts') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                🚨 Alertas de Uso
                            </a>
                        </div>
                    </div>
                    
                    <a href="{{ route('admin.apikeys.index') }}" class="px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('admin.apikeys.*') ? 'bg-gray-100 text-gray-900' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                        🔑 Chaves de API
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
            <div class="flex items-center">
                <div class="ml-3 relative">
                    <div>
                        <button type="button" class="flex items-center max-w-xs bg-white rounded-full focus:outline-none" id="user-menu-button" aria-expanded="false" aria-haspopup="true" onclick="toggleDropdown()">
                            <span class="sr-only">Open user menu</span>
                            <span class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                                {{ Auth::user()->name }}
                                <svg class="ml-2 -mr-0.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </span>
                        </button>
                    </div>

                    <!-- Dropdown menu, show/hide based on menu state -->
                    <div id="user-dropdown" class="hidden origin-top-right absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 py-1" role="menu" aria-orientation="vertical" aria-labelledby="user-menu-button" tabindex="-1">
                        <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem">Perfil</a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem">Sair</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Mobile menu button -->
            <div class="flex items-center sm:hidden">
                <button type="button" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-indigo-500" aria-controls="mobile-menu" aria-expanded="false" onclick="toggleMobileMenu()">
                    <span class="sr-only">Open main menu</span>
                    <svg id="icon-menu" class="block h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                    <svg id="icon-close" class="hidden h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Mobile menu, show/hide based on menu state -->
    <div class="hidden sm:hidden" id="mobile-menu">
        <div class="pt-2 pb-3 space-y-1">
            <a href="{{ route('dashboard') }}" class="block pl-3 pr-4 py-2 border-l-4 {{ request()->routeIs('dashboard') ? 'border-indigo-500 text-indigo-700 bg-indigo-50' : 'border-transparent text-gray-600 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-800' }} text-base font-medium">Dashboard</a>
            
            <!-- Menus mobile de Planos e Cobrança removidos - Sistema simplificado v2.4.0 -->
            
            <!-- Tokens no Mobile -->
            <div class="border-l-4 border-transparent pl-3 pr-4 py-2">
                <div class="text-base font-medium text-gray-600 mb-2">🧮 Tokens</div>
                <div class="pl-4 space-y-1">
                    <a href="{{ route('admin.token-usage.index') }}" class="block py-1 text-sm text-gray-600 hover:text-gray-800 {{ request()->routeIs('admin.token-usage.*') ? 'text-indigo-700' : '' }}">
                        📝 Logs de Uso
                    </a>
                    <a href="{{ route('admin.token-usage.stats') }}" class="block py-1 text-sm text-gray-600 hover:text-gray-800">
                        📊 Estatísticas de Uso
                    </a>
                    <a href="{{ route('admin.token-usage.alerts') }}" class="block py-1 text-sm text-gray-600 hover:text-gray-800">
                        🚨 Alertas de Uso
                    </a>
                </div>
            </div>
            
            <a href="{{ route('admin.apikeys.index') }}" class="block pl-3 pr-4 py-2 border-l-4 {{ request()->routeIs('admin.apikeys.*') ? 'border-indigo-500 text-indigo-700 bg-indigo-50' : 'border-transparent text-gray-600 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-800' }} text-base font-medium">🔑 Chaves de API</a>
            <a href="{{ url('/horizon') }}" target="_blank" class="block pl-3 pr-4 py-2 border-l-4 border-transparent text-gray-600 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-800 text-base font-medium">Horizon</a>
            
            <!-- Monitoramento no Mobile -->
            <div class="border-l-4 border-transparent pl-3 pr-4 py-2">
                <div class="text-base font-medium text-gray-600 mb-2">Monitoramento</div>
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
        <div class="pt-4 pb-3 border-t border-gray-200">
            <div class="flex items-center px-4">
                <div class="flex-shrink-0">
                    <div class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center">
                        <span class="text-xl font-medium text-gray-700">{{ substr(Auth::user()->name, 0, 1) }}</span>
                    </div>
                </div>
                <div class="ml-3">
                    <div class="text-base font-medium text-gray-800">{{ Auth::user()->name }}</div>
                    <div class="text-sm font-medium text-gray-500">{{ Auth::user()->email }}</div>
                </div>
            </div>
            <div class="mt-3 space-y-1">
                <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-base font-medium text-gray-500 hover:text-gray-800 hover:bg-gray-100">Perfil</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="block w-full text-left px-4 py-2 text-base font-medium text-gray-500 hover:text-gray-800 hover:bg-gray-100">Sair</button>
                </form>
            </div>
        </div>
    </div>
</nav>

<script>
    function toggleDropdown() {
        const dropdown = document.getElementById('user-dropdown');
        dropdown.classList.toggle('hidden');
    }

    function toggleMobileMenu() {
        const mobileMenu = document.getElementById('mobile-menu');
        const iconMenu = document.getElementById('icon-menu');
        const iconClose = document.getElementById('icon-close');
        
        mobileMenu.classList.toggle('hidden');
        iconMenu.classList.toggle('hidden');
        iconClose.classList.toggle('hidden');
    }

    // Close dropdown when clicking outside
    window.addEventListener('click', function(e) {
        const dropdown = document.getElementById('user-dropdown');
        const button = document.getElementById('user-menu-button');
        
        if (!dropdown.contains(e.target) && !button.contains(e.target)) {
            dropdown.classList.add('hidden');
        }
    });
</script>
