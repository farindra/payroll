<div class="flex items-center justify-end ml-4">
    <div x-data="{
        currentLocale: '{{ app()->getLocale() }}',
        locales: {{ json_encode(['en' => 'English', 'id' => 'Indonesia']) }},
        flags: {{ json_encode(['en' => '🇺🇸', 'id' => '🇮🇩']) }},
        open: false,

        switchLanguage(locale) {
            // Create URL with language parameter
            const currentUrl = new URL(window.location.href);
            currentUrl.searchParams.set('lang', locale);

            // Force page reload to apply language change
            window.location.href = currentUrl.toString();

            // Alternative: use form submission for better compatibility
            // const form = document.createElement('form');
            // form.method = 'GET';
            // form.action = window.location.pathname;
            // const input = document.createElement('input');
            // input.name = 'lang';
            // input.value = locale;
            // form.appendChild(input);
            // document.body.appendChild(form);
            // form.submit();
        }
    }" class="relative">
        <button
            x-on:click="open = !open"
            @click.outside="open = false"
            class="flex items-center space-x-2 px-3 py-2 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 shadow-sm"
        >
            <span x-text="flags[currentLocale] || '🌐'" class="text-lg"></span>
            <span x-text="locales[currentLocale] || 'Language'" class="text-sm font-medium text-gray-700 hidden sm:block"></span>
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
            x-cloak
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
                        <span x-show="currentLocale === locale" class="ml-auto">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                            </svg>
                        </span>
                    </button>
                </template>
            </div>
        </div>
    </div>
</div>