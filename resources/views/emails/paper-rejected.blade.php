<x-mail::message>
# Paper Status Information

Dear **{{ $submission->author->name }}**,

Thank you for submitting your paper titled **"{{ $submission->title }}"** to **{{ $submission->conference->name }}**.

After the review process, we regret to inform you that your paper has not been accepted for presentation at this conference.

We greatly appreciate the effort and time you have invested. As constructive feedback for your future research, here are some comments from the (anonymous) reviewers:

---

@foreach ($submission->reviews as $review)
**Recommendation:** {{ $review->recommendation->name }}

**Comments:**
{!! $review->comments !!}
<hr>
@endforeach

We hope these comments are helpful, and we look forward to your participation in future opportunities.

Best regards,<br>
Committee of {{ $submission->conference->name }}
</x-mail::message>