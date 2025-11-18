<x-mail::message>
# Congratulations, {{ $submission->author->name }}!

We are pleased to inform you that your paper titled **"{{ $submission->title }}"** has been **ACCEPTED** for presentation at **{{ $submission->conference->name }}**.

Attached to this email is your official Letter of Acceptance.

Congratulations on your achievement!

Best regards,<br>
The {{ $submission->conference->name }} Committee
</x-mail::message>