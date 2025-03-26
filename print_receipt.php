<?php
session_start();
require_once 'config/database.php';
require_once 'vendor/autoload.php';

// Add this at the top of print_receipt.php after session_start()
if (isset($_SESSION['logging_out']) || isset($_GET['logout'])) {
    // Clear all session variables
    $_SESSION = array();
    
    // Destroy the session cookie
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time()-3600, '/');
    }
    
    // Destroy the session
    session_destroy();
    
    // Redirect to login page
    header('Location: index.php');
    exit();
}

//start
if (isset($_SERVER['HTTP_CACHE_CONTROL']) && $_SERVER['HTTP_CACHE_CONTROL'] === 'max-age=0') {
    // Clear all session variables
    $_SESSION = array();
    
    // Destroy the session cookie
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time()-3600, '/');
    }
    
    // Destroy the session
    session_destroy();
    
    // Redirect to login page
    header('Location: index.php');
    exit();
}

// Add these headers right after the session check
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");

if (!isset($_SESSION['student_id'])) {
    header('Location: index.php');
    exit();
} //END

$student_id = $_SESSION['student_id'];
$semester = $_SESSION['semester'];

// Get student details
$query = "SELECT s.*, p.payment_id, p.amount, p.created_at as payment_date 
          FROM students s 
          JOIN payments p ON s.id = p.student_id 
          WHERE s.id = ? AND p.status = 'success' 
          ORDER BY p.created_at DESC LIMIT 1";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();

class PDF extends FPDF {
    private $semester;
    private $program;

    function __construct($semester, $program) {
        parent::__construct();
        $this->semester = $semester;
        $this->program = $program;
    }

    function Header() {
        // Add page border
        $this->SetLineWidth(0.4);
        $this->Rect(10, 10, 190, 277);
        $this->SetLineWidth(0.2);
        
        // University Logo
        $this->Image('assets/images/logo.png', 10, 10, 30);
        
        // University Name
        $this->SetFont('Arial', 'B', 16);
        $this->Cell(0, 10, 'KHANKIPUR UNIVERSITY', 0, 1, 'C');
        $this->SetFont('Arial', '', 12);
        
        // Format as "UG Semester 1 Examination"
        $this->Cell(0, 10, $this->program . ' Semester ' . $this->semester . ' Examination,2024', 0, 1, 'C');
        $this->Ln(10);
    }

    function Footer() {
        $this->SetY(-30);
        
        // Add signature image if exists
        if(file_exists('assets/images/controller_signature.png')) {
            $this->Image('assets/images/controller_signature.png', 145, $this->GetY() -8, 47);
        }
        
        $this->SetFont('Arial', 'B', 10);
        $this->Cell(0, 10, 'Deputy Controller of Examinations (I/C)', 0, 1, 'R');
    }

    function ExamSchedule($student) {
        $this->SetY(120);
        $this->SetFont('Arial', 'B', 11);
        $this->Cell(0, 8, 'EXAMINATION SCHEDULE', 0, 1, 'C');
        
        $startX = 15;
        $this->SetX($startX);
        
        // Define column widths
        $widths = array(
            'code' => 30,
            'name' => 85,
            'date' => 30,
            'time' => 30
        );
        
        // Table Headers
        $this->SetFont('Arial', 'B', 10);
        $this->Cell($widths['code'], 8, 'Paper Code', 1, 0, 'C');
        $this->Cell($widths['name'], 8, 'Paper Name', 1, 0, 'C');
        $this->Cell($widths['date'], 8, 'Date', 1, 0, 'C');
        $this->Cell($widths['time'], 8, 'Time', 1, 1, 'C');

        // Table Content
        $this->SetFont('Arial', '', 9);
        
        // Loop through papers 1-8
        for($i = 1; $i <= 8; $i++) {
            $code_field = "paper{$i}_code";
            $name_field = "paper{$i}_name";
            $date_field = "paper{$i}_exam_date";
            $time_field = "paper{$i}_exam_time";
            
            if(!empty($student[$code_field])) {
                $xPos = $startX;
                $yPos = $this->GetY();
                
                // Paper Code
                $this->SetXY($xPos, $yPos);
                $this->Cell($widths['code'], 8, $student[$code_field], 'LR', 0, 'C');
                
                // Save position for next columns
                $xPos += $widths['code'];
                
                // Paper Name with word wrap
                $this->SetXY($xPos, $yPos);
                $this->MultiCell($widths['name'], 8, $student[$name_field], 'LR');
                
                // Get height of the wrapped text
                $currentY = $this->GetY();
                $rowHeight = $currentY - $yPos;
                
                // Go back to draw other cells with matching height
                $this->SetXY($xPos + $widths['name'], $yPos);
                
                // Format date if exists
                $formatted_date = !empty($student[$date_field]) ? 
                                date('d-m-Y', strtotime($student[$date_field])) : 
                                'TBA';
                
                $this->Cell($widths['date'], $rowHeight, $formatted_date, 'LR', 0, 'C');
                $this->Cell($widths['time'], $rowHeight, $student[$time_field] ?? 'TBA', 'LR', 0, 'C');
                
                // Draw bottom line for all cells
                $this->Line($startX, $currentY, $startX + array_sum($widths), $currentY);
                
                // Move to next line
                $this->SetY($currentY);
            }
        }
    }

    // Add this helper function to calculate MultiCell height
    function GetMultiCellHeight($w, $h, $txt) {
        $text = str_replace("\r", '', $txt);
        $lines = explode("\n", $text);
        $height = 0;
        
        foreach($lines as $line) {
            $words = explode(' ', $line);
            $currentLine = '';
            
            foreach($words as $word) {
                $testLine = $currentLine . ' ' . $word;
                if($this->GetStringWidth($testLine) > $w) {
                    $height += $h;
                    $currentLine = $word;
                } else {
                    $currentLine = $testLine;
                }
            }
            $height += $h;
        }
        
        return $height;
    }
}

// Initialize PDF
$pdf = new PDF($semester, $student['program']);

// First Page - Payment Receipt
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(0, 10, 'PAYMENT RECEIPT', 0, 1, 'C');
$pdf->Ln(10);

$pdf->SetFont('Arial', '', 12);
$pdf->Cell(60, 8, 'Receipt No:', 0);
$pdf->Cell(0, 8, $student['payment_id'], 0, 1);

$pdf->Cell(60, 8, 'Date:', 0);
$pdf->Cell(0, 8, date('d-m-Y h:i A', strtotime($student['payment_date'])), 0, 1);

$pdf->Cell(60, 8, 'Student Name:', 0);
$pdf->Cell(0, 8, $student['name'], 0, 1);

$pdf->Cell(60, 8, 'Registration No:', 0);
$pdf->Cell(0, 8, $student['registration_number'], 0, 1);

$pdf->Cell(60, 8, 'Amount Paid:', 0);
$pdf->Cell(0, 8, 'Rs. ' . number_format($student['amount'], 2), 0, 1);

$pdf->Cell(60, 8, 'Payment Status:', 0);
$pdf->Cell(0, 8, 'Success', 0, 1);

// Second Page - Admit Card
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(0, 10, 'ADMIT CARD', 0, 1, 'C');
$pdf->Ln(10);

// Student Photo
$pdf->Image($student['photo_path'], 160, 40, 30);

$pdf->SetFont('Arial', '', 12);
$pdf->Cell(60, 8, 'Name:', 0);
$pdf->Cell(0, 8, $student['name'], 0, 1);

$pdf->Cell(60, 8, 'Registration No:', 0);
$pdf->Cell(0, 8, $student['registration_number'], 0, 1);

$pdf->Cell(60, 8, 'Roll No:', 0);
$pdf->Cell(0, 8, $student['roll_number'], 0, 1);

$pdf->Cell(60, 8, 'Course:', 0);
$pdf->Cell(0, 8, $student['course'], 0, 1);

$pdf->Cell(60, 8, 'Semester:', 0);
$pdf->Cell(0, 8, $semester, 0, 1);

$pdf->Cell(60, 8, 'Student Type:', 0);
$pdf->Cell(0, 8, ucfirst($student['student_type']), 0, 1);

$pdf->Ln(10);
$pdf->SetFont('Arial', 'B', 12);


// Update the call to ExamSchedule in the main PDF generation
$pdf->ExamSchedule($student);

// Add General Instructions
$pdf->Ln(4);
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 10, '(GENERAL INSTRUCTIONS FOR THE CANDIDATES)', 0, 1, 'C');

// Instructions content
$pdf->SetFont('Arial', '', 10);
$margin = 15;
$pdf->SetX($margin + 5);

// Add instructions with proper spacing
$instructions = array(
    "1. Candidates must bring their valid University ID card & admit card to the examination hall.",
    "2. If any paper description is incorrect / missing then inform the matter immediately to the office of the COE. All admit card correction must be done within the due date of examination form fillup..",
    "3. Mobile phones and other electronic devices are strictly prohibited.",
    "4. Candidates should arrive at least 30 minutes before the examination time.",
    "5. Only blue/black pen is allowed for writing the examination.",
    "6. No candidate will be allowed to leave the examination hall in the first 30 minutes.",   
    "7. The candidate must write on both sides of each page of the answer book.",
    "8. Any unfair means attempted or adopted by a candidate or breach of any of the rules above or any act of indiscipline will result in expulsion and cancellation of examination by the University.",
    "9. Any alteration made in the entries of the admit card without the authority of the university shall render the candidate disqualified to sit for this or any subsequent examination."
);

foreach($instructions as $instruction) {
    $pdf->MultiCell($pdf->GetPageWidth() - (2 * $margin), 6, $instruction, 0, 'L');
    $pdf->SetX($margin + 5);
}

// Output PDF
$pdf->Output('I', 'admit_card_' . $student['registration_number'] . '_' . $student['course'] . '_sem' . $semester . '.pdf');