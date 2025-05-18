<?php
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="program_export.pdf"');
require_once 'db.php';

// Using TCPDF for PDF generation
require_once('../vendor/tecnickcom/tcpdf/tcpdf.php');

try {
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        throw new Exception('Invalid input data');
    }

    // Create new PDF document
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

    // Set document information
    $pdf->SetCreator('FMI Course Program Editor');
    $pdf->SetAuthor('FMI');
    $pdf->SetTitle($input['name']);

    // Set default header data
    $pdf->SetHeaderData('', 0, 'Учебна програма - ФМИ', $input['name']);

    // Set header and footer fonts
    $pdf->setHeaderFont(Array('dejavusans', '', 10));
    $pdf->setFooterFont(Array('dejavusans', '', 8));

    // Set default monospaced font
    $pdf->SetDefaultMonospacedFont('courier');

    // Set margins
    $pdf->SetMargins(15, 27, 15);
    $pdf->SetHeaderMargin(5);
    $pdf->SetFooterMargin(10);

    // Set auto page breaks
    $pdf->SetAutoPageBreak(TRUE, 25);

    // Add a page
    $pdf->AddPage();

    // Set font
    $pdf->SetFont('dejavusans', '', 10);

    // Basic program information
    $pdf->SetFont('dejavusans', 'B', 14);
    $pdf->Cell(0, 10, 'Основна информация', 0, 1, 'L');
    $pdf->SetFont('dejavusans', '', 10);
    $pdf->Cell(0, 10, 'Име на програмата: ' . $input['name'], 0, 1, 'L');
    $pdf->Cell(0, 10, 'Вид: ' . ($input['type'] === 'bachelor' ? 'Бакалавър' : 'Магистър'), 0, 1, 'L');
    $pdf->Ln(5);

    // Courses table
    $pdf->SetFont('dejavusans', 'B', 14);
    $pdf->Cell(0, 10, 'Дисциплини', 0, 1, 'L');
    $pdf->SetFont('dejavusans', '', 10);

    // Table header
    $pdf->SetFillColor(230, 230, 230);
    $pdf->Cell(80, 7, 'Име на дисциплината', 1, 0, 'C', true);
    $pdf->Cell(30, 7, 'Семестър', 1, 0, 'C', true);
    $pdf->Cell(30, 7, 'Кредити', 1, 0, 'C', true);
    $pdf->Cell(40, 7, 'Тип', 1, 1, 'C', true);

    // Sort courses by semester
    usort($input['courses'], function($a, $b) {
        return $a['semester'] - $b['semester'];
    });

    // Table content
    foreach ($input['courses'] as $course) {
        $pdf->Cell(80, 6, $course['name'], 1, 0, 'L');
        $pdf->Cell(30, 6, $course['semester'], 1, 0, 'C');
        $pdf->Cell(30, 6, $course['credits'], 1, 0, 'C');
        $type = '';
        switch ($course['type']) {
            case 'mandatory':
                $type = 'Задължителна';
                break;
            case 'optional':
                $type = 'Избираема';
                break;
            case 'facultative':
                $type = 'Факултативна';
                break;
        }
        $pdf->Cell(40, 6, $type, 1, 1, 'C');
    }

    $pdf->Ln(10);

    // Dependencies section
    if (!empty($input['dependencies'])) {
        $pdf->SetFont('dejavusans', 'B', 14);
        $pdf->Cell(0, 10, 'Зависимости между дисциплините', 0, 1, 'L');
        $pdf->SetFont('dejavusans', '', 10);

        $pdf->Cell(90, 7, 'Дисциплина', 1, 0, 'C', true);
        $pdf->Cell(90, 7, 'Зависи от', 1, 1, 'C', true);

        foreach ($input['dependencies'] as $dep) {
            $pdf->Cell(90, 6, $dep['to'], 1, 0, 'L');
            $pdf->Cell(90, 6, $dep['from'], 1, 1, 'L');
        }
    }

    // Output PDF
    $pdf->Output('program_export.pdf', 'I');
} catch (Exception $e) {
    // In case of error, return JSON error response
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?> 