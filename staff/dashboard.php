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
$course_filter = isset($_GET['course_filter']) ? trim($_GET['course_filter']) : '';
$sort_by = isset($_GET['sort_by']) ? trim($_GET['sort_by']) : 'newest';

try {
    // 1. Fetch dashboard statistics for staff
    $stats = [
        'total' => $pdo->query("SELECT COUNT(*) FROM students WHERE is_submitted = 1")->fetchColumn(),
        'pending' => $pdo->query("SELECT COUNT(*) FROM students WHERE is_submitted = 1 AND status = 'Pending'")->fetchColumn(),
        'approved' => $pdo->query("SELECT COUNT(*) FROM students WHERE is_submitted = 1 AND status = 'Approved'")->fetchColumn(),
        'rejected' => $pdo->query("SELECT COUNT(*) FROM students WHERE is_submitted = 1 AND status = 'Rejected'")->fetchColumn()
    ];

    // Fetch courses list
    $courses_list = $pdo->query("SELECT course_id, course_name FROM courses ORDER BY course_name ASC")->fetchAll();

    // 2. Build dynamic search query for applications
    $query = "
        SELECT s.*, c.course_name 
        FROM students s 
        LEFT JOIN courses c ON s.course_id = c.course_id 
        WHERE s.is_submitted = 1
    ";
    $params = [];

    if (!empty($search)) {
        $query .= " AND (s.admission_no LIKE :search1 
                     OR s.full_name LIKE :search2 
                     OR s.mobile LIKE :search3)";
        $params['search1'] = "%$search%";
        $params['search2'] = "%$search%";
        $params['search3'] = "%$search%";
    }

    if (!empty($status_filter)) {
        $query .= " AND s.status = :status_filter";
        $params['status_filter'] = $status_filter;
    }

    if (!empty($course_filter)) {
        $query .= " AND s.course_id = :course_filter";
        $params['course_filter'] = $course_filter;
    }

    // Sort order mapping
    $order_clause = " ORDER BY s.student_id DESC"; // default newest
    if ($sort_by === 'oldest') {
        $order_clause = " ORDER BY s.student_id ASC";
    } elseif ($sort_by === 'pct_high') {
        $order_clause = " ORDER BY s.twelfth_percentage DESC";
    } elseif ($sort_by === 'pct_low') {
        $order_clause = " ORDER BY s.twelfth_percentage ASC";
    } elseif ($sort_by === 'name_asc') {
        $order_clause = " ORDER BY s.full_name ASC";
    }

    $query .= $order_clause;
    
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
        <?php render_topbar('Staff Control Panel', '<span class="text-muted small"><i class="fa-solid fa-user-gear me-1"></i>' . e($_SESSION['name']) . ' (Staff)</span>'); ?>

        <div class="container-fluid">
            <!-- Stat Cards Row -->
            <div class="row g-4 mb-4">
                <!-- Total Applications -->
                <div class="col-md-3">
                    <div class="card bg-white p-3 stat-card courses">
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
                    <div class="card bg-white p-3 stat-card pending">
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
                    <div class="card bg-white p-3 stat-card approved">
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
                    <div class="card bg-white p-3 stat-card rejected">
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
            <div class="card mb-4 shadow-sm border-0">
                <div class="card-body">
                    <form action="dashboard.php" method="GET" class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label for="search" class="form-label">Search Applications</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                                <input type="text" class="form-control border-start-0" id="search" name="search" 
                                    placeholder="ID, Name, Mobile..." value="<?php echo e($search); ?>">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <label for="status_filter" class="form-label">Filter by Status</label>
                            <select class="form-select" id="status_filter" name="status_filter">
                                <option value="">All Statuses</option>
                                <option value="Pending" <?php echo ($status_filter === 'Pending') ? 'selected' : ''; ?>>Pending</option>
                                <option value="Approved" <?php echo ($status_filter === 'Approved') ? 'selected' : ''; ?>>Approved</option>
                                <option value="Rejected" <?php echo ($status_filter === 'Rejected') ? 'selected' : ''; ?>>Rejected</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="course_filter" class="form-label">Filter by Course</label>
                            <select class="form-select" id="course_filter" name="course_filter">
                                <option value="">All Courses</option>
                                <?php foreach ($courses_list as $c): ?>
                                    <option value="<?php echo $c['course_id']; ?>" <?php echo ($course_filter == $c['course_id']) ? 'selected' : ''; ?>>
                                        <?php echo e($c['course_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="sort_by" class="form-label">Sort By</label>
                            <select class="form-select" id="sort_by" name="sort_by">
                                <option value="newest" <?php echo ($sort_by === 'newest') ? 'selected' : ''; ?>>Newest First</option>
                                <option value="oldest" <?php echo ($sort_by === 'oldest') ? 'selected' : ''; ?>>Oldest First</option>
                                <option value="pct_high" <?php echo ($sort_by === 'pct_high') ? 'selected' : ''; ?>>12th Std (%) High-to-Low</option>
                                <option value="pct_low" <?php echo ($sort_by === 'pct_low') ? 'selected' : ''; ?>>12th Std (%) Low-to-High</option>
                                <option value="name_asc" <?php echo ($sort_by === 'name_asc') ? 'selected' : ''; ?>>Name: A to Z</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <a href="dashboard.php" class="btn btn-outline-secondary w-100 py-2" title="Reset"><i class="fa-solid fa-rotate-left me-1"></i>Reset</a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Student Applications List Table -->
            <div class="card">
                <div class="card-header">
                    <i class="fa-solid fa-table-list me-2"></i>Submitted Applications
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table align-middle">
                            <thead>
                                <tr>
                                    <th>Admission ID</th>
                                    <th>Student Name</th>
                                    <th>Course Applied</th>
                                    <th>Mobile</th>
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
                                            <td class="fw-bold text-primary"><?php echo e($app['admission_no']); ?></td>
                                            <td><?php echo e($app['full_name']); ?></td>
                                            <td><?php echo e($app['course_name']); ?></td>
                                            <td><?php echo e($app['mobile']); ?></td>   
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
                                                <a href="verify.php?id=<?php echo $app['student_id']; ?>" class="btn btn-sm btn-secondary">
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('search');
    if (searchInput) {
        // Restore focus and cursor position to the end if there's a search value
        if (searchInput.value) {
            searchInput.focus();
            const val = searchInput.value;
            searchInput.value = '';
            searchInput.value = val;
        }
        
        // Debounce typing in search input
        let timeout = null;
        searchInput.addEventListener('input', function() {
            clearTimeout(timeout);
            timeout = setTimeout(() => {
                searchInput.form.submit();
            }, 600); // 600ms debounce
        });
    }

    // Auto-submit form when any dropdown selection changes
    const selects = document.querySelectorAll('form select');
    selects.forEach(select => {
        select.addEventListener('change', function() {
            this.form.submit();
        });
    });
});
</script>
<?php include '../includes/footer.php'; ?>

