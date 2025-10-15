<x-mail::message>
# Informasi Status Makalah

Kepada Yth. **{{ $submission->author->name }}**,

Terima kasih telah mengirimkan makalah Anda yang berjudul **"{{ $submission->title }}"** ke **{{ $submission->conference->name }}**.

Setelah melalui proses peninjauan, dengan berat hati kami memberitahukan bahwa makalah Anda belum dapat kami terima untuk dipresentasikan pada konferensi kali ini.

Kami sangat menghargai usaha dan waktu yang telah Anda curahkan. Sebagai masukan yang membangun untuk pengembangan penelitian Anda di masa depan, berikut adalah beberapa komentar dari para reviewer (anonim):

---

@foreach ($submission->reviews as $review)
**Rekomendasi:** {{ $review->recommendation->name }}

**Komentar:**
{!! $review->comments !!}
<hr>
@endforeach

Kami berharap masukan ini dapat bermanfaat dan kami menantikan partisipasi Anda di kesempatan berikutnya.

Hormat kami,<br>
Panitia {{ $submission->conference->name }}
</x-mail::message>