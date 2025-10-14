<?php

namespace App\Filament\Author\Resources;

use App\Enums\SubmissionStatus;
use App\Filament\Author\Resources\SubmissionResource\Pages;
use App\Models\Submission;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms;



class SubmissionResource extends Resource
{
    protected static ?string $model = Submission::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-duplicate';
    protected static ?string $navigationLabel = 'Riwayat Submission Saya';
    protected static ?int $navigationSort = 1;

    public static function getEloquentQuery(): Builder
    {
        // Hanya tampilkan submission milik user yang sedang login
        return parent::getEloquentQuery()->where('user_id', auth()->id());
    }

    // Kita kosongkan form karena create/edit tidak diizinkan dari sini
   // Tambahkan form untuk mengedit submission
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')->label('Judul Makalah')->required(),
                Forms\Components\RichEditor::make('abstract')->label('Abstrak')->required(),
                Forms\Components\TagsInput::make('keywords')->label('Kata Kunci')->required(),
                Forms\Components\FileUpload::make('full_paper_path')
                    ->label('Unggah Makalah Lengkap (PDF/DOCX)')
                    ->directory('full-papers')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('conference.name')
                    ->label('Konferensi')
                    ->sortable(),
                Tables\Columns\TextColumn::make('title')
                    ->label('Judul Makalah')
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge(),
                    // ->color(fn (SubmissionStatus $state): string => match ($state) {
                    //     SubmissionStatus::Submitted => 'gray',
                    //     SubmissionStatus::UnderReview => 'warning',
                    //     SubmissionStatus::Accepted => 'success',
                    //     SubmissionStatus::Rejected => 'danger',
                    // }),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal Submit')
                    ->dateTime('d M Y')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([]) // Hapus aksi edit/delete per baris
            ->bulkActions([]); // Hapus aksi bulk
    }

    public static function canCreate(): bool
    {
        // Sembunyikan tombol "New Submission"
        return false;
    }
    
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSubmissions::route('/'),
            // Kita tidak butuh halaman create/edit di sini, jadi bisa dihapus
            // 'create' => Pages\CreateSubmission::route('/create'),
            'edit' => Pages\EditSubmission::route('/{record}/edit'),
            'view' => Pages\ViewSubmission::route('/{record}'),
        ];
    }
}