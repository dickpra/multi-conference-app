<?php
// app/Filament/Reviewer/Resources/ConferenceResource.php
namespace App\Filament\Reviewer\Resources;

use App\Enums\ConferenceRole;
use App\Filament\Reviewer\Resources\ConferenceResource\Pages;
use App\Filament\Reviewer\Resources\ConferenceResource\RelationManagers;
use App\Models\Conference;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ConferenceResource extends Resource
{
    protected static ?string $model = Conference::class;
    protected static ?string $navigationIcon = 'heroicon-o-building-library';
    protected static ?string $navigationLabel = 'Pilih Konferensi';

    public static function canCreate(): bool
    {
        return false;
    }

    
    public static function getEloquentQuery(): Builder
    {
        // Tampilkan hanya konferensi di mana user ini adalah seorang Reviewer
        return parent::getEloquentQuery()
            ->whereHas('users', fn ($q) => $q->where('user_id', auth()->id())->where('role', ConferenceRole::Reviewer));
    }

    public static function form(Form $form): Form { return $form->schema([]); }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name'),
                Tables\Columns\TextColumn::make('start_date')->date('d M Y'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()->label('Lihat Tugas'),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\SubmissionsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListConferences::route('/'),
            'view' => Pages\ViewConference::route('/{record}'),
        ];
    }
}