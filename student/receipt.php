<?php
require_once '../includes/db_connect.php';
require_once '../includes/auth.php';

// Verify student role access
check_access('student');

$user_id = $_SESSION['user_id'];

try {
    // Fetch student profile, course name, and approval status
    $stmt = $pdo->prepare("
        SELECT s.*, c.course_name, c.department, c.semester 
        FROM students s 
        LEFT JOIN courses c ON s.course_id = c.course_id 
        WHERE s.user_id = :user_id
    ");
    $stmt->execute(['user_id' => $user_id]);
    $student = $stmt->fetch();

    // Prevent access if application is not found or is not approved
    if (!$student || $student['status'] !== 'Approved') {
        die("Unauthorized access: Your application is not approved yet.");
    }

} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}

// --------------------------------------------------------------------
// PDF GENERATION USING FPDF
// --------------------------------------------------------------------

require_once '../libs/fpdf.php';

class AdmissionPDF extends FPDF {
    // Page Header
    function Header() {
        // Logo-like Header Layout
        $this->SetFont('Arial', 'B', 18);
        $this->SetTextColor(15, 76, 129); // Royal Blue Theme color (#0f4c81)
        $this->Cell(0, 10, 'STATE COLLEGE OF TECHNOLOGY', 0, 1, 'C');
        
        $this->SetFont('Arial', '', 10);
        $this->SetTextColor(108, 117, 125);
        $this->Cell(0, 5, 'Affiliated to State Technical University | Estd. 1998', 0, 1, 'C');
        $this->Cell(0, 5, 'Website: www.statecollege.edu.in | Email: admissions@statecollege.edu.in', 0, 1, 'C');
        
        // Horizontal Line
        $this->SetDrawColor(220, 224, 230);
        $this->Line(10, 36, 200, 36);
        $this->Ln(8);
    }

    // Page Footer
    function Footer() {
        // Go to 1.5 cm from bottom
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->SetTextColor(108, 117, 125);
        
        // Page number & copyright
        $this->Cell(0, 10, 'Page ' . $this->PageNo() . ' | Generated Online by Admissions Portal - ' . date('d-M-Y H:i'), 0, 0, 'C');
    }
}

// Instantiate and build PDF
$pdf = new AdmissionPDF('P', 'mm', 'A4');
$pdf->SetMargins(15, 15, 15);
$pdf->AddPage();

// Receipt Document Title
$pdf->SetFont('Arial', 'B', 14);
$pdf->SetTextColor(33, 37, 41);
$pdf->Cell(0, 10, 'ADMISSION CONFIRMATION RECEIPT', 0, 1, 'C');
$pdf->Ln(2);

// Meta box: Admission Number & Date
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(95, 8, 'Admission No: ' . $student['admission_no'], 0, 0, 'L');
$pdf->Cell(75, 8, 'Date: ' . date('d-M-Y', strtotime($student['created_at'])), 0, 1, 'R');

$pdf->SetDrawColor(15, 76, 129);
$pdf->SetLineWidth(0.5);
$pdf->Line(15, 62, 195, 62);
$pdf->Ln(5);

// 1. Personal Details Segment
$pdf->SetFont('Arial', 'B', 11);
$pdf->SetTextColor(15, 76, 129);
$pdf->Cell(0, 8, '1. Candidate Personal Details', 0, 1, 'L');
$pdf->SetFont('Arial', '', 10);
$pdf->SetTextColor(33, 37, 41);

// Grid table structure
$pdf->Cell(45, 8, 'Student Full Name:', 0, 0, 'L');
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(135, 8, $student['full_name'], 0, 1, 'L');

$pdf->SetFont('Arial', '', 10);
$pdf->Cell(45, 8, "Father's Name:", 0, 0, 'L');
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(50, 8, $student['father_name'], 0, 0, 'L');
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(45, 8, "Mother's Name:", 0, 0, 'L');
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(50, 8, $student['mother_name'], 0, 1, 'L');

$pdf->SetFont('Arial', '', 10);
$pdf->Cell(45, 8, 'Gender:', 0, 0, 'L');
$pdf->Cell(50, 8, $student['gender'], 0, 0, 'L');
$pdf->Cell(45, 8, 'Date of Birth:', 0, 0, 'L');
$pdf->Cell(50, 8, date('d-M-Y', strtotime($student['dob'])), 0, 1, 'L');

$pdf->Cell(45, 8, 'Category:', 0, 0, 'L');
$pdf->Cell(50, 8, $student['category'], 0, 0, 'L');
$pdf->Cell(45, 8, 'Mobile No:', 0, 0, 'L');
$pdf->Cell(50, 8, $student['mobile'], 0, 1, 'L');

$pdf->Cell(45, 8, 'Email Address:', 0, 0, 'L');
$pdf->Cell(50, 8, $student['email'], 0, 0, 'L');
$pdf->Cell(45, 8, 'Pincode:', 0, 0, 'L');
$pdf->Cell(50, 8, $student['pincode'], 0, 1, 'L');

$pdf->Cell(45, 8, 'Full Address:', 0, 0, 'L');
$pdf->Cell(145, 8, $student['address'] . ", " . $student['city'] . ", " . $student['state'], 0, 1, 'L');
$pdf->Ln(4);

// 2. Academic Information Segment
$pdf->SetFont('Arial', 'B', 11);
$pdf->SetTextColor(15, 76, 129);
$pdf->Cell(0, 8, '2. Academic Qualifications', 0, 1, 'L');
$pdf->SetFont('Arial', '', 10);
$pdf->SetTextColor(33, 37, 41);

$pdf->Cell(45, 8, '10th Percentage:', 0, 0, 'L');
$pdf->Cell(50, 8, $student['tenth_percentage'] . '%', 0, 0, 'L');
$pdf->Cell(45, 8, '12th Percentage:', 0, 0, 'L');
$pdf->Cell(50, 8, $student['twelfth_percentage'] . '%', 0, 1, 'L');

$pdf->Cell(45, 8, 'School Board Name:', 0, 0, 'L');
$pdf->Cell(50, 8, $student['school_name'], 0, 0, 'L');
$pdf->Cell(45, 8, 'Passing Year:', 0, 0, 'L');
$pdf->Cell(50, 8, $student['passing_year'], 0, 1, 'L');
$pdf->Ln(4);

// 3. Course Details Segment
$pdf->SetFont('Arial', 'B', 11);
$pdf->SetTextColor(15, 76, 129);
$pdf->Cell(0, 8, '3. Allocated Course Information', 0, 1, 'L');
$pdf->SetFont('Arial', '', 10);
$pdf->SetTextColor(33, 37, 41);

$pdf->Cell(45, 8, 'Program Allocated:', 0, 0, 'L');
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(145, 8, $student['course_name'], 0, 1, 'L');
$pdf->SetFont('Arial', '', 10);

$pdf->Cell(45, 8, 'Department:', 0, 0, 'L');
$pdf->Cell(50, 8, $student['department'], 0, 1, 'L');
$pdf->Cell(45, 8, 'Semester:', 0, 0, 'L');
$pdf->Cell(50, 8, $student['semester'], 0, 0, 'L');
$pdf->Ln(15);

// Stamp and Signature Layout
$pdf->Ln(10);
$sig_y = $pdf->GetY();

$pdf->SetFont('Arial', 'B', 12);
$pdf->SetTextColor(40, 167, 69); // Success Green color
$pdf->Cell(90, 15, '[ STATUS: CONFIRMED / APPROVED ]', 0, 0, 'L');

// Draw signature line on the right side
$pdf->Line(135, $sig_y + 10, 195, $sig_y + 10);

$pdf->SetFont('Arial', '', 10);
$pdf->SetTextColor(33, 37, 41);
// Position signatory text below the signature line
$pdf->SetXY(135, $sig_y + 12);
$pdf->Cell(60, 8, 'Authorized Registrar Signatory', 0, 1, 'C');

$pdf->Ln(5);
$pdf->SetY($sig_y + 22);

$pdf->SetFont('Arial', 'I', 8);
$pdf->SetTextColor(108, 117, 125);
$pdf->Cell(0, 5, 'Note: This is a computer-generated confirmation slip. No manual signature is required.', 0, 1, 'C');

// Output PDF in inline mode download
$pdf->Output('I', 'Admission_Receipt_' . $student['admission_no'] . '.pdf');
?>

