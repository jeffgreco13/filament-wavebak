<?php

namespace Jeffgreco13\FilamentWave\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Support\Enums\FontWeight;
use Jeffgreco13\FilamentWave\Models\Currency;
use Jeffgreco13\FilamentWave\Resources\CustomerResource\Pages;

class CustomerResource extends Resource
{
    // protected static ?string $model = Customer::class;

    protected static ?string $navigationIcon = 'heroicon-o-identification';

    public static function getModel(): string
    {
        return filament('wave')->getCustomerModel();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->columns(1)
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Customer or Business Name')
                    ->placeholder('Dunder Mifflin')
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\Fieldset::make('Primary contact')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('first_name')
                            ->placeholder('Michael'),
                        Forms\Components\TextInput::make('last_name')
                            ->placeholder('Scott'),
                        Forms\Components\TextInput::make('email')
                            ->placeholder('mscott@dundermifflin.com')
                            ->email(),
                        Forms\Components\TextInput::make('phone')
                    ]),
                Forms\Components\Select::make('currency')
                    ->searchable()
                    ->placeholder('Use account default')
                    ->options(Currency::all()->sortBy('code')->pluck('label','code')),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('name')
            ->columns([

                Tables\Columns\ImageColumn::make('avatar_url')
                    ->label(false)
                    ->circular()
                    ->grow(false),
                Tables\Columns\TextColumn::make('name')
                    ->weight(FontWeight::Bold)
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->label('Contact')
                    ->description(fn ($record) => "{$record->first_name} {$record->last_name}")
                    ->searchable(['email', 'first_name', 'last_name'])
                    ->sortable(),
                Tables\Columns\TextColumn::make('currency')
                    ->toggleable(isToggledHiddenByDefault: true)


            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->modalWidth('lg'),
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('archive')
                        ->label(fn($record) => $record->is_archived ? 'Restore' : 'Archive')
                        ->color('warning')
                        ->icon('heroicon-s-archive-box')
                        ->action(function($record){
                            $record->toggleArchive();
                        }),
                    Tables\Actions\DeleteAction::make()
                ])
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateActions([
                // Tables\Actions\CreateAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageCustomers::route('/'),
        ];
    }
}
