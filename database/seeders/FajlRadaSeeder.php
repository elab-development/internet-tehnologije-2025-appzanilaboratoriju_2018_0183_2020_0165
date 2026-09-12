<?php

namespace Database\Seeders;

use App\Models\NaucniRad;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FajlRadaSeeder extends Seeder
{
    public function run(): void
    {
        $radovi = NaucniRad::with('autori')->get();

        foreach ($radovi as $rad) {
            $putanja = 'radovi/rad-' . $rad->NRID . '.pdf';

            Storage::disk('local')->put($putanja, $this->napraviPdf($rad));

            $rad->forceFill([
                'putanjaFajla' => $putanja,
                'imeFajla'     => Str::slug($rad->naslov) . '.pdf',
            ])->save();
        }
    }

    private function napraviPdf(NaucniRad $rad): string
    {
        $autori = $rad->spoljniAutori ?: $rad->autori->pluck('ImePrezime')->implode(', ');

        $redovi = array_merge(
            $this->prelomi($rad->naslov, 72),
            [''],
            $this->prelomi('Autori: ' . ($autori ?: 'nepoznati'), 88),
            $this->prelomi('Godina: ' . $rad->godina, 88),
            $this->prelomi('Kljucne reci: ' . $rad->kljucneReci, 88),
            [''],
            ['Apstrakt'],
            $this->prelomi($rad->abstrakt, 88),
            [''],
            $this->prelomi('Ovo je uzorak PDF-a koji generise seeder radi demonstracije preuzimanja i citanja radova u aplikaciji NILApp.', 88)
        );

        return $this->sastaviPdf($redovi);
    }

    private function prelomi(?string $tekst, int $sirina): array
    {
        if (empty($tekst)) {
            return [];
        }

        return explode("\n", wordwrap($this->uAscii($tekst), $sirina, "\n", true));
    }

    private function uAscii(string $tekst): string
    {
        $zamene = [
            'č' => 'c', 'ć' => 'c', 'š' => 's', 'ž' => 'z', 'đ' => 'dj',
            'Č' => 'C', 'Ć' => 'C', 'Š' => 'S', 'Ž' => 'Z', 'Đ' => 'Dj',
        ];

        return strtr($tekst, $zamene);
    }

    private function sastaviPdf(array $redovi): string
    {
        $tok = "BT\n/F1 11 Tf\n15 TL\n56 786 Td\n";

        foreach ($redovi as $red) {
            $tok .= '(' . $this->zastitiTekst($red) . ") Tj\nT*\n";
        }

        $tok .= 'ET';

        $objekti = [
            1 => '<< /Type /Catalog /Pages 2 0 R >>',
            2 => '<< /Type /Pages /Kids [3 0 R] /Count 1 >>',
            3 => '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] '
                . '/Resources << /Font << /F1 5 0 R >> >> /Contents 4 0 R >>',
            4 => '<< /Length ' . strlen($tok) . " >>\nstream\n" . $tok . "\nendstream",
            5 => '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica /Encoding /WinAnsiEncoding >>',
        ];

        $pdf = "%PDF-1.4\n";
        $pomeraji = [];

        foreach ($objekti as $broj => $telo) {
            $pomeraji[$broj] = strlen($pdf);
            $pdf .= $broj . " 0 obj\n" . $telo . "\nendobj\n";
        }

        $pocetakXref = strlen($pdf);
        $brojUnosa = count($objekti) + 1;

        $pdf .= "xref\n0 " . $brojUnosa . "\n0000000000 65535 f \n";

        foreach ($pomeraji as $pomeraj) {
            $pdf .= sprintf("%010d 00000 n \n", $pomeraj);
        }

        $pdf .= "trailer\n<< /Size " . $brojUnosa . " /Root 1 0 R >>\n";
        $pdf .= "startxref\n" . $pocetakXref . "\n%%EOF\n";

        return $pdf;
    }

    private function zastitiTekst(string $tekst): string
    {
        return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $tekst);
    }
}
