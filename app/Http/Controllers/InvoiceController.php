<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Carbon;


class InvoiceController extends Controller
{
    public function show($id)
    {
        $bill = Bill::with(['customer', 'usage'])->findOrFail($id);

        return view('invoice.show', compact('bill'));
    }

    public function download($id)
    {
        $bill = Bill::with('customer','usage')->findOrFail($id);

        $pdf = Pdf::loadView('invoice.show', compact('bill'));

        return $pdf->download('invoice-'.$bill->invoice_number.'.pdf');
    }

    public function downloadJpg($id)
    {
        $bill = Bill::with(['customer', 'usage'])->findOrFail($id);

        $image = $this->createInvoiceImage($bill);
        $filename = 'invoice-' . ($bill->invoice_number ?: 'INV-' . $bill->id) . '.jpg';

        ob_start();
        imagejpeg($image, null, 92);
        imagedestroy($image);
        $binary = ob_get_clean();

        return response()->make($binary, 200, [
            'Content-Type' => 'image/jpeg',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
            'Content-Length' => strlen($binary),
        ]);
    }

    private function createInvoiceImage(Bill $bill)
    {
        $width = 1200;
        $height = 1600;
        $image = imagecreatetruecolor($width, $height);

        $colors = [
            'bg' => imagecolorallocate($image, 243, 244, 246),
            'card' => imagecolorallocate($image, 255, 255, 255),
            'border' => imagecolorallocate($image, 229, 231, 235),
            'text' => imagecolorallocate($image, 31, 41, 55),
            'muted' => imagecolorallocate($image, 107, 114, 128),
            'primary' => imagecolorallocate($image, 37, 99, 235),
            'success' => imagecolorallocate($image, 22, 163, 74),
            'danger' => imagecolorallocate($image, 220, 38, 38),
            'header' => imagecolorallocate($image, 17, 24, 39),
            'accent' => imagecolorallocate($image, 239, 246, 255),
        ];

        imagefilledrectangle($image, 0, 0, $width, $height, $colors['bg']);

        $cardLeft = 70;
        $cardTop = 70;
        $cardRight = $width - 70;
        $cardBottom = $height - 70;

        imagefilledrectangle($image, $cardLeft, $cardTop, $cardRight, $cardBottom, $colors['card']);
        imagerectangle($image, $cardLeft, $cardTop, $cardRight, $cardBottom, $colors['border']);

        imagefilledrectangle($image, $cardLeft, $cardTop, $cardRight, $cardTop + 14, $colors['primary']);

        $logoPath = public_path('logo.png');
        if (is_file($logoPath)) {
            $logo = @imagecreatefrompng($logoPath);
            if ($logo) {
                imagesavealpha($logo, true);
                $logoCanvas = imagecreatetruecolor(120, 120);
                imagealphablending($logoCanvas, false);
                imagesavealpha($logoCanvas, true);
                $transparent = imagecolorallocatealpha($logoCanvas, 0, 0, 0, 127);
                imagefill($logoCanvas, 0, 0, $transparent);
                imagecopyresampled(
                    $logoCanvas,
                    $logo,
                    0,
                    0,
                    0,
                    0,
                    120,
                    120,
                    imagesx($logo),
                    imagesy($logo)
                );
                imagecopy($image, $logoCanvas, 100, 110, 0, 0, 120, 120);
                imagedestroy($logoCanvas);
                imagedestroy($logo);
            }
        }

        $this->drawText($image, 'INVOICE', 250, 130, 6, $colors['header']);
        $this->drawText($image, 'No: ' . ($bill->invoice_number ?: 'INV-' . $bill->id), 250, 175, 4, $colors['muted']);

        $statusColor = $bill->status === 'lunas' ? $colors['success'] : $colors['danger'];
        imagefilledrectangle($image, 960, 120, 1090, 170, $statusColor);
        $this->drawCenteredText($image, strtoupper($bill->status), 1025, 138, 3, imagecolorallocate($image, 255, 255, 255));

        $this->drawSectionTitle($image, 'Data Tagihan', 100, 280, $colors);

        $this->drawLabelValue($image, 'Customer', $bill->customer->name ?? '-', 100, 340, $colors);
        $this->drawLabelValue($image, 'Grup', $bill->customer->group_name ?? '-', 620, 340, $colors);
        $this->drawLabelValue($image, 'Periode', Carbon::create()->month($bill->month)->translatedFormat('F') . ' ' . $bill->year, 100, 430, $colors);
        $this->drawLabelValue($image, 'Status', strtoupper($bill->status), 620, 430, $colors);

        imagefilledrectangle($image, 100, 540, 1100, 540, $colors['border']);

        $this->drawSectionTitle($image, 'Rincian Pemakaian', 100, 610, $colors);

        $tableY = 680;
        $columns = [
            ['label' => 'Meter Awal', 'value' => $bill->usage->meter_start ?? '-'],
            ['label' => 'Meter Akhir', 'value' => $bill->usage->meter_end ?? '-'],
            ['label' => 'Pemakaian', 'value' => $bill->usage->usage ?? '-'],
            ['label' => 'Total', 'value' => 'Rp ' . number_format($bill->total_bill, 0, ',', '.')],
        ];

        $columnWidth = 250;
        foreach ($columns as $index => $column) {
            $x1 = 100 + ($index * $columnWidth);
            $x2 = $x1 + $columnWidth - 20;
            imagefilledrectangle($image, $x1, $tableY, $x2, $tableY + 120, $colors['accent']);
            imagerectangle($image, $x1, $tableY, $x2, $tableY + 120, $colors['border']);
            $this->drawCenteredText($image, $column['label'], $x1 + (($x2 - $x1) / 2), $tableY + 28, 3, $colors['muted']);
            $this->drawCenteredText($image, (string) $column['value'], $x1 + (($x2 - $x1) / 2), $tableY + 70, 5, $colors['text']);
        }

        imagefilledrectangle($image, 100, 860, 1100, 860, $colors['border']);

        imagefilledrectangle($image, 100, 910, 1100, 1010, imagecolorallocate($image, 249, 250, 251));
        imagerectangle($image, 100, 910, 1100, 1010, $colors['border']);
        $this->drawText($image, 'Total Bayar', 130, 945, 5, $colors['muted']);
        $this->drawText($image, 'Rp ' . number_format($bill->total_bill, 0, ',', '.'), 130, 985, 6, $colors['primary']);

        if ($bill->status === 'lunas') {
            $this->drawLabelValue($image, 'Metode Pembayaran', strtoupper($bill->payment_method ?: '-'), 620, 935, $colors);
            $this->drawLabelValue($image, 'Tanggal Bayar', (string) ($bill->paid_at ?? '-'), 620, 990, $colors);
        }

        $this->drawText($image, 'Dicetak pada ' . now()->format('d M Y H:i'), 100, 1160, 2, $colors['muted']);
        $this->drawText($image, 'Nota ini dibuat otomatis dari sistem.', 100, 1190, 2, $colors['muted']);

        return $image;
    }

    private function drawSectionTitle($image, string $title, int $x, int $y, array $colors): void
    {
        $this->drawText($image, $title, $x, $y, 5, $colors['header']);
        imagefilledrectangle($image, $x, $y + 18, $x + 120, $y + 22, $colors['primary']);
    }

    private function drawLabelValue($image, string $label, string $value, int $x, int $y, array $colors): void
    {
        $this->drawText($image, $label, $x, $y, 3, $colors['muted']);
        $this->drawText($image, $value, $x, $y + 28, 5, $colors['text']);
    }

    private function drawText($image, string $text, int $x, int $y, int $font, int $color): void
    {
        imagestring($image, $font, $x, $y, $text, $color);
    }

    private function drawCenteredText($image, string $text, int $centerX, int $y, int $font, int $color): void
    {
        $textWidth = imagefontwidth($font) * strlen($text);
        $x = (int) ($centerX - ($textWidth / 2));
        imagestring($image, $font, $x, $y, $text, $color);
    }
}
