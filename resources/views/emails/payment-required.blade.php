<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Required</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; background-color: #f9f9f9; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 20px auto; background: #ffffff; padding: 30px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .header { text-align: center; padding-bottom: 20px; border-bottom: 2px solid #eee; }
        .header h1 { color: #2563eb; margin: 0; }
        .content { padding: 20px 0; }
        .payment-box { background-color: #f0f9ff; border-left: 4px solid #2563eb; padding: 15px; margin: 20px 0; border-radius: 4px; }
        .payment-details table { width: 100%; }
        .payment-details td { padding: 5px 0; }
        .payment-details td:first-child { font-weight: bold; width: 140px; color: #555; }
        .footer { text-align: center; font-size: 12px; color: #888; margin-top: 30px; border-top: 1px solid #eee; padding-top: 10px; }
        .btn { display: inline-block; background-color: #2563eb; color: #ffffff; text-decoration: none; padding: 10px 20px; border-radius: 5px; font-weight: bold; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Paper Accepted</h1>
            <p>{{ $submission->conference->name }}</p>
        </div>

        <div class="content">
            <p>Dear <strong>{{ $submission->author->name }}</strong>,</p>

            <p>We are pleased to inform you that your paper entitled:</p>
            <p style="font-style: italic; text-align: center;"><strong>"{{ $submission->title }}"</strong></p>
            <p>has been <strong>ACCEPTED</strong> for presentation at {{ $submission->conference->name }}.</p>

            <p>To proceed with the final registration and receive your Letter of Acceptance (LoA), please complete the registration fee payment.</p>

            <div class="payment-box">
                <h3>Payment Details</h3>
                <div class="payment-details">
                    <table>
                        <tr>
                            <td>Amount:</td>
                            <td style="font-size: 18px; color: #d32f2f;">
                                <strong>Rp {{ number_format($submission->conference->registration_fee, 0, ',', '.') }}</strong>
                            </td>
                        </tr>
                        <tr>
                            <td>Bank Name:</td>
                            <td>{{ $submission->conference->bank_name ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td>Account No.:</td>
                            <td><strong>{{ $submission->conference->bank_account_number ?? '-' }}</strong></td>
                        </tr>
                        <tr>
                            <td>Account Holder:</td>
                            <td>{{ $submission->conference->bank_account_holder ?? '-' }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            <p><strong>Next Steps:</strong></p>
            <ol>
                <li>Transfer the exact amount to the bank account listed above.</li>
                <li>Log in to your Author Dashboard.</li>
                <li>Upload your transfer proof (receipt) in the submission details page.</li>
            </ol>

            <div style="text-align: center;">
                <a href="{{ url('/author/login') }}" class="btn">Login to Upload Proof</a>
            </div>

            <p>Once your payment is verified by our admin, your Letter of Acceptance will be sent to your email automatically.</p>
        </div>

        <div class="footer">
            <p>&copy; {{ date('Y') }} {{ $submission->conference->name }}. All rights reserved.</p>
            <p>This is an automated message, please do not reply directly to this email.</p>
        </div>
    </div>
</body>
</html>