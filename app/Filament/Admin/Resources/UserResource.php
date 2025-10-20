<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\Builder;
use App\Enums\UserStatus;
use Filament\Resources\Components\Tab;

class UserResource extends Resource
{
    protected static ?string $model = User::class;
    protected static ?string $navigationIcon = 'heroicon-o-users';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required(),
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->required(),
                // Field password hanya muncul saat membuat user baru
                Forms\Components\TextInput::make('password')
                    ->password()
                    ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                    ->dehydrated(fn ($state) => filled($state))
                    ->required(fn (string $context): bool => $context === 'create'),
                // Toggle untuk menjadikan user sebagai Super Admin
                Forms\Components\Toggle::make('is_super_admin')
                    ->label('Jadikan Super Admin')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable(),
                Tables\Columns\TextColumn::make('email')->searchable(),
                // Kolom Status dengan Badge
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn ($state): string => match ($state) {
                        UserStatus::Pending->value, 'pending' => 'warning',
                        UserStatus::Approved->value, 'approved' => 'success',
                        UserStatus::Rejected->value, 'rejected' => 'danger',
                        default => 'secondary',
                    }),
                Tables\Columns\IconColumn::make('is_super_admin')->label('Super Admin')->boolean(),
                Tables\Columns\TextColumn::make('created_at')->dateTime('d M Y')->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                // Grup Tombol untuk Approve & Reject
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('approve')
                        ->label('Approve')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(fn (User $record) => $record->update(['status' => UserStatus::Approved]))
                        // Tombol hanya muncul jika status user bukan 'approved'
                        ->visible(fn (User $record) => $record->status !== UserStatus::Approved),
                    
                    Tables\Actions\Action::make('reject')
                        ->label('Reject')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->requiresConfirmation() // Tambahkan konfirmasi untuk reject
                        ->action(fn (User $record) => $record->update(['status' => UserStatus::Rejected]))
                        // Tombol hanya muncul jika status user bukan 'rejected'
                        ->visible(fn (User $record) => $record->status !== UserStatus::Rejected),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    // public static function table(Table $table): Table
    // {
    //     return $table
    //         ->columns([
    //             Tables\Columns\TextColumn::make('name')->searchable(),
    //             Tables\Columns\TextColumn::make('email')->searchable(),
    //             Tables\Columns\IconColumn::make('is_super_admin')
    //                 ->label('Super Admin')
    //                 ->boolean(),
    //             Tables\Columns\TextColumn::make('created_at')
    //                 ->dateTime('d M Y')
    //                 ->sortable(),
    //         ])
    //         ->actions([
    //             Tables\Actions\EditAction::make(),
    //             Tables\Actions\DeleteAction::make(),
    //         ]);
    // }
    
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }    
}