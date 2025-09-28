<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SalaryComponentResource\Pages;
use App\Models\SalaryComponent;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SalaryComponentResource extends Resource
{
    protected static ?string $model = SalaryComponent::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';

    protected static ?string $navigationGroup = 'Konfigurasi Payroll';

    public static function getNavigationLabel(): string
    {
        return 'Komponen Gaji';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Komponen')
                    ->description('Definisikan komponen gaji dan propertinya')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Select::make('type')
                            ->options([
                                'allowance' => 'Tunjangan',
                                'deduction' => 'Potongan',
                                'tax' => 'Pajak',
                                'insurance' => 'Asuransi',
                            ])
                            ->required(),
                        Forms\Components\TextInput::make('formula_code')
                            ->maxLength(100)
                            ->placeholder('contoh: OVERTIME_RATE, TRANSPORT_ALLOWANCE'),
                        Forms\Components\Toggle::make('is_fixed')
                            ->label('Jumlah Tetap')
                            ->default(false),
                    ]),
                Forms\Components\Section::make('Konfigurasi Jumlah')
                    ->description('Atur jumlah default dan metode perhitungan')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('default_amount')
                            ->numeric()
                            ->prefix('IDR')
                            ->rules(['min:0']),
                        Forms\Components\Toggle::make('is_percentage')
                            ->label('Berdasarkan Persentase')
                            ->default(false),
                    ]),
                Forms\Components\Section::make('Pengaturan Tambahan')
                    ->schema([
                        Forms\Components\Textarea::make('description')
                            ->columnSpanFull()
                            ->rows(3),
                        Forms\Components\Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(true),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('type')
                    ->colors([
                        'allowance' => 'success',
                        'deduction' => 'danger',
                        'tax' => 'warning',
                        'insurance' => 'info',
                    ]),
                Tables\Columns\IconColumn::make('is_fixed')
                    ->boolean()
                    ->label('Tetap'),
                Tables\Columns\TextColumn::make('default_amount')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_percentage')
                    ->boolean()
                    ->label('Persentase'),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->label('Aktif'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'allowance' => 'Tunjangan',
                        'deduction' => 'Potongan',
                        'tax' => 'Pajak',
                        'insurance' => 'Asuransi',
                    ]),
                Tables\Filters\TernaryFilter::make('is_fixed')
                    ->label('Jumlah Tetap'),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Aktif'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSalaryComponents::route('/'),
            'create' => Pages\CreateSalaryComponent::route('/create'),
            'edit' => Pages\EditSalaryComponent::route('/{record}/edit'),
        ];
    }
}