<?php
// ====================================================================
// Staff Dashboard Page (staff/dashboard.php)
// This page allows staff to view admission stats, search applications
// by ID, Name, or Mobile, and access verification profiles.
// ====================================================================

require_once '../includes/db_connect.php';
require_once '../includes/auth.php';

// Verify that the user is logged in as staff
check_access('staff');

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$status_filter = isset($_GET['status_filter']) ? trim($_GET['status_filter']) : '';

try {
    // 1. Fetch dashboard statistics for staff
    $stats = [
        'total' => $pdo->query("SELECT COUNT(*) FROM students WHERE is_submitted = 1")->fetchColumn(),
        'pending' => $pdo->query("SELECT COUNT(*) FROM students WHERE is_submitted = 1 AND status = 'Pending'")->fetchColumn(),
        'approved' => $pdo->query("SELECT COUNT(*) FROM students WHERE is_submitted = 1 AND status = 'Approved'")->fetchColumn(),
        'rejected' => $pdo->query("SELECT COUNT(*) FROM students WHERE is_submitted = 1 AND status = 'Rejected'")->fetchColumn()
    ];

    // 2. Build dynamic search query for applications
    $query = "
        SELECT s.*, c.course_name 
        FROM students s 
        LEFT JOIN courses c ON s.course_id = c.course_id 
        WHERE s.is_submitted = 1
    ";
    $params = [];

    if (!empty($search)) {
        $query .= " AND (s.admission_no LIKE :search 
                     OR s.full_name LIKE :search 
                     OR s.mobile LIKE :search)";
        $params['search'] = "%$search%";
    }

    if (!empty($status_filter)) {
        $query .= " AND s.status = :status_filter";
        $params['status_filter'] = $status_filter;
    }

    $query .= " ORDER BY s.student_id DESC";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $applications = $stmt->fetchAll();

} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}

$page_title = "Staff Dashboard";
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
                <span class="navbar-brand ms-3">Staff Control Panel</span>
                <div class="ms-auto d-flex align-items-center">
                    <span class="me-3 text-muted small"><i class="fa-solid fa-user-gear me-1"></i><?php echo htmlspecialchars($_SESSION['name']); ?> (Staff)</span>
                </div>
            </div>
        </nav>

        <div class="container-fluid">
            <!-- Stat Cards Row -->
            <div class="row g-4 mb-4">
                <!-- Total Applications -->
                <div class="col-md-3">
                    <div class="card-custom bg-white p-3 stat-card courses">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="text-muted small fw-bold">Submitted Apps</div>
                                <h3 class="fw-bold mb-0 mt-1"><?php echo $stats['total']; ?></h3>
                            </div>
                            <i class="fa-solid fa-folder-open stat-icon text-primary"></i>
                        </div>
                    </div>
                </div>
                <!-- Pending -->
                <div class="col-md-3">
                    <div class="card-custom bg-white p-3 stat-card pending">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="text-muted small fw-bold">Pending Review</div>
                                <h3 class="fw-bold mb-0 mt-1 text-warning"><?php echo $stats['pending']; ?></h3>
                            </div>
                            <i class="fa-solid fa-spinner fa-spin stat-icon text-warning"></i>
                        </div>
                    </div>
                </div>
                <!-- Approved -->
                <div class="col-md-3">
                    <div class="card-custom bg-white p-3 stat-card approved">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="text-muted small fw-bold">Approved Admissions</div>
                                <h3 class="fw-bold mb-0 mt-1 text-success"><?php echo $stats['approved']; ?></h3>
                            </div>
                            <i class="fa-solid fa-circle-check stat-icon text-success"></i>
                        </div>
                    </div>
                </div>
                <!-- Rejected -->
                <div class="col-md-3">
                    <div class="card-custom bg-white p-3 stat-card rejected">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="text-muted small fw-bold">Rejected Apps</div>
                                <h3 class="fw-bold mb-0 mt-1 text-danger"><?php echo $stats['rejected']; ?></h3>
                            </div>
                            <i class="fa-solid fa-circle-xmark stat-icon text-danger"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Search and Filtering Filter Bar -->
            <div class="card-custom mb-4">
                <div class="card-body-custom">
                    <form action="dashboard.php" method="GET" class="row g-3 align-items-end">
                        <div class="col-md-5">
                            <label for="search" class="form-label form-label-custom">Search Applications</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                                <input type="text" class="form-control form-control-custom border-start-0" id="search" name="search" 
                                    placeholder="Search by Admission ID, Name, or Mobile..." value="<?php echo htmlspecialchars($search); ?>">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label for="status_filter" class="form-label form-label-custom">Filter by Status</label>
                            <select class="form-select form-control-custom" id="status_filter" name="status_filter">
                                <option value="">All Applications</option>
                                <option value="Pending" <?php echo ($status_filter === 'Pending') ? 'selected' : ''; ?>>Pending</option>
                                <option value="Approved" <?php echo ($status_filter === 'Approved') ? 'selected' : ''; ?>>Approved</option>
                                <option value="Rejected" <?php echo ($status_filter === 'Rejected') ? 'selected' : ''; ?>>Rejected</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-custom-primary w-100 py-2"><i class="fa-solid fa-filter me-1"></i>Apply Filters</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Student Applications List Table -->
            <div class="card-custom">
                <div class="card-header-custom">
                    <i class="fa-solid fa-table-list me-2"></i>Submitted Applications
                </div>
                <div class="card-body-custom p-0">
                    <div class="table-responsive">
                        <table class="table table-custom align-middle">
                            <thead>
                                <tr>
                                    <th>Admission ID</th>
                                    <th>Student Name</th>
                                    <th>Course Applied</th>
                                    <th>Mobile</th>
                                    <th>12th Std (%)</th>
                                    <th>Status</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($applications)): ?>
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-4">No applications matching the criteria found.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($applications as $app): ?>
                                        <tr>
                                            <td class="fw-bold text-primary"><?php echo htmlspecialchars($app['admission_no']); ?></td>
                                            <td><?php echo htmlspecialchars($app['full_name']); ?></td>
                                            <td><?php echo htmlspecialchars($app['course_name']); ?></td>
                                            <td><?php echo htmlspecialchars($app['mobile']); ?></td>
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
                                            <td class="text-center">
                                                <a href="verify.php?id=<?php echo $app['student_id']; ?>" class="btn btn-sm btn-custom-secondary">
                                                    <i class="fa-solid fa-user-check me-1"></i>Verify Details
                                                </a>
                                            </td>
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
