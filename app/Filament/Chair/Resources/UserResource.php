<?php

namespace App\Filament\Chair\Resources;

use App\Filament\Chair\Resources\UserResource\Pages;
use App\Filament\Chair\Resources\UserResource\RelationManagers;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Relations\Relation;
use App\Enums\ConferenceRole; 

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    

//    public static function getEloquentQuery(): Builder
//     {
//         $conference = Filament::getTenant();
        
//         // Tambahkan ->select('users.*') untuk memberitahu database
//         // agar memprioritaskan kolom dari tabel 'users'.
//         return $conference->users()->getQuery()->select('users.*');
//     }

    public static function getEloquentQuery(): Builder
    {
        $conference = \Filament\Facades\Filament::getTenant();

        $relation   = $conference->users();       // BelongsToMany
        $pivotTable = $relation->getTable();      // contoh: 'conference_user'

        return $relation
            ->select('users.*')                   // tetap aman jika ada kolom ambigu
            ->addSelect("$pivotTable.role as pivot_role") // <-- kunci: alias 'pivot_role'
            ->getQuery();
    }

    public static function canCreate(): bool
    {
        return false;
    }

    
    public static function getRecordRouteKeyName(): string|null
    {
        return 'users.id';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
            // Kita tidak akan membuat user baru dari sini, jadi form bisa sederhana
            // atau kita bisa definisikan form untuk membuat user jika perlu.
            // Untuk saat ini, kita fokus pada penugasan.
            Forms\Components\TextInput::make('name')->required(),
            Forms\Components\TextInput::make('email')->email()->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable(),
                Tables\Columns\TextColumn::make('email')->searchable(),

                // cukup ini saja
                Tables\Columns\TextColumn::make('pivot_role')
                    ->label('Role')
                    ->badge(),
            ])

            ->filters([
                //
            ])
            ->headerActions([
            // --- INI ADALAH AKSI ATTACH MANUAL ---
            Tables\Actions\Action::make('attachUser')
                ->label('Attach User')
                ->form([
                    Forms\Components\Select::make('userId')
                        ->label('User')
                        // Ganti 'options' dengan closure yang lebih pintar
                        ->options(function (): array {
                            // Ambil tenant (conference) yang sedang aktif
                            $conference = \Filament\Facades\Filament::getTenant();

                            // Ambil ID semua user yang SUDAH terhubung ke conference ini
                            $attachedUserIds = $conference->users()->pluck('users.id')->where('super_admin', false)->toArray();

                            // Kembalikan daftar user yang BELUM terhubung
                            return \App\Models\User::query()
                                ->whereNotIn('id', $attachedUserIds)
                                ->where('is_super_admin', false) 
                                ->pluck('name', 'id')
                                ->toArray();
                        })
                        ->searchable()
                        ->required(),
                    // --- BATAS AKHIR MODIFIKASI ---
                    Forms\Components\Select::make('role')
                        ->options(ConferenceRole::class)
                        ->required(),
                ])
                ->action(function (array $data) {
                    $conference = Filament::getTenant();
                    $conference->users()->attach($data['userId'], ['role' => $data['role']]);
                }),
        ])
        ->actions([
            Tables\Actions\EditAction::make()
                ->form(fn (Tables\Actions\EditAction $action): array => [
                    Forms\Components\Select::make('role')
                        ->options(ConferenceRole::class)
                        ->required(),
                ]),
            
            Tables\Actions\Action::make('detachUser')
                ->label('Detach')
                ->requiresConfirmation()
                ->color('danger')
                // --- TAMBAHKAN BARIS KONDISI INI ---
                ->hidden(fn ($record) => $record->id === auth()->id())
                ->action(function ($record) {
                    $conference = Filament::getTenant();
                    $conference->users()->detach($record->id);
                }),
        ])
        ->bulkActions([
            Tables\Actions\BulkActionGroup::make([
                // Aksi bulk detach juga bisa dibuat manual jika diperlukan
                // Untuk saat ini kita sederhanakan dulu
            ]),
        ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
