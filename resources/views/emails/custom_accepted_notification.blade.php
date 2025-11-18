<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Congratulations! Your Paper Has Been Accepted</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 20px auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        .header { background-color: #2563eb; color: #ffffff; padding: 20px 30px; text-align: center; }
        .header h1 { margin: 0; font-size: 24px; }
        .content { padding: 30px; line-height: 1.6; color: #333; }
        .content p { margin: 0 0 15px; }
        .content strong { color: #2563eb; }
        .footer { background-color: #f8f8f8; padding: 20px 30px; text-align: center; font-size: 12px; color: #777; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            {{-- GANTI DENGAN NAMA APLIKASI ATAU KONFERENSI ANDA --}}
            <h1>{{ $submission->conference->name }}</h1>
        </div>
        <div class="content">
            <h3>Congratulations! Your Paper Has Been Accepted</h3>
            <p>
                We are pleased to inform you that your paper titled:
            </p>
            <p style="text-align: center; font-style: italic; font-weight: bold; padding: 10px; background-color: #f0f5ff; border-radius: 5px;">
                "{{ $submission->title }}"
            </p>
            <p>
                has been <strong>ACCEPTED</strong> for presentation at <strong>{{ $submission->conference->name }}</strong>.
            </p>
            <p>
                Attached to this email is your official Letter of Acceptance. Further information will be provided shortly.
            </p>
            <p>
                Best regards,<br>
                The Committee of {{ $submission->conference->name }}
            </p>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
        </div>
    </div>
</body>
</html>