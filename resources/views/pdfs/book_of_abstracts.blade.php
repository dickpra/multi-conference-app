<!DOCTYPE html>
<html lang="id">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Book of Abstracts - {{ $conference->name }}</title>
    <style>
        @page { margin: 2.5cm 2cm; }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10pt;
            line-height: 1.4;
            color: #333;
        }
        h1, h2, h3 { font-family: 'DejaVu Sans', sans-serif; color: #000; }
        .page-break { page-break-after: always; }

        /* Cover Page */
        .cover-page { text-align: center; margin-top: 4cm; }
        .cover-page .logo { max-height: 100px; margin-bottom: 1cm; }
        .cover-page h1 { font-size: 24pt; margin-bottom: 0.5cm; }
        .cover-page h2 { font-size: 16pt; color: #444; margin-bottom: 2cm; }
        .cover-page .details { font-size: 12pt; }

        /* Table of Contents */
        .toc ul { list-style-type: none; padding-left: 0; }
        .toc li { margin-bottom: 10px; }
        .toc a { text-decoration: none; color: #000; }
        .toc .dot-leader { border-bottom: 1px dotted #999; width: 100%; }
        .toc .page-num { float: right; }

        /* Abstract Page */
        .abstract-item { margin-bottom: 1.5cm; page-break-inside: avoid; }
        .abstract-item h3.title { font-size: 14pt; margin-bottom: 5px; text-align: center; }
        .abstract-item p.authors { font-size: 11pt; font-style: italic; text-align: center; margin-bottom: 10px; }
        .abstract-item p.affiliation { font-size: 9pt; text-align: center; margin-bottom: 15px; color: #555; }
        .abstract-item .keywords { margin-top: 10px; font-size: 9pt; }
    </style>
</head>
<body>
    <!-- Halaman Sampul -->
    <div class="cover-page">
        @if ($conference->logo)
            <img src="{{ public_path('storage/' . $conference->logo) }}" class="logo">
        @endif
        <h1>{{ $conference->book_title ?? 'BOOK OF ABSTRACTS' }}</h1>
        <h2>{{ $conference->name }}</h2>
        <div class="details">
            <p>{{ \Carbon\Carbon::parse($conference->start_date)->format('d M') }} - {{ \Carbon\Carbon::parse($conference->end_date)->format('d M Y') }}</p>
            <p>{{ $conference->location }}</p>
            @if($conference->isbn_issn)
                <p>ISBN/ISSN: {{ $conference->isbn_issn }}</p>
            @endif
        </div>
    </div>
    <div class="page-break"></div>

    <!-- Halaman Kata Pengantar -->
    <div>
        <h2>{{ $conference->foreword_title ?? 'Kata Pengantar' }}</h2>

        @if($conference->foreword)
            {!! $conference->foreword !!}
        @else
            <p>
                Puji syukur kami panjatkan kehadirat Tuhan Yang Maha Esa atas rahmat dan karunia-Nya sehingga acara {{ $conference->name }} dapat terselenggara dengan baik. Buku abstrak ini merupakan kompilasi dari karya-karya terbaik yang telah melalui proses peninjauan ketat oleh para ahli di bidangnya.
            </p>
            <p>
                Kami mengucapkan terima kasih yang sebesar-besarnya kepada para pembicara, penulis, reviewer, dan seluruh panitia yang telah berkontribusi dalam kesuksesan acara ini. Semoga kumpulan abstrak ini dapat menjadi referensi yang berharga dan memicu inovasi lebih lanjut.
            </p>
        @endif

        <br>
        <p>Hormat kami,</p>
        <br><br><br>
        <p><strong>{{ $conference->users()->where('role', 'chair')->first()->name ?? 'Conference Chair' }}</strong></p>
        <p><em>Conference Chair</em></p>
    </div>
    <div class="page-break"></div>

    <!-- Halaman Daftar Isi -->
    <div>
        <h2>Daftar Isi</h2>
        <div class="toc">
            <ul>
                @foreach($submissions as $submission)
                    <li>
                        <span class="dot-leader"></span>
                        <a href="#">
                            {{ $submission->title }}<br>
                            <small><em>{{ $submission->author->name }}</em></small>
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
    <div class="page-break"></div>

    <!-- Halaman-Halaman Abstrak -->
    @foreach($submissions as $submission)
        <div class="abstract-item">
            <h3 class="title">{{ $submission->title }}</h3>
            <p class="authors">{{ $submission->author->name }}</p>
            <p class="affiliation">{{-- Anda bisa tambahkan afiliasi di sini jika ada --}}</p>
            <div class="abstract-body">
                {!! $submission->abstract !!}
            </div>
            <p class="keywords"><strong>Keywords:</strong> {{ is_array($submission->keywords) ? implode(', ', $submission->keywords) : $submission->keywords }}</p>
        </div>
        @if(!$loop->last)
            <hr style="border: 0; border-top: 1px solid #ccc; margin: 1cm 0;">
        @endif
    @endforeach
</body>
</html>