<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk #{{ $transaction->id }} - Warung Nasi Kuning Berkah</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Courier New', Courier, monospace;
        }
        body {
            background-color: #f1f5f9;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 24px 12px;
            color: #000;
        }
        .actions {
            margin-bottom: 20px;
            display: flex;
            gap: 12px;
        }
        .btn {
            background: #0f172a;
            color: #fff;
            border: none;
            padding: 10px 20px;
            border-radius: 9999px;
            font-size: 14px;
            font-weight: bold;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .btn-print {
            background: #d97706;
        }
        .btn-print:hover {
            background: #b45309;
        }
        .receipt-container {
            width: 320px;
            max-width: 100%;
            background: #fff;
            padding: 24px 18px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            font-size: 12px;
            line-height: 1.4;
        }
        .header {
            text-align: center;
            margin-bottom: 12px;
        }
        .header h1 {
            font-size: 15px;
            font-weight: 900;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
        }
        .header p {
            font-size: 11px;
            color: #333;
        }
        .divider {
            border-top: 1px dashed #000;
            margin: 8px 0;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 3px;
            font-size: 11px;
        }
        .items-table {
            width: 100%;
            margin: 8px 0;
        }
        .item-row {
            margin-bottom: 6px;
        }
        .item-name {
            font-weight: bold;
        }
        .item-math {
            display: flex;
            justify-content: space-between;
            font-size: 11px;
            color: #222;
        }
        .total-row {
            display: flex;
            justify-content: space-between;
            font-size: 12px;
            margin-bottom: 4px;
        }
        .total-highlight {
            font-size: 14px;
            font-weight: 900;
        }
        .footer {
            text-align: center;
            margin-top: 14px;
            font-size: 11px;
            color: #333;
        }

        @media print {
            body {
                background: #fff;
                padding: 0;
            }
            .actions {
                display: none !important;
            }
            .receipt-container {
                box-shadow: none;
                padding: 0;
                width: 100%;
                border-radius: 0;
            }
            @page {
                margin: 0;
                size: 58mm auto;
            }
        }
    </style>
</head>
<body>

    <div class="actions">
        <button class="btn btn-print" onclick="window.print()">🖨️ Cetak Struk (Thermal)</button>
        <a href="{{ route('pos.index') }}" class="btn">← Kembali ke Kasir</a>
    </div>

    <div class="receipt-container">
        <div class="header">
            <h1>WARUNG NASI KUNING BERKAH</h1>
            <p>Autentik, Cita Rasa Berkah</p>
            <p>Jl. Nasi Kuning Berkah No. 12, Bandung</p>
            <p>WhatsApp: {{ config('services.warung.whatsapp', '0878-4836-1913') }}</p>
        </div>

        <div class="divider"></div>

        <div class="info-row">
            <span>No. Order:</span>
            <span>#{{ $transaction->id }}</span>
        </div>
        <div class="info-row">
            <span>Tanggal:</span>
            <span>{{ $transaction->paid_at ? $transaction->paid_at->format('d/m/Y H:i') : $transaction->created_at->format('d/m/Y H:i') }}</span>
        </div>
        <div class="info-row">
            <span>Kasir:</span>
            <span>{{ $transaction->cashier?->name ?? 'Kasir Berkah' }}</span>
        </div>
        <div class="info-row">
            <span>Pelanggan:</span>
            <span>{{ $transaction->customer_name ?: 'Tamu' }}</span>
        </div>

        <div class="divider"></div>

        <div class="items-table">
            @foreach ($transaction->details as $detail)
                <div class="item-row">
                    <div class="item-name">{{ $detail->menu->name }}</div>
                    <div class="item-math">
                        <span>{{ $detail->quantity }} x Rp {{ number_format($detail->unit_price, 0, ',', '.') }}</span>
                        <span>Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</span>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="divider"></div>

        <div class="total-row">
            <span>Subtotal:</span>
            <span>Rp {{ number_format($transaction->total_amount, 0, ',', '.') }}</span>
        </div>
        <div class="total-row">
            <span>Pajak (0%):</span>
            <span>Rp 0</span>
        </div>
        <div class="total-row total-highlight">
            <span>TOTAL:</span>
            <span>Rp {{ number_format($transaction->total_amount, 0, ',', '.') }}</span>
        </div>

        <div class="divider"></div>

        <div class="total-row">
            <span>Metode Bayar:</span>
            <span style="text-transform: uppercase;">{{ $transaction->payment_method === 'qris' ? 'QRIS / E-WALLET' : 'TUNAI (CASH)' }}</span>
        </div>
        <div class="total-row">
            <span>Bayar (Diterima):</span>
            <span>Rp {{ number_format($transaction->received_amount ?: $transaction->total_amount, 0, ',', '.') }}</span>
        </div>
        <div class="total-row">
            <span>Kembalian:</span>
            <span>Rp {{ number_format($transaction->change_amount, 0, ',', '.') }}</span>
        </div>

        <div class="divider"></div>

        <div class="footer">
            <p>Terima kasih atas kunjungannya!</p>
            <p>Semoga berkah & nikmat seleranya.</p>
            <p style="margin-top: 4px; font-size: 10px;">Simpan struk ini sebagai bukti pembayaran yang sah.</p>
        </div>
    </div>

</body>
</html>

