<?php
// ====================================================================
// Admission Reports Page (staff/reports.php)
// This page outputs student data tables filtered by Course and Status,
// and implements a complete CSV export facility.
// ====================================================================

require_once '../includes/db_connect.php';
require_once '../includes/auth.php';

// Verify that the user is logged in as staff
check_access('staff');

$course_filter = isset($_GET['course_filter']) ? trim($_GET['course_filter']) : '';
$status_filter = isset($_GET['status_filter']) ? trim($_GET['status_filter']) : '';

// --------------------------------------------------------------------
// CSV EXPORT PROCESSING
// --------------------------------------------------------------------
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    try {
        // Build export query
        $export_query = "
            SELECT s.admission_no, s.first_name, s.last_name, s.gender, s.dob, s.category, 
                   s.mobile, s.email, s.tenth_percentage, s.twelfth_percentage, 
                   s.school_name, s.passing_year, c.course_name, s.status, s.created_at
            FROM students s 
            LEFT JOIN courses c ON s.course_id = c.course_id 
            WHERE s.is_submitted = 1
        ";
        $export_params = [];

        if (!empty($course_filter)) {
            $export_query .= " AND s.course_id = :course_filter";
            $export_params['course_filter'] = $course_filter;
        }

        if (!empty($status_filter)) {
            $export_query .= " AND s.status = :status_filter";
            $export_params['status_filter'] = $status_filter;
        }

        $export_query .= " ORDER BY s.student_id DESC";
        
        $stmt = $pdo->prepare($export_query);
        $stmt->execute($export_params);
        $records = $stmt->fetchAll();

        // Clear output buffer to prevent stray HTML tags in CSV
        ob_end_clean();
        
        // Configure headers for CSV download
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=Student_Admission_Report_' . date('Ymd_His') . '.csv');
        
        // Open output stream
        $output = fopen('php://output', 'w');
        
        // Write Column Headers
        fputcsv($output, [
            'Admission ID', 'First Name', 'Last Name', 'Gender', 'DOB', 'Category', 
            'Mobile', 'Email', '10th %', '12th %', 'School Name', 'Passing Year', 
            'Course Selected', 'Status', 'Date Submitted'
        ]);
        
        // Write Data Rows
        foreach ($records as $row) {
            fputcsv($output, [
                $row['admission_no'],
                $row['first_name'],
                $row['last_name'],
                $row['gender'],
                $row['dob'],
                $row['category'],
                $row['mobile'],
                $row['email'],
                $row['tenth_percentage'],
                $row['twelfth_percentage'],
                $row['school_name'],
                $row['passing_year'],
                $row['course_name'],
                $row['status'],
                $row['created_at']
            ]);
        }
        
        fclose($output);
        exit;
        
    } catch (PDOException $e) {
        die("Export Error: " . $e->getMessage());
    }
}

// --------------------------------------------------------------------
// NORMAL HTML PAGE PROCESSING
// --------------------------------------------------------------------
try {
    // 1. Fetch courses for filters dropdown
    $courses = $pdo->query("SELECT * FROM courses ORDER BY course_name ASC")->fetchAll();

    // 2. Build list query based on filters
    $list_query = "
        SELECT s.*, c.course_name 
        FROM students s 
        LEFT JOIN courses c ON s.course_id = c.course_id 
        WHERE s.is_submitted = 1
    ";
    $list_params = [];

    if (!empty($course_filter)) {
        $list_query .= " AND s.course_id = :course_filter";
        $list_params['course_filter'] = $course_filter;
    }

    if (!empty($status_filter)) {
        $list_query .= " AND s.status = :status_filter";
        $list_params['status_filter'] = $status_filter;
    }

    $list_query .= " ORDER BY s.student_id DESC";
    
    $stmt = $pdo->prepare($list_query);
    $stmt->execute($list_params);
    $applicants = $stmt->fetchAll();

} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}

$page_title = "Admission Reports";
include '../includes/header.php';
?>

<div class="wrapper">
    <!-- Sidebar -->
    <?php include '../includes/sidebar.php'; ?>

    <!-- Page Content -->
    <div id="content">
        <!-- Top Navbar -->
        <nav class="navbar navbar-expand-lg navbar-custom">
            <div class="container-fluid">
                <button type="button" id="sidebarCollapse" class="btn btn-custom-primary">
                    <i class="fa-solid fa-bars"></i>
                </button>
                <span class="navbar-brand ms-3">Reports & Audits</span>
            </div>
        </nav>

        <div class="container-fluid">
            <!-- Filter Options Form -->
            <div class="card-custom mb-4">
                <div class="card-body-custom">
                    <form action="reports.php" method="GET" class="row g-3 align-items-end">
                        <!-- Course Filter -->
                        <div class="col-md-4">
                            <label for="course_filter" class="form-label form-label-custom">Filter by Course</label>
                            <select class="form-select form-control-custom" id="course_filter" name="course_filter">
                                <option value="">All Courses</option>
                                <?php foreach ($courses as $c): ?>
                                    <option value="<?php echo $c['course_id']; ?>" <?php echo ($course_filter == $c['course_id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($c['course_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <!-- Status Filter -->
                        <div class="col-md-4">
                            <label for="status_filter" class="form-label form-label-custom">Filter by Status</label>
                            <select class="form-select form-control-custom" id="status_filter" name="status_filter">
                                <option value="">All Statuses</option>
                                <option value="Pending" <?php echo ($status_filter === 'Pending') ? 'selected' : ''; ?>>Pending</option>
                                <option value="Approved" <?php echo ($status_filter === 'Approved') ? 'selected' : ''; ?>>Approved</option>
                                <option value="Rejected" <?php echo ($status_filter === 'Rejected') ? 'selected' : ''; ?>>Rejected</option>
                            </select>
                        </div>
                        <!-- Buttons -->
                        <div class="col-md-4 d-flex gap-2">
                            <button type="submit" class="btn btn-custom-primary flex-grow-1 py-2">
                                <i class="fa-solid fa-arrows-rotate me-1"></i>Apply Filters
                            </button>
                            <!-- Export Button -->
                            <a href="reports.php?course_filter=<?php echo $course_filter; ?>&status_filter=<?php echo $status_filter; ?>&export=csv" class="btn btn-success py-2 px-3" title="Export to CSV">
                                <i class="fa-solid fa-file-csv fs-5"></i> Export
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Report Results Table -->
            <div class="card-custom">
                <div class="card-header-custom">
                    <i class="fa-solid fa-file-invoice me-2"></i>Admission Records List
                </div>
                <div class="card-body-custom p-0">
                    <div class="table-responsive">
                        <table class="table table-custom align-middle">
                            <thead>
                                <tr>
                                    <th>Admission ID</th>
                                    <th>Student Name</th>
                                    <th>Applied Course</th>
                                    <th>Gender</th>
                                    <th>10th Std (%)</th>
                                    <th>12th Std (%)</th>
                                    <th>Status</th>
                                    <th>Date Applied</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($applicants)): ?>
                                    <tr>
                                        <td colspan="8" class="text-center text-muted py-4">No matching applicants found.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($applicants as $app): ?>
                                        <tr>
                                            <td class="fw-bold text-primary"><?php echo htmlspecialchars($app['admission_no']); ?></td>
                                            <td><?php echo htmlspecialchars($app['first_name'] . ' ' . $app['last_name']); ?></td>
                                            <td><?php echo htmlspecialchars($app['course_name']); ?></td>
                                            <td><?php echo htmlspecialchars($app['gender']); ?></td>
                                            <td><?php echo htmlspecialchars($app['tenth_percentage']); ?>%</td>
                                            <td><?php echo htmlspecialchars($app['twelfth_percentage']); ?>%</td>
                                            <td>
                                                <?php if ($app['status'] === 'Pending'): ?>
                                                    <span class="badge badge-pending">Pending</span>
                                                <?php elseif ($app['status'] === 'Approved'): ?>
                                                    <span class="badge badge-approved">Approved</span>
                                                <?php elseif ($app['status'] === 'Rejected'): ?>
                                                    <span class="badge badge-rejected">Rejected</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="small text-muted"><?php echo date('d-M-Y', strtotime($app['created_at'])); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
