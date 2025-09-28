<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PayrollPeriodResource\Pages;
use App\Models\PayrollPeriod;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PayrollPeriodResource extends Resource
{
    protected static ?string $model = PayrollPeriod::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar';

    protected static ?string $navigationGroup = 'Operasional Payroll';

    public static function getNavigationLabel(): string
    {
        return 'Periode Payroll';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Periode')
                    ->description('Definisikan detail periode payroll')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('period_name')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('contoh: Juli 2025'),
                        Forms\Components\DatePicker::make('start_date')
                            ->required()
                            ->date(),
                        Forms\Components\DatePicker::make('end_date')
                            ->required()
                            ->date()
                            ->after('start_date'),
                        Forms\Components\DatePicker::make('payment_date')
                            ->required()
                            ->date()
                            ->after('end_date'),
                    ]),
                Forms\Components\Section::make('Status & Catatan')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->options([
                                'Draft' => 'Draft',
                                'Calculated' => 'Dihitung',
                                'Paid' => 'Dibayar',
                                'Failed' => 'Gagal',
                            ])
                            ->default('Draft')
                            ->required(),
                        Forms\Components\Textarea::make('notes')
                            ->columnSpanFull()
                            ->rows(3),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('period_name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('start_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('end_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('payment_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'Draft' => 'gray',
                        'Calculated' => 'warning',
                        'Paid' => 'success',
                        'Failed' => 'danger',
                    ]),
                Tables\Columns\TextColumn::make('total_employees')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_amount')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'Draft' => 'Draft',
                        'Calculated' => 'Dihitung',
                        'Paid' => 'Dibayar',
                        'Failed' => 'Gagal',
                    ]),
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
            'index' => Pages\ListPayrollPeriods::route('/'),
            'create' => Pages\CreatePayrollPeriod::route('/create'),
            'edit' => Pages\EditPayrollPeriod::route('/{record}/edit'),
        ];
    }
}