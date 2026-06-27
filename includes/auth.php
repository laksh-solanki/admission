<?php
// ====================================================================
// Authentication and Authorization Helper File
// This file initializes sessions and verifies user login and roles.
// ====================================================================

// Start session if not already active
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

/**
 * Check if the user is logged in. If not, redirect to the appropriate login page.
 * If logged in but lacks the required role, redirect to an unauthorized page or dashboard.
 *
 * @param array|string $allowed_roles List of roles permitted to view the page.
 */
function check_access($allowed_roles) {
    // Verify user still exists in database (handles database resets/deletions)
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
            // User no longer exists in database (e.g. database was reset)
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

    // If a single role string is passed, convert it to an array
    if (!is_array($allowed_roles)) {
        $allowed_roles = [$allowed_roles];
    }
    
    // Check if the user is logged in by verifying if the role session is set
    if (!isset($_SESSION['role'])) {
        // Not logged in. Redirect to corresponding login page.
        // We guess which login page to redirect to based on the current directory.
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
    
    // User is logged in. Verify if their role is permitted.
    if (!in_array($_SESSION['role'], $allowed_roles)) {
        // Role not permitted. Redirect to their actual dashboard.
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

/**
 * Check if the user is already logged in, and redirect them to their dashboard
 * if they try to access login/registration pages.
 */
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

