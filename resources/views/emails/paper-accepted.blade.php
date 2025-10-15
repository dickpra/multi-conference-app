<x-mail::message>
# Selamat, {{ $submission->author->name }}!

Kami dengan gembira memberitahukan bahwa makalah Anda yang berjudul **"{{ $submission->title }}"** telah **DITERIMA** untuk dipresentasikan di **{{ $submission->conference->name }}**.

Terlampir bersama email ini adalah Surat Penerimaan (Letter of Acceptance) resmi Anda.

Selamat atas pencapaian Anda!

Hormat kami,<br>
Panitia {{ $submission->conference->name }}
</x-mail::message>