<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Certificate of Attendance</title>
    <style>
        body { font-family: 'Times New Roman', serif; text-align: center; border: 10px solid #1e3a8a; padding: 50px; }
        .container { border: 2px solid #1e3a8a; padding: 40px; height: 90%; }
        h1 { font-size: 48px; color: #1e3a8a; margin-bottom: 10px; text-transform: uppercase; }
        .subtitle { font-size: 24px; color: #555; margin-bottom: 40px; }
        .name { font-size: 36px; font-weight: bold; margin: 20px 0; border-bottom: 1px solid #333; display: inline-block; padding: 0 50px; }
        .content { font-size: 18px; line-height: 1.6; margin-bottom: 60px; }
        .conference-name { font-weight: bold; font-size: 22px; }
        .signature { margin-top: 50px; display: flex; justify-content: space-between; }
        .sig-block { width: 40%; text-align: center; border-top: 1px solid #333; padding-top: 10px; margin: 0 auto; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Certificate of Attendance</h1>
        <div class="subtitle">This certificate is proudly presented to</div>

        <div class="name">{{ $attendee->user->name }}</div>

        <div class="content">
            <p>For participating as a <strong>LISTENER</strong> in the</p>
            <div class="conference-name">{{ $conference->name }}</div>
            <p>{{ $conference->theme }}</p>
            <p>Held on {{ \Carbon\Carbon::parse($conference->start_date)->format('F d, Y') }}</p>
        </div>

        <div class="signature">
            <div class="sig-block">
                <strong>Conference Chair</strong>
            </div>
        </div>
    </div>
</body>
</html>