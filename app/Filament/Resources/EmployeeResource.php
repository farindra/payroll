<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EmployeeResource\Pages;
use App\Models\Employee;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;

class EmployeeResource extends Resource
{
    protected static ?string $model = Employee::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = 'Sumber Daya Manusia';

    public static function getNavigationLabel(): string
    {
        return 'Karyawan';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Pribadi')
                    ->description('Detail dasar karyawan')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('nip')
                            ->label('Employee ID')
                            ->required()
                            ->unique(Employee::class, 'nip', ignoreRecord: true)
                            ->maxLength(20),
                        Forms\Components\TextInput::make('full_name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->required()
                            ->unique(Employee::class, 'email', ignoreRecord: true)
                            ->maxLength(255),
                        Forms\Components\TextInput::make('phone')
                            ->tel()
                            ->maxLength(20),
                        Forms\Components\DatePicker::make('date_of_birth'),
                        Forms\Components\Select::make('gender')
                            ->options([
                                'male' => 'Male',
                                'female' => 'Female',
                                'other' => 'Other',
                            ]),
                        Forms\Components\Select::make('marital_status')
                            ->options([
                                'single' => 'Single',
                                'married' => 'Married',
                                'divorced' => 'Divorced',
                                'widowed' => 'Widowed',
                            ]),
                        Forms\Components\TextInput::make('nationality')
                            ->default('Indonesia'),
                    ]),

                Forms\Components\Section::make('Informasi Pekerjaan')
                    ->description('Detail pekerjaan dan gaji')
                    ->columns(2)
                    ->schema([
                        Forms\Components\DatePicker::make('hire_date')
                            ->required(),
                        Forms\Components\Select::make('employment_status')
                            ->options([
                                'active' => 'Active',
                                'terminated' => 'Terminated',
                                'suspended' => 'Suspended',
                                'on_leave' => 'On Leave',
                            ])
                            ->default('active'),
                        Forms\Components\TextInput::make('position')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Select::make('department_id')
                            ->relationship('department', 'name')
                            ->required(),
                        Forms\Components\Select::make('manager_id')
                            ->relationship('manager', 'full_name')
                            ->searchable()
                            ->preload(),
                        Forms\Components\TextInput::make('basic_salary')
                            ->label('Basic Salary')
                            ->numeric()
                            ->prefix('IDR')
                            ->required()
                            ->rules(['min:0']),
                        Forms\Components\Select::make('ptkp_status')
                            ->label('PTKP Status')
                            ->options([
                                'TK/0' => 'TK/0',
                                'TK/1' => 'TK/1',
                                'TK/2' => 'TK/2',
                                'TK/3' => 'TK/3',
                                'K/0' => 'K/0',
                                'K/1' => 'K/1',
                                'K/2' => 'K/2',
                                'K/3' => 'K/3',
                            ])
                            ->default('TK/0'),
                    ]),

                Forms\Components\Section::make('Informasi Bank')
                    ->description('Detail pembayaran')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('bank_name')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('bank_branch')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('bank_account_no')
                            ->label('Bank Account Number')
                            ->maxLength(50),
                    ]),

                Forms\Components\Section::make('Informasi Pajak & Asuransi')
                    ->description('Detail kepatuhan')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('npwp')
                            ->label('NPWP')
                            ->maxLength(50),
                        Forms\Components\TextInput::make('bpjs_kesehatan_no')
                            ->label('BPJS Kesehatan No')
                            ->maxLength(50),
                        Forms\Components\TextInput::make('bpjs_tk_no')
                            ->label('BPJS TK No')
                            ->maxLength(50),
                    ]),

                Forms\Components\Section::make('Informasi Alamat')
                    ->description('Detail kontak')
                    ->columns(2)
                    ->schema([
                        Forms\Components\Textarea::make('address')
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('city')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('state')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('postal_code')
                            ->maxLength(20),
                        Forms\Components\TextInput::make('country')
                            ->default('Indonesia'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nip')
                    ->label('Employee ID')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('full_name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('position')
                    ->searchable(),
                Tables\Columns\TextColumn::make('department.name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('basic_salary')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('employment_status')
                    ->badge()
                    ->colors([
                        'active' => 'success',
                        'terminated' => 'danger',
                        'suspended' => 'warning',
                        'on_leave' => 'info',
                    ]),
                Tables\Columns\TextColumn::make('hire_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('department_id')
                    ->relationship('department', 'name')
                    ->label('Department'),
                Tables\Filters\SelectFilter::make('employment_status')
                    ->options([
                        'active' => 'Active',
                        'terminated' => 'Terminated',
                        'suspended' => 'Suspended',
                        'on_leave' => 'On Leave',
                    ]),
                Tables\Filters\SelectFilter::make('ptkp_status')
                    ->options([
                        'TK/0' => 'TK/0',
                        'TK/1' => 'TK/1',
                        'TK/2' => 'TK/2',
                        'TK/3' => 'TK/3',
                        'K/0' => 'K/0',
                        'K/1' => 'K/1',
                        'K/2' => 'K/2',
                        'K/3' => 'K/3',
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
            'index' => Pages\ListEmployees::route('/'),
            'create' => Pages\CreateEmployee::route('/create'),
            'edit' => Pages\EditEmployee::route('/{record}/edit'),
        ];
    }
}