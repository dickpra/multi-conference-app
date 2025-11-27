<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice Participant - {{ $attendee->invoice_number }}</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            color: #333;
            font-size: 14px;
            line-height: 1.5;
        }
        
        /* Layout Header */
        .header {
            width: 100%;
            border-bottom: 2px solid #2563eb; /* Warna Biru Utama */
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .logo {
            float: left;
            max-height: 60px;
            max-width: 200px;
        }
        .company-details {
            float: right;
            text-align: right;
            font-size: 12px;
            color: #555;
            max-width: 300px;
        }
        
        /* Judul Invoice & Stempel */
        .invoice-title-wrapper {
            position: relative;
            margin-bottom: 20px;
            height: 50px;
            clear: both; /* Clear float header */
            padding-top: 10px;
        }
        .invoice-title {
            font-size: 28px;
            font-weight: bold;
            color: #1e3a8a;
            letter-spacing: 2px;
            text-transform: uppercase;
            float: left;
        }
        
        /* Stempel Status (Paid/Unpaid) */
        .stamp-box {
            float: right;
            font-size: 14px;
            font-weight: bold;
            text-transform: uppercase;
            padding: 5px 15px;
            border-radius: 4px;
            transform: rotate(-5deg); /* Efek miring sedikit */
        }
        .stamp-paid {
            color: #15803d; /* Hijau Tua */
            border: 3px solid #15803d;
            background-color: #f0fdf4;
        }
        .stamp-unpaid {
            color: #b91c1c; /* Merah Tua */
            border: 3px solid #b91c1c;
            background-color: #fef2f2;
        }

        /* Informasi Tagihan (Bill To) */
        .details-table {
            width: 100%;
            margin-top: 10px;
            border-collapse: collapse;
        }
        .details-table td {
            vertical-align: top;
        }
        .bill-to-heading {
            font-size: 12px;
            color: #888;
            text-transform: uppercase;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .invoice-meta {
            text-align: right;
        }
        .invoice-meta strong {
            color: #333;
            display: inline-block;
            width: 100px;
        }

        /* Tabel Item */
        .items-table {
            width: 100%;
            margin-top: 40px;
            border-collapse: collapse;
        }
        .items-table th {
            background-color: #f3f4f6;
            color: #1f2937;
            font-weight: bold;
            text-align: left;
            padding: 12px;
            border-bottom: 2px solid #e5e7eb;
            text-transform: uppercase;
            font-size: 12px;
        }
        .items-table td {
            padding: 12px;
            border-bottom: 1px solid #f3f4f6;
        }
        .total-row td {
            background-color: #f9fafb;
            font-weight: bold;
            font-size: 16px;
            color: #111827;
            border-top: 2px solid #111827;
        }

        /* Info Pembayaran (Kotak Bawah) */
        .payment-section {
            margin-top: 50px;
            padding: 20px;
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            page-break-inside: avoid; /* Jangan terpotong halaman */
        }
        .payment-section h3 {
            margin-top: 0;
            margin-bottom: 15px;
            color: #0f172a;
            font-size: 16px;
            border-bottom: 1px solid #cbd5e1;
            padding-bottom: 10px;
        }
        .payment-table {
            width: 100%;
            font-size: 13px;
        }
        .payment-table td {
            padding: 4px 0;
            vertical-align: top;
        }
        .payment-label {
            font-weight: bold;
            color: #64748b;
            width: 140px;
        }
        .payment-value {
            color: #334155;
        }

        /* Footer */
        .footer {
            margin-top: 50px;
            text-align: center;
            font-size: 11px;
            color: #94a3b8;
            border-top: 1px solid #e2e8f0;
            padding-top: 20px;
        }
    </style>
</head>
<body>

    <div class="header">
        @if ($conference->logo)
            <img src="{{ public_path('storage/' . $conference->logo) }}" class="logo" alt="Logo">
        @else
            <h2 style="margin:0; color:#2563eb;">{{ config('app.name') }}</h2>
        @endif

        <div class="company-details">
            <strong>{{ $conference->name }}</strong><br>
            @if($conference->postal_address)
                {!! nl2br(e($conference->postal_address)) !!}<br>
            @endif
            @if($conference->vat_number)
                VAT/NPWP: {{ $conference->vat_number }}
            @endif
        </div>
        <div style="clear: both;"></div>
    </div>

    <div class="invoice-title-wrapper">
        <div class="invoice-title">INVOICE (PARTICIPANT)</div>
        
        @if($attendee->status === 'paid')
            <div class="stamp-box stamp-paid">PAID / LUNAS</div>
        @else
            <div class="stamp-box stamp-unpaid">UNPAID</div>
        @endif
    </div>

    <table class="details-table">
        <tr>
            <td width="55%">
                <div class="bill-to-heading">BILL TO:</div>
                <strong style="font-size: 16px;">{{ $attendee->user->name }}</strong><br>
                {{ $attendee->user->email }}<br>
                {{ $attendee->user->country ?? '' }}
            </td>
            <td width="45%" class="invoice-meta">
                <strong>Invoice No:</strong> {{ $attendee->invoice_number }}<br>
                <strong>Date:</strong> {{ $attendee->created_at->format('d M Y') }}<br>
                
                @if($attendee->status === 'paid')
                    <strong>Paid Date:</strong> {{ now()->format('d M Y') }}<br>
                @else
                    <strong>Due Date:</strong> {{ $attendee->created_at->addDays(7)->format('d M Y') }}<br>
                @endif
            </td>
        </tr>
    </table>

    <table class="items-table">
        <thead>
            <tr>
                <th width="70%">Description</th>
                <th width="30%" style="text-align: right;">Amount</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    <strong>Conference Registration Fee (Listener)</strong><br>
                    <span style="color: #666; font-size: 12px;">Event: {{ $conference->name }}</span>
                </td>
                <td style="text-align: right;">
                    Rp {{ number_format($conference->participant_fee, 0, ',', '.') }}
                </td>
            </tr>
            <tr class="total-row">
                <td style="text-align: right;">TOTAL</td>
                <td style="text-align: right;">
                    Rp {{ number_format($conference->participant_fee, 0, ',', '.') }}
                </td>
            </tr>
        </tbody>
    </table>

    <div class="payment-section">
        <h3>Payment Information (Bank Transfer)</h3>
        
        <table class="payment-table">
            <tr>
                <td class="payment-label">Bank Name:</td>
                <td class="payment-value"><strong>{{ $conference->bank_name }}</strong></td>
            </tr>
            <tr>
                <td class="payment-label">Account Number:</td>
                <td class="payment-value" style="font-size: 15px;"><strong>{{ $conference->bank_account_number }}</strong></td>
            </tr>
            <tr>
                <td class="payment-label">Account Holder:</td>
                <td class="payment-value">{{ $conference->bank_account_holder }}</td>
            </tr>

            @if($conference->swift_code)
            <tr>
                <td class="payment-label">SWIFT / BIC Code:</td>
                <td class="payment-value"><strong>{{ $conference->swift_code }}</strong></td>
            </tr>
            @endif

            @if($conference->bank_account_address)
            <tr>
                <td class="payment-label">Bank Address:</td>
                <td class="payment-value">
                    {!! nl2br(e($conference->bank_account_address)) !!}
                    @if($conference->bank_city), {{ $conference->bank_city }}@endif
                </td>
            </tr>
            @endif
        </table>

        <div style="margin-top: 15px; font-size: 12px; color: #666; border-top: 1px dashed #ccc; padding-top: 10px;">
            Please include the Invoice Number <strong>({{ $attendee->invoice_number }})</strong> in your payment reference description.
        </div>
    </div>

    <div class="footer">
        <p>Thank you for your participation.</p>
        <p>&copy; {{ date('Y') }} {{ $conference->name }}. All rights reserved.</p>
    </div>

</body>
</html>