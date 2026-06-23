<?php
// ====================================================================
// Database Backup & Restore Utility (admin/backup_restore.php)
// This page performs database exports and imports using native PHP
// script execution, avoiding command shell dependency.
// ====================================================================

require_once '../includes/db_connect.php';
require_once '../includes/auth.php';

// Verify admin access
check_access('admin');

$error_msg = "";
$success_msg = "";

// List of system tables to backup
$tables = ['users', 'courses', 'students', 'documents', 'admission_staff', 'status_history'];

// --------------------------------------------------------------------
// 1. PROCESS DATABASE BACKUP GENERATION
// --------------------------------------------------------------------
if (isset($_GET['action']) && $_GET['action'] === 'backup') {
    try {
        $sql_dump = "-- ====================================================================\n";
        $sql_dump .= "-- Student Admission Management System Backup File\n";
        $sql_dump .= "-- Date Generated: " . date('Y-m-d H:i:s') . "\n";
        $sql_dump .= "-- ====================================================================\n\n";
        
        $sql_dump .= "SET FOREIGN_KEY_CHECKS=0;\n\n";
        
        foreach ($tables as $table) {
            // Fetch structure (CREATE TABLE statement)
            $struct_stmt = $pdo->query("SHOW CREATE TABLE `$table`");
            $struct_row = $struct_stmt->fetch(PDO::FETCH_NUM);
            
            $sql_dump .= "-- Structure for table `$table`\n";
            $sql_dump .= "DROP TABLE IF EXISTS `$table`;\n";
            $sql_dump .= $struct_row[1] . ";\n\n";
            
            // Fetch rows (INSERT INTO statements)
            $data_stmt = $pdo->query("SELECT * FROM `$table`");
            $rows = $data_stmt->fetchAll(PDO::FETCH_NUM);
            
            if (!empty($rows)) {
                $sql_dump .= "-- Data dump for table `$table`\n";
                foreach ($rows as $row) {
                    $escaped_values = array_map(function($val) use ($pdo) {
                        if ($val === null) {
                            return 'NULL';
                        }
                        return $pdo->quote($val);
                    }, $row);
                    
                    $sql_dump .= "INSERT INTO `$table` VALUES (" . implode(', ', $escaped_values) . ");\n";
                }
                $sql_dump .= "\n";
            }
        }
        
        $sql_dump .= "SET FOREIGN_KEY_CHECKS=1;\n";
        
        // Output as downloadable file
        ob_end_clean();
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="admission_db_backup_' . date('Ymd_His') . '.sql"');
        echo $sql_dump;
        exit;
        
    } catch (PDOException $e) {
        $error_msg = "Backup Failed: " . $e->getMessage();
    }
}

// --------------------------------------------------------------------
// 2. PROCESS DATABASE RESTORE IMPORT
// --------------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'restore') {
    if (isset($_FILES['backup_file']) && $_FILES['backup_file']['error'] === UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['backup_file']['tmp_name'];
        $ext = strtolower(pathinfo($_FILES['backup_file']['name'], PATHINFO_EXTENSION));
        
        if ($ext !== 'sql') {
            $error_msg = "Please upload a valid .sql backup file.";
        } else {
            try {
                $sql_contents = file_get_contents($file_tmp);
                
                // Temporary transaction bypass for table drops/creates
                $pdo->exec("SET FOREIGN_KEY_CHECKS=0;");
                
                // Split queries by semicolon and newline
                // Replacing line endings to standard Unix line endings for easier split
                $sql_contents = str_replace("\r\n", "\n", $sql_contents);
                $queries = explode(";\n", $sql_contents);
                
                $executed = 0;
                foreach ($queries as $query) {
                    $query = trim($query);
                    if (!empty($query)) {
                        $pdo->exec($query);
                        $executed++;
                    }
                }
                
                $pdo->exec("SET FOREIGN_KEY_CHECKS=1;");
                $success_msg = "Database restored successfully. Executed " . $executed . " SQL query statements.";
            } catch (PDOException $e) {
                $pdo->exec("SET FOREIGN_KEY_CHECKS=1;");
                $error_msg = "Restore Failed: " . $e->getMessage();
            }
        }
    } else {
        $error_msg = "Please choose a SQL backup file to upload.";
    }
}

$page_title = "Database Backup & Restore";
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
                <span class="navbar-brand ms-3">Database Maintenance Desk</span>
            </div>
        </nav>

        <div class="container-fluid">
            <!-- Notifications -->
            <?php if (!empty($success_msg)): ?>
                <div class="alert alert-success" role="alert">
                    <i class="fa-solid fa-circle-check me-2"></i><?php echo $success_msg; ?>
                </div>
            <?php endif; ?>
            <?php if (!empty($error_msg)): ?>
                <div class="alert alert-danger" role="alert">
                    <i class="fa-solid fa-triangle-exclamation me-2"></i><?php echo $error_msg; ?>
                </div>
            <?php endif; ?>

            <div class="row g-4">
                <!-- Backup Panel -->
                <div class="col-md-6">
                    <div class="card-custom h-100">
                        <div class="card-header-custom">
                            <i class="fa-solid fa-download me-2 text-primary"></i>Export Database Backup
                        </div>
                        <div class="card-body-custom d-flex flex-column justify-content-between h-100">
                            <p class="text-muted">Generates and downloads a complete `.sql` backup file containing both the database schema structure (CREATE TABLE statements) and the data contents for all tables.</p>
                            <div class="alert alert-warning small">
                                <i class="fa-solid fa-triangle-exclamation me-1"></i><strong>Safety Note:</strong> Save your SQL backups securely as they contain candidate data and credentials hashes.
                            </div>
                            <div class="mt-4 pt-3 border-top">
                                <a href="backup_restore.php?action=backup" class="btn btn-custom-primary w-100 py-2 fw-bold">
                                    <i class="fa-solid fa-file-export me-1"></i>Download SQL Backup file
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Restore Panel -->
                <div class="col-md-6">
                    <div class="card-custom h-100">
                        <div class="card-header-custom">
                            <i class="fa-solid fa-upload me-2 text-danger"></i>Import Database Restore
                        </div>
                        <div class="card-body-custom">
                            <p class="text-muted">Upload an existing `.sql` backup file to overwrite current database records. All existing records will be dropped and replaced by the dump file contents.</p>
                            
                            <form action="backup_restore.php" method="POST" enctype="multipart/form-data">
                                <input type="hidden" name="action" value="restore">
                                
                                <div class="mb-3">
                                    <label for="backup_file" class="form-label form-label-custom">Select SQL Backup File</label>
                                    <input type="file" class="form-control form-control-custom" id="backup_file" name="backup_file" accept=".sql" required>
                                </div>
                                
                                <div class="alert alert-danger small">
                                    <i class="fa-solid fa-circle-exclamation me-1"></i><strong>Caution:</strong> Restoring database will drop existing tables. Make sure you back up current work first.
                                </div>

                                <div class="mt-4 pt-3 border-top">
                                    <button type="submit" class="btn btn-danger w-100 py-2 fw-bold" onclick="return confirm('DANGER: This will delete and recreate all tables and data. Are you sure you want to restore the database?');">
                                        <i class="fa-solid fa-file-import me-1"></i>Upload & Restore Database
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
