<?php

namespace App\Services;

use BaconQrCode\Common\ErrorCorrectionLevel;
use BaconQrCode\Encoder\Encoder;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class QrCodeService
{
    /**
     * QR sebagai SVG untuk halaman tiket (PRD 7.5). Tidak disimpan sebagai file.
     */
    public function svg(string $content, int $size = 320): string
    {
        return (string) QrCode::format('svg')
            ->size($size)
            ->margin(1)
            ->errorCorrection('M')
            ->generate($content);
    }

    /**
     * QR sebagai PNG biner untuk dilampirkan inline pada email.
     * Dibuat dari matriks QR Bacon (GD), karena backend Imagick tidak tersedia.
     */
    public function png(string $content, int $size = 320, int $margin = 2): string
    {
        $matrix = Encoder::encode($content, ErrorCorrectionLevel::M())->getMatrix();
        $modules = $matrix->getWidth();
        $scale = max(1, intdiv($size, $modules + ($margin * 2)));
        $dimension = ($modules + ($margin * 2)) * $scale;

        $image = imagecreatetruecolor($dimension, $dimension);
        $white = imagecolorallocate($image, 255, 255, 255);
        $black = imagecolorallocate($image, 0, 0, 0);
        imagefilledrectangle($image, 0, 0, $dimension, $dimension, $white);

        for ($y = 0; $y < $modules; $y++) {
            for ($x = 0; $x < $modules; $x++) {
                if ($matrix->get($x, $y) !== 0) {
                    $left = ($x + $margin) * $scale;
                    $top = ($y + $margin) * $scale;
                    imagefilledrectangle($image, $left, $top, $left + $scale - 1, $top + $scale - 1, $black);
                }
            }
        }

        ob_start();
        imagepng($image);
        $png = (string) ob_get_clean();
        imagedestroy($image);

        return $png;
    }
}
