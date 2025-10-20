<x-mail::message>
# Pengingat Batas Akhir Submission

Halo,

Ini adalah pengingat bahwa batas akhir pengumpulan makalah untuk konferensi **{{ $schedule->conference->name }}** akan segera tiba.

**Deadline:** {{ \Carbon\Carbon::parse($schedule->date)->translatedFormat('d F Y') }}.

Jangan sampai terlewat! Segera siapkan dan kirimkan makalah terbaik Anda melalui panel Author di platform kami.

Terima kasih,<br>
Panitia {{ $schedule->conference->name }}
</x-mail::message>