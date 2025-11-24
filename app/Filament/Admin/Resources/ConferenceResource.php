<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\ConferenceResource\Pages;
use App\Filament\Admin\Resources\ConferenceResource\RelationManagers;
use App\Models\Conference;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Tables\Actions\BulkActionGroup; 
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Actions\DeleteBulkAction;   
// use App\Filament\Admin\Widgets\ConferenceStatsWidget;
use App\Filament\Admin\Resources\ConferenceResource\Widgets\ConferenceStatsWidget;

class ConferenceResource extends Resource
{
    protected static ?string $model = Conference::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')->required(),
                TextInput::make('slug')->required()->unique(ignoreRecord: true),
                Textarea::make('theme')->columnSpanFull(),
                TextInput::make('location'),
                DatePicker::make('start_date')->required(),
                DatePicker::make('end_date')->required(),
                FileUpload::make('logo')->image()->directory('logos'),
                TextInput::make('isbn_issn')
                    ->label('ISBN/ISSN')
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('slug'),
                TextColumn::make('location')->searchable(),
                TextColumn::make('start_date')->date()->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\UsersRelationManager::class, // <-- Tambahkan ini
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListConferences::route('/'),
            'create' => Pages\CreateConference::route('/create'),
            'edit' => Pages\EditConference::route('/{record}/edit'),
            'view' => Pages\ViewConference::route('/{record}')
            // ->getHeaderWidgets([
            //     ConferenceStatsWidget::class,
            // ]),
        ];
    }
}
