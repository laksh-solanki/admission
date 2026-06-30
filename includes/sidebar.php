<?php
require_once __DIR__ . '/auth.php';

$current_page = basename($_SERVER['PHP_SELF']);
$role = current_role() ?: '';
$user_name = $_SESSION['name'] ?? 'Guest';

$nav_items = [
    'admin' => [
        ['href' => 'admin/dashboard.php', 'icon' => 'fa-gauge', 'label' => 'Dashboard', 'pages' => ['dashboard.php']],
        ['href' => 'admin/manage_courses.php', 'icon' => 'fa-book', 'label' => 'Courses', 'pages' => ['manage_courses.php']],
        ['href' => 'admin/manage_staff.php', 'icon' => 'fa-user-tie', 'label' => 'Staff', 'pages' => ['manage_staff.php']],
        ['href' => 'admin/manage_students.php', 'icon' => 'fa-users', 'label' => 'Students', 'pages' => ['manage_students.php', 'add_student.php', 'edit_student.php', 'view_students.php']],
    ],
    'staff' => [
        ['href' => 'staff/dashboard.php', 'icon' => 'fa-gauge', 'label' => 'Dashboard', 'pages' => ['dashboard.php']],
        ['href' => 'staff/verify.php', 'icon' => 'fa-id-card-clip', 'label' => 'Verify Applications', 'pages' => ['verify.php', 'verify_details.php']],
        ['href' => 'staff/reports.php', 'icon' => 'fa-chart-line', 'label' => 'Reports', 'pages' => ['reports.php']],
    ],
    'student' => [
        ['href' => 'student/dashboard.php', 'icon' => 'fa-gauge', 'label' => 'Dashboard', 'pages' => ['dashboard.php']],
        ['href' => 'student/apply.php', 'icon' => 'fa-file-signature', 'label' => 'Admission Form', 'pages' => ['apply.php']],
        ['href' => 'student/upload.php', 'icon' => 'fa-file-arrow-up', 'label' => 'Documents', 'pages' => ['upload.php']],
        ['href' => 'student/payment.php', 'icon' => 'fa-credit-card', 'label' => 'Payment', 'pages' => ['payment.php']],
        ['href' => 'student/status.php', 'icon' => 'fa-clock-rotate-left', 'label' => 'Status', 'pages' => ['status.php']],
    ],
];

$items = $nav_items[$role] ?? [];
if ($role !== '') {
    $items[] = ['href' => 'courses.php', 'icon' => 'fa-book-open', 'label' => 'Academic Courses', 'pages' => ['courses.php']];
}
?>
<nav id="sidebar">
    <div class="sidebar-header">
        <h4><i class="fa-solid fa-graduation-cap me-2"></i>College Portal</h4>
        <small>Welcome, <?php echo e($user_name); ?></small>
    </div>

    <ul class="list-unstyled components">
        <?php foreach ($items as $item): ?>
            <li class="<?php echo in_array($current_page, $item['pages'], true) ? 'active' : ''; ?>">
                <a href="<?php echo e(app_url($item['href'])); ?>">
                    <i class="fa-solid <?php echo e($item['icon']); ?>"></i>
                    <?php echo e($item['label']); ?>
                </a>
            </li>
        <?php endforeach; ?>

        <?php if (!empty($items)): ?>
            <li class="nav-divider" aria-hidden="true"></li>
        <?php endif; ?>

        <li class="<?php echo ($current_page === 'profile.php') ? 'active' : ''; ?>">
            <a href="<?php echo e(app_url('profile.php')); ?>"><i class="fa-solid fa-user-gear"></i> My Profile</a>
        </li>
        <li>
            <a href="<?php echo e(app_url('logout.php')); ?>" class="logout-link"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
        </li>
    </ul>
</nav>
