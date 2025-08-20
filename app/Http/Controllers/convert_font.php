<?php
require __DIR__ . '/vendor/autoload.php';

use Fpdf\Fpdf;

// Define absolute path to font file (no storage_path())
$fontFile = __DIR__ . '/storage/fonts/iskoola-pota.ttf';

// Output directory for generated PHP font file
$outputDir = __DIR__ . '/storage/fonts';

if (!file_exists($fontFile)) {
    die("❌ Font file not found: $fontFile\n");
}

// Create FPDF instance
$pdf = new Fpdf();

// Add font (this will generate .php definition file inside $outputDir)
$pdf->AddFont('IskoolaPota', '', 'iskoola-pota.php');

// Register font
$pdf->SetFont('IskoolaPota', '', 14);

// Add a page
$pdf->AddPage();

// Write Sinhala text
$pdf->Cell(0, 10, 'සිංහල අකුරු පරික්ෂාව', 0, 1);

// Save test PDF
$pdf->Output('F', __DIR__ . '/storage/fonts/sinhala_test.pdf');

echo "✅ Font added successfully and PDF generated at storage/fonts/sinhala_test.pdf\n";
