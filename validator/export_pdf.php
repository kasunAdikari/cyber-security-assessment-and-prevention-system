<?php
session_start();

// Security check
if (!isset($_SESSION['scan_results'])) {
    die("No report data found. Please generate a report first.");
}

$data = $_SESSION['scan_results'];
$case_title = $data['case_title'];
$sensitivity = $data['sensitivity'];
$issues = $data['issues'];
$model_text = $data['model_text'];
$generated_at = $data['generated_at'];

// Include FPDF
require_once('../fpdf/fpdf.php'); // Adjust path if needed, e.g., '../fpdf/fpdf.php'

// Create PDF instance
$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetAutoPageBreak(true, 20);

// Set fonts
$pdf->SetFont('Arial', 'B', 18);

// Title
$pdf->Cell(0, 15, 'Security Scan Report', 0, 1, 'C');
$pdf->Ln(5);

$pdf->SetFont('Arial', '', 12);
$pdf->Cell(0, 8, 'Case Title: ' . $case_title, 0, 1);
$pdf->Cell(0, 8, 'Generated: ' . $generated_at, 0, 1);
$pdf->Cell(0, 8, 'Sensitivity Level: ' . ucfirst($sensitivity), 0, 1);
$pdf->Ln(10);

if (!empty($issues)) {
    $pdf->SetFont('Arial', 'B', 14);
    $pdf->Cell(0, 10, count($issues) . ' Security Issue(s) Identified', 0, 1);
    $pdf->Ln(5);

    $pdf->SetFont('Arial', '', 11);

    foreach ($issues as $idx => $issue) {
        $title = $issue['title'] ?? "Issue #" . ($idx + 1);
        $risk = strtoupper($issue['risk'] ?? 'UNKNOWN');

        // Risk color mapping
        switch ($risk) {
            case 'HIGH':
                $pdf->SetFillColor(220, 53, 69);
                $pdf->SetTextColor(255, 255, 255);
                break;
            case 'MEDIUM':
            case 'MED':
                $pdf->SetFillColor(255, 193, 7);
                $pdf->SetTextColor(0, 0, 0);
                break;
            case 'LOW':
                $pdf->SetFillColor(23, 162, 184);
                $pdf->SetTextColor(255, 255, 255);
                break;
            default:
                $pdf->SetFillColor(108, 117, 125);
                $pdf->SetTextColor(255, 255, 255);
        }

        // Issue header
        $pdf->SetFont('Arial', 'B', 13);
        $pdf->Cell(0, 12, ($idx + 1) . '. ' . $title . '  [' . $risk . ' RISK]', 0, 1, 'L', true);
        
        // Reset text color
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFillColor(255, 255, 255);
        $pdf->Ln(4);

        // Remediation
        if (!empty($issue['remediation'])) {
            $pdf->SetFont('Arial', 'B', 11);
            $pdf->Cell(0, 8, 'Recommended Remediation:', 0, 1);
            $pdf->SetFont('Arial', '', 11);
            $pdf->MultiCell(0, 7, strip_tags($issue['remediation']));
            $pdf->Ln(4);
        }

        // Commands / Configuration
        if (!empty($issue['commands'])) {
            $pdf->SetFont('Arial', 'B', 11);
            $pdf->Cell(0, 8, 'Commands / Configuration:', 0, 1);
            $pdf->SetFont('Courier', '', 10);
            $commands = is_array($issue['commands']) ? implode("\n\n", $issue['commands']) : $issue['commands'];
            $pdf->MultiCell(0, 7, $commands, 1, 'L', true);
            $pdf->Ln(6);
            $pdf->SetFont('Arial', '', 11);
        }

        // References
        if (!empty($issue['references'])) {
            $pdf->SetFont('Arial', 'B', 11);
            $pdf->Cell(0, 8, 'References:', 0, 1);
            $pdf->SetFont('Arial', '', 10);
            foreach ((array)$issue['references'] as $ref) {
                $ref = trim($ref);
                if (filter_var($ref, FILTER_VALIDATE_URL)) {
                    $pdf->SetTextColor(0, 0, 255);
                    $pdf->Cell(0, 6, $ref, 0, 1, 'L', false, $ref);
                    $pdf->SetTextColor(0, 0, 0);
                } else {
                    $pdf->Cell(0, 6, $ref, 0, 1);
                }
            }
            $pdf->Ln(4);
        }

        // Notes
        if (!empty($issue['notes'])) {
            $pdf->SetFont('Arial', 'B', 11);
            $pdf->Cell(0, 8, 'Additional Notes:', 0, 1);
            $pdf->SetFont('Arial', '', 11);
            $pdf->MultiCell(0, 7, strip_tags($issue['notes']));
            $pdf->Ln(8);
        }

        // Optional: Add page break after each issue (remove if you want compact report)
        if ($idx < count($issues) - 1) {
            $pdf->AddPage();
        }
    }
} else {
    $pdf->SetFont('Arial', 'B', 14);
    $pdf->Cell(0, 10, 'No Structured Issues Detected', 0, 1);
    $pdf->Ln(8);
    $pdf->SetFont('Arial', '', 11);
    $pdf->MultiCell(0, 8, "Raw AI response:\n\n" . $model_text);
}

// Output PDF for download
$filename = 'Security_Report_' . preg_replace('/[^a-zA-Z0-9_-]/', '_', $case_title) . '_' . date('Y-m-d') . '.pdf';
$pdf->Output('D', $filename); // 'D' = force download

exit;