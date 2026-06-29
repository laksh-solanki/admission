<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

/**
 * Check if the user has access to the requested page based on their role.
 *
 * @param array|string $allowed_roles List of roles permitted to view the page.
 */

function check_access($allowed_roles) {
    global $pdo;
    if (isset($_SESSION['user_id']) && isset($_SESSION['role']) && isset($pdo)) {
        $user_id = $_SESSION['user_id'];
        $user_exists = false;
        
        if ($_SESSION['role'] === 'staff') {
            $stmt = $pdo->prepare("SELECT staff_id FROM admission_staff WHERE staff_id = :id");
            $stmt->execute(['id' => $user_id]);
            $user_exists = ($stmt->rowCount() > 0);
        } else {
            $stmt = $pdo->prepare("SELECT user_id FROM users WHERE user_id = :id");
            $stmt->execute(['id' => $user_id]);
            $user_exists = ($stmt->rowCount() > 0);
        }
        
        if (!$user_exists) {
            session_unset();
            session_destroy();
            $current_path = $_SERVER['PHP_SELF'];
            if (strpos($current_path, '/admin/') !== false || strpos($current_path, '/staff/') !== false || strpos($current_path, '/student/') !== false) {
                header("Location: ../index.php");
            } else {
                header("Location: index.php");
            }
            exit;
        }
    }

    if (!is_array($allowed_roles)) {
        $allowed_roles = [$allowed_roles];
    }

    if (!isset($_SESSION['role'])) {

        $current_path = $_SERVER['PHP_SELF'];
        
        if (strpos($current_path, '/admin/') !== false) {
            header("Location: ../login.php?role=admin");
        } elseif (strpos($current_path, '/staff/') !== false) {
            header("Location: ../login.php?role=staff");
        } else {
            header("Location: ../login.php?role=student");
        }
        exit;
    }
    
    if (!in_array($_SESSION['role'], $allowed_roles)) {
        if ($_SESSION['role'] === 'admin') {
            header("Location: ../admin/dashboard.php");
        } elseif ($_SESSION['role'] === 'staff') {
            header("Location: ../staff/dashboard.php");
        } elseif ($_SESSION['role'] === 'student') {
            header("Location: ../student/dashboard.php");
        } else {
            header("Location: ../index.php");
        }
        exit;
    }
}

function redirect_if_logged_in() {
    if (isset($_SESSION['role'])) {
        if ($_SESSION['role'] === 'admin') {
            header("Location: admin/dashboard.php");
        } elseif ($_SESSION['role'] === 'staff') {
            header("Location: staff/dashboard.php");
        } elseif ($_SESSION['role'] === 'student') {
            header("Location: student/dashboard.php");
        }
        exit;
    }
}
?>

