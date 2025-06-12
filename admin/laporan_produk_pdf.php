<?php
session_start();
require_once '../functions.php'; 
require_once '../proyek/lib/fpdf186/fpdf.php';
protect_admin_page(); 


//PDF Reporting
class PDF_MC_Table extends FPDF
{
    protected $widths;
    protected $aligns;

    function SetWidths($w)
    {
        $this->widths = $w;
    }

    function SetAligns($a)
    {
        $this->aligns = $a;
    }

    function Row($data)
    {
        $nb = 0;
        for ($i = 0; $i < count($data); $i++) {
            $nb = max($nb, $this->NbLines($this->widths[$i], $data[$i]));
        }
        $h = 5 * $nb;
        $this->CheckPageBreak($h);
        for ($i = 0; $i < count($data); $i++) {
            $w = $this->widths[$i];
            $a = isset($this->aligns[$i]) ? $this->aligns[$i] : 'L';
            $x = $this->GetX();
            $y = $this->GetY();
            $this->Rect($x, $y, $w, $h);
            $this->MultiCell($w, 5, $data[$i], 0, $a);
            $this->SetXY($x + $w, $y);
        }
        $this->Ln($h);
    }

    function CheckPageBreak($h)
    {
        if ($this->GetY() + $h > $this->PageBreakTrigger) {
            $this->AddPage($this->CurOrientation);
        }
    }

    function NbLines($w, $txt)
    {
        $cw = &$this->CurrentFont['cw'];
        if ($w == 0) {
            $w = $this->w - $this->rMargin - $this->x;
        }
        $wmax = ($w - 2 * $this->cMargin) * 1000 / $this->FontSize;
        $s = str_replace("\r", '', $txt);
        $nb = strlen($s);
        if ($nb > 0 && $s[$nb - 1] == "\n") {
            $nb--;
        }
        $sep = -1;
        $i = 0;
        $j = 0;
        $l = 0;
        $nl = 1;
        while ($i < $nb) {
            $c = $s[$i];
            if ($c == "\n") {
                $i++;
                $sep = -1;
                $j = $i;
                $l = 0;
                $nl++;
                continue;
            }
            if ($c == ' ') {
                $sep = $i;
            }
            $l += $cw[$c];
            if ($l > $wmax) {
                if ($sep == -1) {
                    if ($i == $j) {
                        $i++;
                    }
                } else {
                    $i = $sep + 1;
                }
                $sep = -1;
                $j = $i;
                $l = 0;
                $nl++;
            } else {
                $i++;
            }
        }
        return $nl;
    }

    function Header()
    {
        $this->Image('../img/logoasli.png', 10, 8, 33);
        $this->SetFont('Arial', 'B', 18);
        $this->Cell(80);
        $this->Cell(30, 10, 'Laporan Stok Produk', 0, 0, 'C');
        $this->SetFont('Arial', '', 12);
        $this->Ln(6);
        $this->Cell(80);
        $this->Cell(30, 10, 'BengkelinAja', 0, 0, 'C');
        $this->Ln(6);
        $this->SetFont('Arial', 'I', 10);
        $this->Cell(80);
        $this->Cell(30, 10, 'Dicetak pada: ' . date('d F Y, H:i:s'), 0, 0, 'C');
        $this->Ln(20);
    }

    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, 'Halaman ' . $this->PageNo() . '/{nb}', 0, 0, 'C');
    }
}


// untuk mengambil semua data produk untuk laporan
$produk_list_pdf = query("SELECT p.id, p.name, p.price, p.stock, c.nama_kategori 
                          FROM product p 
                          LEFT JOIN categories c ON p.category_id = c.id_kategori 
                          ORDER BY c.nama_kategori, p.name ASC");

if (!$produk_list_pdf) {
    die("Gagal mengambil data produk dari database.");
}

// membuat pdf
$pdf = new PDF_MC_Table();
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetFont('Arial', '', 10);

// Header Tabel
$pdf->SetFont('Arial', 'B', 10);
$pdf->SetFillColor(230, 230, 230); 
$pdf->SetTextColor(0);
$pdf->SetDrawColor(128, 128, 128);
$pdf->SetLineWidth(0.3);

// Lebar kolom tabel
$pdf->SetWidths(array(10, 85, 35, 20, 40));
// Perataan kolom
$pdf->SetAligns(array('C', 'L', 'R', 'C', 'L'));
// Data header
$header = array('ID', 'Nama Produk', 'Harga (Rp)', 'Stok', 'Kategori');

$pdf->Row($header);

// Body Tabel
$pdf->SetFont('Arial', '', 10);
$pdf->SetFillColor(255, 255, 255);
$fill = false;
$no = 1;

foreach ($produk_list_pdf as $row) {
    $data_row = array(
        $no++,
        iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $row['name']), 
        number_format($row['price'], 0, ',', '.'),
        $row['stock'],
        iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $row['nama_kategori'] ?? 'N/A')
    );
    $pdf->Row($data_row);
}

$filename = 'Laporan_Produk_BengkelinAja_' . date('Y-m-d') . '.pdf';
$pdf->Output('D', $filename);
