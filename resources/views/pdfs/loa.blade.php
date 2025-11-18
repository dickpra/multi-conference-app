<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Letter of Acceptance</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 11pt;
            line-height: 1.6;
            color: #333;
        }
        .container {
            padding: 1cm;
        }
        .letterhead {
            text-align: center;
            border-bottom: 2px solid #333;
            padding-bottom: 15px;
            margin-bottom: 1.5cm;
        }
        .letterhead img.logo {
            width: 90px;
            height: auto;
            display: block;
            margin: 0 auto 10px auto;
        }
        .conference-title h1 {
            margin: 0;
            font-size: 18pt;
            color: #000;
        }
        .conference-title p {
            margin: 5px 0 0;
            font-size: 10pt;
            color: #555;
        }
        .date {
            text-align: right;
            margin-bottom: 1cm;
        }
        .content p {
            margin-bottom: 1em;
            text-align: justify;
        }
        .paper-title {
            text-align: center;
            font-weight: bold;
            font-style: italic;
            padding: 10px;
            margin: 1em 2em;
            background-color: #f9f9f9;
            border-left: 3px solid #2563eb;
        }
        .signature-block {
            margin-top: 2cm;
            text-align: left;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="letterhead">
            {{-- Logo at the top --}}
            @if ($submission->conference->logo)
                <img src="{{ public_path('storage/' . $submission->conference->logo) }}" class="logo" alt="Conference Logo">
            @endif

            {{-- Conference title --}}
            <div class="conference-title">
                <h1>{{ $submission->conference->name }}</h1>
                <p>{{ $submission->conference->theme }}</p>
            </div>
        </div>

        <p class="date">Date: {{ now()->format('d F Y') }}</p>

        <div class="content">
            <p>
                To Whom It May Concern:<br>
                <strong>{{ $submission->author->name }}</strong>
            </p>

            <p>Dear **{{ $submission->author->name }}** / Distinguished Author,</p>

            <p>
                Referring to the review process for the paper you submitted with the title:
            </p>

            <div class="paper-title">"{{ $submission->title }}"</div>

            <p>
                We are pleased to inform you that your paper has been <strong>ACCEPTED</strong> for presentation at the <strong>{{ $submission->conference->name }}</strong>.
            </p>

            <p>
                We congratulate you on your achievement and thank you for your valuable contribution to our conference. Further information will be provided shortly.
            </p>

            <p>Sincerely,</p>
        </div>

        <div class="signature-block">
            <p><strong>( {{ $submission->conference->users()->where('role', 'chair')->first()->name ?? 'Conference Chair' }} )</strong></p>
            <p>Conference Chair</p>
        </div>
    </div>
</body>
</html>