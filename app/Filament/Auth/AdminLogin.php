<?php

namespace App\Filament\Auth;

use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Textarea;
use Filament\Pages\Auth\Login;
use Illuminate\Contracts\Support\Htmlable;

class AdminLogin extends Login
{

    /**
     * @return array<int | string, string | Form>
     */
    protected function getForms(): array
    {
        return [
            'form' => $this->form(
                $this->makeForm()
                    ->schema([
                        $this->getEmailFormComponent(),
                        $this->getPasswordFormComponent(),
                        $this->getRememberFormComponent(),
                        Textarea::make('Info Login Demo')->rows(5)->disabled()->default("Email: admin@example.com\nPassword: password")
                    ])
                    ->statePath('data'),
            ),
        ];
    }
}
