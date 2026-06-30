<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

const APP_ROLES = ['student', 'staff', 'admin'];

function e($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function app_section() {
    $directory = basename(dirname($_SERVER['PHP_SELF'] ?? ''));
    return in_array($directory, APP_ROLES, true) ? $directory : '';
}

function app_base_path() {
    return app_section() === '' ? '' : '../';
}

function app_url($path = '') {
    return app_base_path() . ltrim($path, '/');
}

function app_redirect($path) {
    header('Location: ' . app_url($path));
    exit;
}

function normalize_role($role, $default = 'student') {
    return in_array($role, APP_ROLES, true) ? $role : $default;
}

function role_label($role) {
    return ucfirst(normalize_role($role));
}

function role_dashboard_path($role) {
    $role = normalize_role($role);
    return $role . '/dashboard.php';
}

function current_role() {
    return isset($_SESSION['role']) ? normalize_role($_SESSION['role']) : null;
}

function login_role_for_current_page() {
    return app_section() ?: 'student';
}

function account_source_for_role($role) {
    $role = normalize_role($role);

    if ($role === 'staff') {
        return ['table' => 'admission_staff', 'id' => 'staff_id'];
    }

    return ['table' => 'users', 'id' => 'user_id'];
}

function login_user($id, $role, $name, $email) {
    $_SESSION['user_id'] = $id;
    $_SESSION['role'] = normalize_role($role);
    $_SESSION['name'] = $name;
    $_SESSION['email'] = $email;
}

function logout_user() {
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly']
        );
    }

    session_destroy();
}

function session_account_exists($pdo) {
    if (empty($_SESSION['user_id']) || empty($_SESSION['role']) || !$pdo) {
        return true;
    }

    $source = account_source_for_role($_SESSION['role']);
    $sql = "SELECT {$source['id']} FROM {$source['table']} WHERE {$source['id']} = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id' => $_SESSION['user_id']]);

    return (bool) $stmt->fetchColumn();
}

function check_access($allowed_roles) {
    global $pdo;

    $allowed_roles = (array) $allowed_roles;

    if (!isset($_SESSION['role'])) {
        app_redirect('login.php?role=' . login_role_for_current_page());
    }

    if (isset($pdo) && !session_account_exists($pdo)) {
        logout_user();
        app_redirect('index.php');
    }

    if (!in_array($_SESSION['role'], $allowed_roles, true)) {
        app_redirect(role_dashboard_path($_SESSION['role']));
    }
}

function redirect_if_logged_in() {
    if (isset($_SESSION['role'])) {
        app_redirect(role_dashboard_path($_SESSION['role']));
    }
}

function render_topbar($title, $actions = '') {
    ?>
    <nav class="navbar navbar-expand-lg app-topbar">
        <div class="container-fluid">
            <button type="button" id="sidebarCollapse" class="btn topbar-toggle" aria-label="Toggle sidebar">
                <i class="fa-solid fa-bars"></i>
            </button>
            <span class="navbar-brand ms-3 d-flex align-items-center">
                <i class="fa-solid fa-graduation-cap text-primary me-2 fs-4"></i>
                <span class="fw-bold text-dark me-2">SCT Portal</span>
                <span class="text-muted border-start ps-2 d-none d-md-inline" style="font-size: 13px;">State College of Technology</span>
            </span>
            <span class="ms-3 fw-semibold text-primary d-none d-lg-inline-block">/ &nbsp;<?php echo e($title); ?></span>
            <?php if ($actions !== ''): ?>
                <div class="ms-auto topbar-actions">
                    <?php echo $actions; ?>
                </div>
            <?php endif; ?>
        </div>
    </nav>
    <?php
}

function render_alert($message, $type = 'success', $allow_html = false, $dismissible = false) {
    if ($message === '' || $message === null) {
        return;
    }

    $classes = 'alert alert-' . e($type);
    if ($dismissible) {
        $classes .= ' alert-dismissible fade show';
    }
    ?>
    <div class="<?php echo $classes; ?>" role="alert">
        <i class="fa-solid <?php echo $type === 'success' ? 'fa-circle-check' : 'fa-triangle-exclamation'; ?> me-2"></i>
        <?php echo $allow_html ? $message : e($message); ?>
        <?php if ($dismissible): ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        <?php endif; ?>
    </div>
    <?php
}
