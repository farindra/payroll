<?php

namespace App\Forms\Components;

use Filament\Forms\Components\Component;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class LanguageSwitcher extends Component
{
    protected string $view = 'forms.components.language-switcher';

    public function getCurrentLocale(): string
    {
        return Session::get('locale', App::getLocale());
    }

    public function getAvailableLocales(): array
    {
        return [
            'en' => 'English',
            'id' => 'Indonesia',
        ];
    }

    public function getLanguageFlag(string $locale): string
    {
        return match($locale) {
            'en' => '🇺🇸',
            'id' => '🇮🇩',
            default => '🌐',
        };
    }
}