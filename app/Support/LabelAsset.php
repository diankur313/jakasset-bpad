<?php

namespace App\Support;

use App\Models\KendaraanDinas;
use Barryvdh\DomPDF\Facade\Pdf;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Label aset kendaraan dinas (Added Value AV-003).
 *
 * - QR code berisi Nomor Asset (ID asset), dengan logo BPAD di tengah.
 *   Error correction level High (bisa memulihkan ±30% data), jadi QR tetap terbaca walau tertutup logo.
 * - Di samping QR: Merek, Tipe / Model, Tahun Produksi, Nomor Polisi, dan Nomor Asset.
 * - Satuan: PDF berukuran 1 label (90 x 45 mm).
 * - Batch: PDF A4 berisi 2 x 5 label per halaman dengan garis potong.
 */
class LabelAsset
{
    public const LOGO_PATH = 'images/logo-bpad.png';

    /** Ukuran label dalam milimeter. */
    public const LEBAR_MM = 90;

    public const TINGGI_MM = 45;

    /** Jumlah label per baris & per halaman pada lembar A4. */
    public const KOLOM_A4 = 2;

    public const LABEL_PER_HALAMAN_A4 = 10;

    /** Jarak tepi isi label (mm). */
    public const PADDING_MM = 3.5;

    public static function qrDataUri(string $data): string
    {
        $result = (new Builder(
            writer: new PngWriter,
            data: $data,
            errorCorrectionLevel: ErrorCorrectionLevel::High,
            size: 600,
            margin: 0,
            roundBlockSizeMode: RoundBlockSizeMode::Margin,
            logoPath: resource_path(self::LOGO_PATH),
            logoResizeToHeight: 150,
            // Kosongkan modul QR di belakang logo agar logo terlihat jelas.
            logoPunchoutBackground: true,
        ))->build();

        return $result->getDataUri();
    }

    /**
     * @param  Collection<int, KendaraanDinas>  $kendaraan
     */
    public static function download(Collection $kendaraan): StreamedResponse
    {
        // Bisa menerima koleksi biasa (mis. collect([$record])); diubah ke koleksi Eloquent untuk eager loading.
        $kendaraan = (new EloquentCollection($kendaraan->all()))
            ->filter(fn (KendaraanDinas $k): bool => filled($k->nomor_asset))
            ->loadMissing('masterKendaraan')
            ->values();

        $satuan = $kendaraan->count() === 1;

        $labels = $kendaraan->map(fn (KendaraanDinas $k): array => [
            'qr' => static::qrDataUri($k->nomor_asset),
            'merek' => $k->masterKendaraan?->merek,
            'tipe' => $k->masterKendaraan?->tipe_model,
            'tahun' => $k->masterKendaraan?->tahun_produksi,
            'nomor_polisi' => $k->nomor_polisi,
            'nomor_asset' => $k->nomor_asset,
        ]);

        $pdf = Pdf::loadView('pdf.label-kendaraan', [
            'satuan' => $satuan,
            'halaman' => $labels->chunk(self::LABEL_PER_HALAMAN_A4)->map(fn ($c) => $c->chunk(self::KOLOM_A4)),
            'labels' => $labels,
            // Dompdf tidak mendukung box-sizing, jadi ukuran isi = ukuran label - padding.
            'lebarIsi' => self::LEBAR_MM - 2 * self::PADDING_MM,
            'tinggiIsi' => self::TINGGI_MM - 2 * self::PADDING_MM,
            'padding' => self::PADDING_MM,
        ]);

        // Hanya karakter yang dipakai yang ditanam ke PDF, supaya ukuran file kecil.
        $pdf->setOption('isFontSubsettingEnabled', true);

        $pdf->setPaper($satuan ? [0, 0, self::mmKePt(self::LEBAR_MM), self::mmKePt(self::TINGGI_MM)] : 'a4');

        $namaFile = $satuan
            ? 'label-'.Str::slug(str_replace('/', '-', $kendaraan->first()->nomor_asset)).'.pdf'
            : 'label-kendaraan-dinas-'.now()->format('Ymd-His').'.pdf';

        return response()->streamDownload(fn () => print ($pdf->output()), $namaFile, [
            'Content-Type' => 'application/pdf',
        ]);
    }

    private static function mmKePt(float $mm): float
    {
        return $mm * 72 / 25.4;
    }
}
