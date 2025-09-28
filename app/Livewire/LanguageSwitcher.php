<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class LanguageSwitcher extends Component
{
    public $currentLocale;
    public $showDropdown = false;

    public function mount()
    {
        $this->currentLocale = Session::get('locale', App::getLocale());
    }

    public function toggleDropdown()
    {
        $this->showDropdown = !$this->showDropdown;
    }

    public function switchLanguage($locale)
    {
        if (in_array($locale, ['en', 'id'])) {
            App::setLocale($locale);
            Session::put('locale', $locale);
            $this->currentLocale = $locale;

            // Force a full page reload to ensure all components update
            return redirect()->to(request()->fullUrlWithQuery(['lang' => $locale]));
        }
    }

    public function getAvailableLocales()
    {
        return [
            'en' => 'English',
            'id' => 'Indonesia',
        ];
    }

    public function getLanguageFlags()
    {
        return [
            'en' => '🇺🇸',
            'id' => '🇮🇩',
        ];
    }

    public function render()
    {
        return view('livewire.language-switcher');
    }
}