<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Harian {{ $tanggalFormatted }} — Grand Sulawesi Parepare</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 12mm 13mm 12mm 13mm;
        }
        body {
            font-family: DejaVu Sans, Arial, Helvetica, sans-serif;
            color: #0F172A;
            font-size: 9.5px;
            line-height: 1.35;
            margin: 0;
            padding: 0;
        }
        .header-box {
            border-bottom: 2.5px solid #0F172A;
            padding-bottom: 8px;
            margin-bottom: 10px;
        }
        .header-title {
            font-size: 15px;
            font-weight: bold;
            color: #0F172A;
            margin: 0;
            letter-spacing: 0.4px;
        }
        .header-sub {
            font-size: 9.5px;
            color: #475569;
            margin: 3px 0 0 0;
        }
        .meta-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            font-size: 9.5px;
        }
        .meta-table td {
            padding: 2px 0;
        }
        .summary-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 6px 0;
            margin: 0 -6px 12px -6px;
        }
        .summary-card {
            border: 1px solid #CBD5E1;
            background: #F8FAFC;
            padding: 8px 10px;
            vertical-align: top;
        }
        .summary-label {
            font-size: 8px;
            font-weight: bold;
            color: #475569;
            text-transform: uppercase;
            margin: 0 0 3px 0;
        }
        .summary-value {
            font-size: 12.5px;
            font-weight: bold;
            font-family: DejaVu Sans Mono, monospace;
            color: #0F172A;
            margin: 0;
        }
        .summary-value.paid { color: #059669; }
        .summary-foot {
            font-size: 8px;
            color: #64748B;
            margin: 3px 0 0 0;
        }
        .section-heading {
            font-size: 10px;
            font-weight: bold;
            color: #0F172A;
            margin: 0 0 5px 0;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8.8px;
        }
        .data-table th {
            background: #0F172A;
            color: #FFFFFF;
            font-weight: bold;
            text-transform: uppercase;
            padding: 5px 5px;
            border: 1px solid #0F172A;
        }
        .data-table td {
            padding: 4.5px 5px;
            border: 1px solid #CBD5E1;
        }
        .data-table tbody tr:nth-child(even) {
            background: #F8FAFC;
        }
        .text-left { text-align: left; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .mono { font-family: DejaVu Sans Mono, monospace; }
        .text-paid { color: #059669; font-weight: bold; }
        .footer {
            margin-top: 12px;
            padding-top: 6px;
            border-top: 1px solid #CBD5E1;
            font-size: 8.5px;
            color: #64748B;
        }
    </style>
</head>
<body>
    @include('admin.partials.laporan-harian-cetak')
</body>
</html>
