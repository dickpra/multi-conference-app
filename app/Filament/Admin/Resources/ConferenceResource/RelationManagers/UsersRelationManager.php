<?php

namespace App\Filament\Admin\Resources\ConferenceResource\RelationManagers;

use App\Enums\ConferenceRole; // <-- Import Enum
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class UsersRelationManager extends RelationManager
{
    protected static string $relationship = 'users';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name'),
                // Tampilkan peran dari pivot table
                Tables\Columns\TextColumn::make('pivot.role')->badge(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                // Tombol untuk menugaskan user baru
                Tables\Actions\AttachAction::make()
                    ->form(fn (Tables\Actions\AttachAction $action): array => [
                        $action->getRecordSelect(),
                        // Field untuk memilih peran saat menugaskan
                        Forms\Components\Select::make('role')
                            ->options(ConferenceRole::class) // Ambil opsi dari Enum
                            ->required(),
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->form(fn (Tables\Actions\EditAction $action): array => [
                         // Field untuk mengedit peran
                        Forms\Components\Select::make('role')
                            ->options(ConferenceRole::class)
                            ->required(),
                    ]),
                Tables\Actions\DetachAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DetachBulkAction::make(),
                ]),
            ]);
    }
}