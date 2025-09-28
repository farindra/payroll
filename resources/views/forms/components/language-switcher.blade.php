<div x-data="{
    currentLocale: '{{ $getCurrentLocale() }}',
    locales: {{ json_encode($getAvailableLocales()) }},
    flags: {{ json_encode(['en' => '🇺🇸', 'id' => '🇮🇩']) }},

    switchLanguage(locale) {
        const url = new URL(window.location.href);
        url.searchParams.set('lang', locale);
        window.location.href = url.toString();
    }
}" class="flex items-center space-x-2">
    <div class="relative">
        <button
            x-on:click="open = !open"
            @click.outside="open = false"
            class="flex items-center space-x-2 px-3 py-2 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500"
        >
            <span x-text="flags[currentLocale] || '🌐'"></span>
            <span x-text="locales[currentLocale] || 'Language'" class="text-sm font-medium text-gray-700"></span>
            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
            </svg>
        </button>

        <div
            x-show="open"
            x-transition:enter="transition ease-out duration-100"
            x-transition:enter-start="transform opacity-0 scale-95"
            x-transition:enter-end="transform opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-75"
            x-transition:leave-start="transform opacity-100 scale-100"
            x-transition:leave-end="transform opacity-0 scale-95"
            class="absolute right-0 mt-2 w-48 bg-white border border-gray-200 rounded-md shadow-lg z-50"
        >
            <div class="py-1">
                <template x-for="(name, locale) in locales" :key="locale">
                    <button
                        x-on:click="switchLanguage(locale)"
                        class="flex items-center space-x-3 w-full px-4 py-2 text-left text-sm hover:bg-gray-100"
                        :class="currentLocale === locale ? 'bg-blue-50 text-blue-700' : 'text-gray-700'"
                    >
                        <span x-text="flags[locale] || '🌐'" class="text-lg"></span>
                        <span x-text="name"></span>
                    </button>
                </template>
            </div>
        </div>
    </div>
</div>