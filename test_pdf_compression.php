<?php
/**
 * Script de diagnóstico y prueba de compresión de PDFs.
 * Accede desde: http://localhost/proformas-app/test_pdf_compression.php
 */

require_once __DIR__ . '/core/Autoload.php';

use Core\Compressor;

// Información del sistema
echo "=== DIAGNÓSTICO DE COMPRESIÓN DE PDFs ===\n\n";

echo "🔧 Herramientas disponibles:\n";

// Ghostscript
$gsAvailable = false;
$gsCommand = 'where gswin64c';
if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') {
    $gsCommand = 'which gs';
}
$output = [];
$returnCode = 0;
@exec($gsCommand, $output, $returnCode);
$gsAvailable = $returnCode === 0;
echo "  - Ghostscript: " . ($gsAvailable ? "✅ Disponible" : "❌ NO disponible") . "\n";

// qpdf
$qpdfAvailable = false;
$qpdfCommand = 'where qpdf';
if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') {
    $qpdfCommand = 'which qpdf';
}
$output = [];
$returnCode = 0;
@exec($qpdfCommand, $output, $returnCode);
$qpdfAvailable = $returnCode === 0;
echo "  - qpdf: " . ($qpdfAvailable ? "✅ Disponible" : "❌ NO disponible (usará fallback)") . "\n";

echo "\n📋 Método de compresión que se usará:\n";
if ($gsAvailable) {
    echo "  ➜ 1️⃣ Ghostscript (mejor compresión)\n";
} else if ($qpdfAvailable) {
    echo "  ➜ 2️⃣ qpdf (compresión media)\n";
} else {
    echo "  ➜ 3️⃣ PHP puro (elimina metadatos, 10-40% reducción)\n";
}

echo "\n" . str_repeat("=", 50) . "\n";

// Crear un PDF de prueba si existe uno
$testPdfPath = __DIR__ . '/test_compression_sample.pdf';

if (!file_exists($testPdfPath)) {
    echo "\n📄 Creando PDF de prueba...\n";

    // Crea un PDF muy simple con FPDF o lo hace con contenido raw
    $pdfContent = "%PDF-1.4\n";
    $pdfContent .= "1 0 obj<</Type/Catalog/Pages 2 0 R>>endobj\n";
    $pdfContent .= "2 0 obj<</Type/Pages/Kids[3 0 R]/Count 1>>endobj\n";
    $pdfContent .= "3 0 obj<</Type/Page/Parent 2 0 R/Resources<</Font<</F1 4 0 R>>>>/MediaBox[0 0 612 792]/Contents 5 0 R>>endobj\n";
    $pdfContent .= "4 0 obj<</Type/Font/Subtype/Type1/BaseFont/Helvetica>>endobj\n";
    $pdfContent .= "5 0 obj<</Length 44>>stream\n";
    $pdfContent .= "BT /F1 12 Tf 100 750 Td (TEST PDF) Tj ET\n";
    $pdfContent .= "endstream endobj\n";
    $pdfContent .= "xref\n";
    $pdfContent .= "0 6\n";
    $pdfContent .= "0000000000 65535 f\n";
    $pdfContent .= "0000000009 00000 n\n";
    $pdfContent .= "0000000058 00000 n\n";
    $pdfContent .= "0000000115 00000 n\n";
    $pdfContent .= "0000000233 00000 n\n";
    $pdfContent .= "0000000302 00000 n\n";
    $pdfContent .= "trailer<</Size 6/Root 1 0 R>>\n";
    $pdfContent .= "startxref\n";
    $pdfContent .= "393\n";
    $pdfContent .= "%%EOF\n";

    // Repite el contenido para hacer un archivo más grande (simula un PDF real)
    for ($i = 0; $i < 100; $i++) {
        $pdfContent .= "% Contenido de relleno para simular un PDF más grande\n";
        $pdfContent .= "% Esto es solo para propósitos de prueba\n";
    }

    file_put_contents($testPdfPath, $pdfContent);
    echo "✅ PDF de prueba creado: test_compression_sample.pdf\n";
}

$originalSize = filesize($testPdfPath);
echo "\n🧪 Prueba de compresión:\n";
echo "  Archivo: test_compression_sample.pdf\n";
echo "  Tamaño original: " . Compressor::formatBytes($originalSize) . "\n";

// Crea una copia para no destruir el original
$testCopy = $testPdfPath . '.backup';
copy($testPdfPath, $testCopy);

// Intenta comprimir
$result = Compressor::compress($testCopy, 'application/pdf');

echo "\n📊 Resultado:\n";
echo "  Éxito: " . ($result['success'] ? "✅ Sí" : "❌ No") . "\n";
echo "  Mensaje: " . $result['message'] . "\n";
echo "  Comprimido: " . ($result['compressed'] ? "✅ Sí" : "❌ No") . "\n";
echo "  Tamaño después: " . Compressor::formatBytes($result['sizeAfter']) . "\n";

if ($result['compressed']) {
    $reduction = (($result['sizeBefore'] - $result['sizeAfter']) / $result['sizeBefore']) * 100;
    echo "  Reducción: " . round($reduction, 1) . "%\n";
}

// Limpia archivo de prueba
unlink($testCopy);

echo "\n" . str_repeat("=", 50) . "\n";
echo "\n✅ PRÓXIMOS PASOS:\n";

if (!$gsAvailable && !$qpdfAvailable) {
    echo "  1. El sistema usará compresión PHP (reducción 10-40%)\n";
    echo "  2. Para mejor compresión, instala Ghostscript:\n";
    echo "     👉 https://www.ghostscript.com/download/gsdnld.html\n";
    echo "  3. O instala qpdf (más ligero):\n";
    echo "     👉 https://sourceforge.net/projects/qpdf/\n";
} else if (!$gsAvailable) {
    echo "  1. Usando: " . ($qpdfAvailable ? "qpdf" : "PHP puro") . "\n";
    echo "  2. Para mejor compresión, instala Ghostscript:\n";
    echo "     👉 https://www.ghostscript.com/download/gsdnld.html\n";
} else {
    echo "  ✅ Sistema completamente optimizado\n";
}

echo "\n💡 NOTA: Sube un PDF real de 3+ MB para ver compresión real\n";
echo "   Los PDFs muy pequeños pueden no comprimirse mucho\n";
