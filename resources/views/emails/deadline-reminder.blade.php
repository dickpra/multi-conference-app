<x-mail::message>
# Submission Deadline Reminder

Hello,

This is a reminder that the paper submission deadline for the **{{ $schedule->conference->name }}** conference is approaching soon.

**Deadline:** {{ \Carbon\Carbon::parse($schedule->date)->translatedFormat('F d, Y') }}.

Don't miss it! Please prepare and submit your best paper through the Author panel on our platform.

Thank you,<br>
Committee of {{ $schedule->conference->name }}
</x-mail::message>