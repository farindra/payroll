<div class="flex items-center justify-end ml-4">
    <div class="relative">
        <button
            wire:click="toggleDropdown"
            class="flex items-center space-x-2 px-3 py-2 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 shadow-sm"
        >
            <span>{{ $this->getLanguageFlags()[$currentLocale] ?? '🌐' }}</span>
            <span class="text-sm font-medium text-gray-700 hidden sm:block">
                {{ $this->getAvailableLocales()[$currentLocale] ?? 'Language' }}
            </span>
            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
            </svg>
        </button>

        @if($showDropdown ?? false)
            <div class="absolute right-0 mt-2 w-48 bg-white border border-gray-200 rounded-md shadow-lg z-50">
                <div class="py-1">
                    @foreach($this->getAvailableLocales() as $locale => $name)
                        <button
                            wire:click="switchLanguage('{{ $locale }}')"
                            class="flex items-center space-x-3 w-full px-4 py-2 text-left text-sm hover:bg-gray-100 {{ $currentLocale === $locale ? 'bg-blue-50 text-blue-700' : 'text-gray-700' }}"
                        >
                            <span class="text-lg">{{ $this->getLanguageFlags()[$locale] ?? '🌐' }}</span>
                            <span>{{ $name }}</span>
                            @if($currentLocale === $locale)
                                <span class="ml-auto">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                    </svg>
                                </span>
                            @endif
                        </button>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</div>