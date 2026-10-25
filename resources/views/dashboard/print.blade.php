<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Penjualan - Warung Nasi Kuning Berkah</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: Arial, Helvetica, sans-serif;
        }
        body {
            background-color: #f8fafc;
            padding: 30px;
            color: #1e293b;
        }
        .no-print {
            max-width: 900px;
            margin: 0 auto 20px auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .btn {
            background: #0f172a;
            color: #fff;
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 14px;
            font-weight: bold;
            cursor: pointer;
            border: none;
        }
        .btn-print {
            background: #d97706;
        }
        .report-page {
            max-width: 900px;
            margin: 0 auto;
            background: #fff;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        }
        .report-header {
            display: flex;
            justify-content: space-between;
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 20px;
            margin-bottom: 25px;
        }
        .brand-title {
            font-size: 24px;
            font-weight: 900;
            color: #0f172a;
        }
        .brand-subtitle {
            font-size: 13px;
            color: #64748b;
            margin-top: 4px;
        }
        .report-meta {
            text-align: right;
            font-size: 13px;
            color: #475569;
        }
        .kpi-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            margin-bottom: 30px;
        }
        .kpi-card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 15px;
        }
        .kpi-label {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #64748b;
            font-weight: bold;
        }
        .kpi-val {
            font-size: 20px;
            font-weight: 900;
            color: #0f172a;
            margin-top: 6px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
            margin-bottom: 30px;
        }
        th {
            background: #f1f5f9;
            text-align: left;
            padding: 10px 12px;
            font-weight: bold;
            color: #334155;
            border-bottom: 2px solid #cbd5e1;
        }
        td {
            padding: 10px 12px;
            border-bottom: 1px solid #e2e8f0;
            vertical-align: top;
        }
        tr:nth-child(even) td {
            background: #fafafa;
        }
        .text-right {
            text-align: right;
        }
        .report-footer {
            margin-top: 40px;
            border-top: 1px dashed #cbd5e1;
            padding-top: 20px;
            display: flex;
            justify-content: space-between;
            font-size: 12px;
            color: #64748b;
        }

        @media print {
            body {
                background: #fff;
                padding: 0;
            }
            .no-print {
                display: none !important;
            }
            .report-page {
                box-shadow: none;
                padding: 0;
                width: 100%;
            }
            @page {
                margin: 15mm;
            }
        }
    </style>
</head>
<body>

    <div class="no-print">
        <a href="{{ route('dashboard') }}" class="btn">← Kembali ke Dashboard</a>
        <button class="btn btn-print" onclick="window.print()">🖨️ Cetak Dokumen Laporan</button>
    </div>

    <div class="report-page">
        <div class="report-header">
            <div>
                <div class="brand-title">WARUNG NASI KUNING BERKAH</div>
                <div class="brand-subtitle">Laporan Rekapitulasi Penjualan & Omset Kasir</div>
            </div>
            <div class="report-meta">
                <p><strong>Periode:</strong> {{ $rangeLabel }}</p>
                <p>Dicetak: {{ now()->format('d/m/Y H:i') }} WIB</p>
                <p>Oleh: {{ auth()->user()->name }} (Owner)</p>
            </div>
        </div>

        <div class="kpi-grid">
            <div class="kpi-card">
                <div class="kpi-label">Total Omset Lunas</div>
                <div class="kpi-val" style="color: #d97706;">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</div>
            </div>
            <div class="kpi-card">
                <div class="kpi-label">Pendapatan Tunai (Cash)</div>
                <div class="kpi-val">Rp {{ number_format($cashRevenue, 0, ',', '.') }}</div>
            </div>
            <div class="kpi-card">
                <div class="kpi-label">Pendapatan QRIS / Digital</div>
                <div class="kpi-val">Rp {{ number_format($qrisRevenue, 0, ',', '.') }}</div>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>No. Order</th>
                    <th>Waktu Bayar</th>
                    <th>Pelanggan</th>
                    <th>Item Pesanan</th>
                    <th>Metode</th>
                    <th>Kasir</th>
                    <th class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($transactions as $t)
                    <tr>
                        <td style="font-weight: bold;">#{{ $t->id }}</td>
                        <td>{{ $t->paid_at ? $t->paid_at->format('d/m H:i') : '-' }}</td>
                        <td>{{ $t->customer_name }}</td>
                        <td>
                            @foreach ($t->details as $d)
                                <div>{{ $d->menu?->name }} (x{{ $d->quantity }})</div>
                            @endforeach
                        </td>
                        <td style="text-transform: uppercase;">{{ $t->payment_method }}</td>
                        <td>{{ $t->cashier?->name ?? 'Website' }}</td>
                        <td class="text-right" style="font-weight: bold;">Rp {{ number_format($t->total_amount, 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 25px; color: #64748b;">Tidak ada data transaksi lunas pada periode ini.</td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr style="background: #f1f5f9; font-weight: bold;">
                    <td colspan="6" style="padding: 12px; font-size: 14px;">TOTAL PENJUALAN KESELURUHAN ({{ $transactions->count() }} Transaksi):</td>
                    <td class="text-right" style="padding: 12px; font-size: 15px; color: #d97706;">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</td>
                </tr>
            </tfoot>
        </table>

        <div class="report-footer">
            <p>Warung Nasi Kuning Berkah · Sistem POS Laravel 12</p>
            <p>Tanda Tangan Pemilik Warung: ______________________</p>
        </div>
    </div>

</body>
</html>

