<?php

namespace App\Filament\Chair\Resources;

use App\Filament\Chair\Resources\ConferenceScheduleResource\Pages;
use App\Models\ConferenceSchedule;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ConferenceScheduleResource extends Resource
{
    protected static ?string $model = ConferenceSchedule::class;
    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';
    protected static ?int $navigationSort = 2;

    protected static ?string $tenantRelationshipName = 'schedules';


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('title')
                    ->required()
                    ->maxLength(255),
                DatePicker::make('date')
                    ->required(),
                Textarea::make('description')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')->searchable(),
                TextColumn::make('date')->date('d M Y')->sortable(),
            ])
            ->defaultSort('date', 'asc')
            ->filters([])
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

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListConferenceSchedules::route('/'),
            'create' => Pages\CreateConferenceSchedule::route('/create'),
            'edit' => Pages\EditConferenceSchedule::route('/{record}/edit'),
        ];
    }    
}