<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice {{ $submission->invoice_number }}</title>
    <style>
        body { font-family: sans-serif; color: #333; font-size: 14px; }
        .header { width: 100%; border-bottom: 2px solid #444; padding-bottom: 20px; margin-bottom: 30px; }
        .logo { float: left; max-height: 60px; }
        .company-details { float: right; text-align: right; }
        .invoice-title { font-size: 24px; font-weight: bold; text-transform: uppercase; color: #2563eb; clear: both; padding-top: 20px;}
        .details-table { width: 100%; margin-top: 20px; border-collapse: collapse; }
        .details-table td { vertical-align: top; padding: 5px 0; }
        .bill-to { width: 50%; }
        .invoice-data { width: 50%; text-align: right; }
        
        .items-table { width: 100%; margin-top: 40px; border-collapse: collapse; }
        .items-table th { background: #f3f4f6; padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
        .items-table td { padding: 10px; border-bottom: 1px solid #eee; }
        .total-row td { font-weight: bold; font-size: 16px; background: #f9fafb; border-top: 2px solid #333; }
        
        .bank-info { margin-top: 50px; padding: 15px; background-color: #f0f9ff; border: 1px solid #bae6fd; border-radius: 5px; page-break-inside: avoid; }
        .bank-info h3 { margin-top: 0; color: #0284c7; }
        /* Tambahkan Class untuk Stamp Lunas */
        .stamp-paid {
            color: #15803d; /* Hijau */
            border: 3px solid #15803d;
            padding: 5px 10px;
            border-radius: 5px;
            font-weight: bold;
            display: inline-block;
            transform: rotate(-10deg); /* Efek miring seperti stempel */
        }
        .stamp-unpaid {
            color: #b91c1c; /* Merah */
            background-color: #fee2e2;
            padding: 5px 10px;
            border-radius: 5px;
            font-weight: bold;
        }
    </style>
</head>
<body>

    <div class="header">
        @if ($submission->conference->logo)
            <img src="{{ public_path('storage/' . $submission->conference->logo) }}" class="logo">
        @endif
        <div class="company-details">
            <strong>{{ $submission->conference->name }}</strong><br>
            {!! nl2br(e($submission->conference->postal_address)) !!}<br>
            @if($submission->conference->vat_number)
                VAT/NPWP: {{ $submission->conference->vat_number }}
            @endif
        </div>
        <div style="clear: both;"></div>
    </div>

   <div class="invoice-title">
        INVOICE
        {{-- Tampilkan status berdasarkan data record --}}
        <span style="font-size: 14px; float: right; margin-top: 5px;">
            @if($submission->status === \App\Enums\SubmissionStatus::Paid)
                <span class="stamp-paid">PAID / LUNAS</span>
            @else
                <span class="stamp-unpaid">UNPAID / BELUM BAYAR</span>
            @endif
        </span>
    </div>

    <table class="details-table">
        <tr>
            <td class="bill-to">
                <strong>BILL TO:</strong><br>
                {{ $submission->author->name }}<br>
                {{ $submission->author->email }}
            </td>
            <td class="invoice-data">
                <strong>Invoice Number:</strong> {{ $submission->invoice_number }}<br>
                <strong>Invoice Date:</strong> {{ $submission->created_at->format('d M Y') }}<br>
                
                {{-- Tampilkan Tanggal Lunas jika sudah Paid --}}
                @if($submission->status === \App\Enums\SubmissionStatus::Paid)
                    <strong>Paid Date:</strong> {{ now()->format('d M Y') }}<br>
                @endif
            </td>
        </tr>
    </table>

    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 70%;">Description</th>
                <th style="text-align: right;">Amount</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    <strong>Conference Registration Fee</strong><br>
                    Paper Title: "<em>{{ $submission->title }}</em>"<br>
                    Conference: {{ $submission->conference->name }}
                </td>
                <td style="text-align: right;">
                    Rp {{ number_format($submission->conference->registration_fee, 0, ',', '.') }}
                </td>
            </tr>
            <tr class="total-row">
                <td style="text-align: right;">TOTAL DUE</td>
                <td style="text-align: right;">
                    Rp {{ number_format($submission->conference->registration_fee, 0, ',', '.') }}
                </td>
            </tr>
        </tbody>
    </table>

    <div class="bank-info">
        <h3>Payment Method (Bank Transfer)</h3>
        <table style="width: 100%;">
            <tr>
                <td style="width: 120px;"><strong>Bank Name:</strong></td>
                <td>{{ $submission->conference->bank_name }}</td>
            </tr>
            <tr>
                <td><strong>Account No:</strong></td>
                <td>{{ $submission->conference->bank_account_number }}</td>
            </tr>
            <tr>
                <td><strong>Holder:</strong></td>
                <td>{{ $submission->conference->bank_account_holder }}</td>
            </tr>
            @if($submission->conference->swift_code)
            <tr>
                <td><strong>SWIFT/BIC:</strong></td>
                <td>{{ $submission->conference->swift_code }}</td>
            </tr>
            @endif
            @if($submission->conference->bank_account_address)
            <tr>
                <td style="vertical-align: top;"><strong>Address:</strong></td>
                <td>{!! nl2br(e($submission->conference->bank_account_address)) !!}, {{ $submission->conference->bank_city }}</td>
            </tr>
            @endif
        </table>
    </div>

    <div style="margin-top: 40px; text-align: center; font-size: 12px; color: #777;">
        Thank you for your participation.
    </div>

</body>
</html>