<?php
// ====================================================================
// Admin Dashboard Page (admin/dashboard.php)
// This page outputs system-wide statistics (counts, courses, etc.) and
// renders interactive HTML5 graphs (gender and category distribution)
// using Chart.js.
// ====================================================================

require_once '../includes/db_connect.php';
require_once '../includes/auth.php';

// Verify admin role access
check_access('admin');

try {
    // 1. Fetch core stats counts
    $stats = [
        'total_apps' => $pdo->query("SELECT COUNT(*) FROM students WHERE is_submitted = 1")->fetchColumn(),
        'approved' => $pdo->query("SELECT COUNT(*) FROM students WHERE status = 'Approved'")->fetchColumn(),
        'pending' => $pdo->query("SELECT COUNT(*) FROM students WHERE status = 'Pending' AND is_submitted = 1")->fetchColumn(),
        'rejected' => $pdo->query("SELECT COUNT(*) FROM students WHERE status = 'Rejected'")->fetchColumn(),
        'courses' => $pdo->query("SELECT COUNT(*) FROM courses")->fetchColumn(),
        'staff' => $pdo->query("SELECT COUNT(*) FROM admission_staff")->fetchColumn()
    ];

    // 2. Fetch gender-wise statistics (only for submitted applications)
    $gender_stmt = $pdo->query("SELECT gender, COUNT(*) as count FROM students WHERE is_submitted = 1 GROUP BY gender");
    $gender_data = $gender_stmt->fetchAll();
    
    $gender_labels = [];
    $gender_counts = [];
    foreach ($gender_data as $g) {
        $gender_labels[] = $g['gender'];
        $gender_counts[] = (int)$g['count'];
    }

    // 3. Fetch category-wise statistics (only for submitted applications)
    $category_stmt = $pdo->query("SELECT category, COUNT(*) as count FROM students WHERE is_submitted = 1 GROUP BY category");
    $category_data = $category_stmt->fetchAll();
    
    $category_labels = [];
    $category_counts = [];
    foreach ($category_data as $c) {
        $category_labels[] = $c['category'];
        $category_counts[] = (int)$c['count'];
    }

} catch (PDOException $e) {
    die("Database Query Failed: " . $e->getMessage());
}

$page_title = "Admin Dashboard";
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
                <span class="navbar-brand ms-3">Administrator Command Center</span>
                <div class="ms-auto d-flex align-items-center">
                    <span class="badge bg-danger me-2"><i class="fa-solid fa-shield-halved me-1"></i>Secure Admin</span>
                    <span class="text-muted small"><?php echo htmlspecialchars($_SESSION['name']); ?></span>
                </div>
            </div>
        </nav>

        <div class="container-fluid">
            <!-- Stat Cards Row -->
            <div class="row g-4 mb-4">
                <!-- Total Applications -->
                <div class="col-md-4 col-xl-2">
                    <div class="card-custom bg-white p-3 stat-card courses">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <small class="text-muted fw-bold">Total Apps</small>
                                <h3 class="fw-bold mb-0 mt-1"><?php echo $stats['total_apps']; ?></h3>
                            </div>
                            <i class="fa-solid fa-file-invoice stat-icon text-primary"></i>
                        </div>
                    </div>
                </div>
                <!-- Pending -->
                <div class="col-md-4 col-xl-2">
                    <div class="card-custom bg-white p-3 stat-card pending">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <small class="text-muted fw-bold">Pending</small>
                                <h3 class="fw-bold mb-0 mt-1 text-warning"><?php echo $stats['pending']; ?></h3>
                            </div>
                            <i class="fa-solid fa-spinner fa-spin stat-icon text-warning"></i>
                        </div>
                    </div>
                </div>
                <!-- Approved -->
                <div class="col-md-4 col-xl-2">
                    <div class="card-custom bg-white p-3 stat-card approved">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <small class="text-muted fw-bold">Approved</small>
                                <h3 class="fw-bold mb-0 mt-1 text-success"><?php echo $stats['approved']; ?></h3>
                            </div>
                            <i class="fa-solid fa-circle-check stat-icon text-success"></i>
                        </div>
                    </div>
                </div>
                <!-- Rejected -->
                <div class="col-md-4 col-xl-2">
                    <div class="card-custom bg-white p-3 stat-card rejected">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <small class="text-muted fw-bold">Rejected</small>
                                <h3 class="fw-bold mb-0 mt-1 text-danger"><?php echo $stats['rejected']; ?></h3>
                            </div>
                            <i class="fa-solid fa-circle-xmark stat-icon text-danger"></i>
                        </div>
                    </div>
                </div>
                <!-- Courses -->
                <div class="col-md-4 col-xl-2">
                    <div class="card-custom bg-white p-3 stat-card courses">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <small class="text-muted fw-bold">Active Courses</small>
                                <h3 class="fw-bold mb-0 mt-1 text-info"><?php echo $stats['courses']; ?></h3>
                            </div>
                            <i class="fa-solid fa-book-bookmark stat-icon text-info"></i>
                        </div>
                    </div>
                </div>
                <!-- Staff -->
                <div class="col-md-4 col-xl-2">
                    <div class="card-custom bg-white p-3 stat-card approved">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <small class="text-muted fw-bold">Active Staff</small>
                                <h3 class="fw-bold mb-0 mt-1 text-purple" style="color: #6f42c1;"><?php echo $stats['staff']; ?></h3>
                            </div>
                            <i class="fa-solid fa-user-tie stat-icon text-purple" style="color: #6f42c1;"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Row -->
            <div class="row">
                <!-- Gender Stats Chart -->
                <div class="col-lg-6 mb-4">
                    <div class="card-custom h-100">
                        <div class="card-header-custom">
                            <i class="fa-solid fa-chart-pie me-2"></i>Gender-wise Application Statistics
                        </div>
                        <div class="card-body-custom d-flex justify-content-center align-items-center" style="position: relative; height:320px;">
                            <?php if (empty($gender_counts)): ?>
                                <p class="text-muted">No student applications submitted yet to map data.</p>
                            <?php else: ?>
                                <canvas id="genderChart"></canvas>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Category Stats Chart -->
                <div class="col-lg-6 mb-4">
                    <div class="card-custom h-100">
                        <div class="card-header-custom">
                            <i class="fa-solid fa-chart-bar me-2"></i>Category-wise Application Statistics
                        </div>
                        <div class="card-body-custom d-flex justify-content-center align-items-center" style="position: relative; height:320px;">
                            <?php if (empty($category_counts)): ?>
                                <p class="text-muted">No student applications submitted yet to map data.</p>
                            <?php else: ?>
                                <canvas id="categoryChart"></canvas>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Load Chart.js library via CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
document.addEventListener("DOMContentLoaded", function() {
    <?php if (!empty($gender_counts)): ?>
    // 1. Initialize Gender Chart
    const ctxGender = document.getElementById('genderChart').getContext('2d');
    new Chart(ctxGender, {
        type: 'doughnut',
        data: {
            labels: <?php echo json_encode($gender_labels); ?>,
            datasets: [{
                data: <?php echo json_encode($gender_counts); ?>,
                backgroundColor: ['#0f4c81', '#328cc1', '#ff9f1c', '#e71d36'],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
    <?php endif; ?>

    <?php if (!empty($category_counts)): ?>
    // 2. Initialize Category Chart
    const ctxCategory = document.getElementById('categoryChart').getContext('2d');
    new Chart(ctxCategory, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($category_labels); ?>,
            datasets: [{
                label: 'Number of Applicants',
                data: <?php echo json_encode($category_counts); ?>,
                backgroundColor: '#328cc1',
                borderColor: '#0f4c81',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });
    <?php endif; ?>
});
</script>

<?php include '../includes/footer.php'; ?>
