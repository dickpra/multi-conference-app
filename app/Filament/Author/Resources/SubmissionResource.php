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
    // protected static ?string $navigationLabel = 'Riwayat Submission Saya';
    protected static ?int $navigationSort = 1;

    public static function getModelLabel(): string
    {
        return 'Submission';
    }
    public static function getPluralModelLabel(): string
    {
        return 'Submissions';
    }


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
                Forms\Components\TextInput::make('title')->label(__('Judul Makalah'))->required(),
                Forms\Components\RichEditor::make('abstract')->label(__('Abstrak'))->required(),
                Forms\Components\TagsInput::make('keywords')->label(__('Kata Kunci'))->required(),
                Forms\Components\FileUpload::make('full_paper_path')
                    ->label(__('Unggah Makalah Lengkap (PDF/DOCX)'))
                    ->directory('full-papers')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('conference.name')
                    ->label(__('Konferensi'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('title')
                    ->label(__('Judul Makalah'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(function ($state) {
                        switch ($state) {
                            case SubmissionStatus::Submitted:
                                return 'gray';
                            case SubmissionStatus::UnderReview:
                                return 'warning';
                            case SubmissionStatus::Accepted:
                                return 'success';
                            case SubmissionStatus::Rejected:
                                return 'danger';
                            case SubmissionStatus::RevisionRequired:
                                return 'info';
                            case SubmissionStatus::Paid:
                                return 'success';
                            default:
                                return 'secondary';
                        }
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('Tanggal Submit'))
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