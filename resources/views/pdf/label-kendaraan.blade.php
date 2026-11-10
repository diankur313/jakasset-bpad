{{-- Label aset kendaraan dinas (AV-003). Dirender oleh App\Support\LabelAsset. --}}
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<style>
    @page { margin: {{ $satuan ? '0' : '10mm 10mm' }}; }
    body { margin: 0; font-family: "DejaVu Sans", sans-serif; color: #0f172a; }

    .label {
        width: {{ $lebarIsi }}mm;
        height: {{ $tinggiIsi }}mm;
        padding: {{ $padding }}mm;
        overflow: hidden;
        background: #ffffff;
    }
    .lembar .label { border: 0.25mm dashed #94a3b8; }

    .isi { width: 100%; height: 100%; border-collapse: collapse; }
    .isi td { vertical-align: middle; padding: 0; }
    .qr { width: 38mm; }
    .qr img { width: 36mm; height: 36mm; display: block; }

    .teks { padding-left: 2mm !important; }
    .instansi { font-size: 6.5pt; font-weight: bold; color: #0369a1; letter-spacing: 0.3pt; text-transform: uppercase; }
    .merek { font-size: 12pt; font-weight: bold; line-height: 1.15; margin-top: 1.2mm; }
    .tipe { font-size: 9pt; line-height: 1.2; margin-top: 0.6mm; }
    .tahun { font-size: 8pt; margin-top: 1.4mm; }
    .tahun b { font-size: 9pt; }
    .garis { border-top: 0.25mm solid #cbd5e1; margin: 1.8mm 0 1.2mm; }
    .kecil { font-size: 6.5pt; color: #334155; line-height: 1.35; }
    .mono { font-family: "DejaVu Sans Mono", monospace; }

    table.lembar-grid { border-collapse: separate; border-spacing: 4mm 3mm; margin: -3mm -4mm; }
    table.lembar-grid td { padding: 0; vertical-align: top; }
    .halaman-baru { page-break-after: always; }
</style>
</head>
<body>
@php
    $render = function (array $l): string {
        return view('pdf.partials.label-kendaraan-isi', ['l' => $l])->render();
    };
@endphp

@if ($satuan)
    <div class="label">{!! $render($labels->first()) !!}</div>
@else
    <div class="lembar">
        @foreach ($halaman as $baris)
            <table class="lembar-grid @if (! $loop->last) halaman-baru @endif">
                @foreach ($baris as $pasangan)
                    <tr>
                        @foreach ($pasangan as $l)
                            <td><div class="label">{!! $render($l) !!}</div></td>
                        @endforeach
                    </tr>
                @endforeach
            </table>
        @endforeach
    </div>
@endif
</body>
</html>
