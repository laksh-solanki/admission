<?php
// ====================================================================
// Course Management Page (admin/manage_courses.php)
// This page handles adding, updating, displaying, and deleting academic
// courses in the system.
// ====================================================================

require_once '../includes/db_connect.php';
require_once '../includes/auth.php';

// Verify admin access
check_access('admin');

$error_msg = "";
$success_msg = "";

// --------------------------------------------------------------------
// PROCESS ACTIONS (ADD, EDIT, DELETE)
// --------------------------------------------------------------------

// 1. Delete Course
if (isset($_GET['delete_id']) && !empty($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    try {
        $stmt = $pdo->prepare("DELETE FROM courses WHERE course_id = :id");
        $stmt->execute(['id' => $delete_id]);
        $success_msg = "Course deleted successfully.";
    } catch (PDOException $e) {
        $error_msg = "Cannot delete course because it is referenced by existing student application records.";
    }
}

// 2. Add or Edit Course
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $course_name = trim($_POST['course_name']);
    $department = trim($_POST['department']);
    $semester = trim($_POST['semester']);
    $total_seats = intval($_POST['total_seats']);

    if (empty($course_name) || empty($department) || empty($semester) || $total_seats <= 0) {
        $error_msg = "Please fill in all course parameters correctly.";
    } else {
        if ($action === 'add') {
            try {
                $stmt = $pdo->prepare("INSERT INTO courses (course_name, department, semester, total_seats) VALUES (:name, :dept, :sem, :seats)");
                $stmt->execute([
                    'name' => $course_name,
                    'dept' => $department,
                    'sem' => $semester,
                    'seats' => $total_seats
                ]);
                $success_msg = "New course added successfully.";
            } catch (PDOException $e) {
                $error_msg = "Database Error: " . $e->getMessage();
            }
        } elseif ($action === 'edit') {
            $course_id = intval($_POST['course_id']);
            try {
                $stmt = $pdo->prepare("UPDATE courses SET course_name = :name, department = :dept, semester = :sem, total_seats = :seats WHERE course_id = :id");
                $stmt->execute([
                    'name' => $course_name,
                    'dept' => $department,
                    'sem' => $semester,
                    'seats' => $total_seats,
                    'id' => $course_id
                ]);
                $success_msg = "Course details updated successfully.";
            } catch (PDOException $e) {
                $error_msg = "Database Error: " . $e->getMessage();
            }
        }
    }
}

// Fetch all courses
try {
    $stmt = $pdo->query("SELECT * FROM courses ORDER BY course_id DESC");
    $courses = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}

$page_title = "Course Management";
include '../includes/header.php';
?>

<div class="wrapper">
    <!-- Sidebar -->
    <?php include '../includes/sidebar.php'; ?>

    <!-- Page Content -->
    <div id="content">
        <?php render_topbar('Course Management Portal', '<button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addCourseModal"><i class="fa-solid fa-plus me-1"></i>Add New Course</button>'); ?>

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

            <!-- Course Catalog Table -->
            <div class="card">
                <div class="card-header">
                    <i class="fa-solid fa-book-bookmark me-2"></i>Available Courses List
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table align-middle">
                            <thead>
                                <tr>
                                    <th># ID</th>
                                    <th>Course Name</th>
                                    <th>Department</th>
                                    <th>Semester</th>
                                    <th>Total Seats</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($courses)): ?>
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4">No courses registered yet. Click "Add New Course" to define one.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($courses as $c): ?>
                                        <tr>
                                            <td><?php echo $c['course_id']; ?></td>
                                            <td class="fw-bold text-primary"><?php echo e($c['course_name']); ?></td>
                                            <td><?php echo e($c['department']); ?></td>
                                            <td><?php echo e($c['semester']); ?></td>
                                            <td><?php echo e($c['total_seats']); ?></td>
                                            <td class="text-center">
                                                <!-- Edit Button triggers modal populate JS -->
                                                <button class="btn btn-sm btn-outline-secondary me-2" onclick="editCourse(<?php echo e(json_encode($c)); ?>)">
                                                    <i class="fa-solid fa-pen-to-square"></i> Edit
                                                </button>
                                                <!-- Delete Button -->
                                                <a href="manage_courses.php?delete_id=<?php echo $c['course_id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this course? This action is irreversible.');">
                                                    <i class="fa-solid fa-trash-can"></i> Delete
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

<!-- ====================================================================
     MODALS SECTION
     ==================================================================== -->

<!-- Add Course Modal -->
<div class="modal fade" id="addCourseModal" tabindex="-1" aria-labelledby="addCourseModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="addCourseModalLabel"><i class="fa-solid fa-plus me-1 text-primary"></i>Add Course</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="manage_courses.php" method="POST">
                <input type="hidden" name="action" value="add">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="course_name" class="form-label">Course / Degree Name</label>
                        <input type="text" class="form-control" id="course_name" name="course_name" placeholder="e.g. B.Sc. Computer Science" required>
                    </div>
                    <div class="mb-3">
                        <label for="department" class="form-label">Department Name</label>
                        <input type="text" class="form-control" id="department" name="department" placeholder="e.g. Science & IT" required>
                    </div>
                    <div class="mb-3">
                        <label for="semester" class="form-label">Semester / Duration</label>
                        <input type="text" class="form-control" id="semester" name="semester" placeholder="e.g. Semester I" required>
                    </div>
                    <div class="mb-3">
                        <label for="total_seats" class="form-label">Total Available Seats</label>
                        <input type="number" min="1" class="form-control" id="total_seats" name="total_seats" placeholder="e.g. 60" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary btn-sm">Add Course</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Course Modal -->
<div class="modal fade" id="editCourseModal" tabindex="-1" aria-labelledby="editCourseModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="editCourseModalLabel"><i class="fa-solid fa-pen-to-square me-1 text-primary"></i>Edit Course Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="manage_courses.php" method="POST">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" id="edit_course_id" name="course_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_course_name" class="form-label">Course / Degree Name</label>
                        <input type="text" class="form-control" id="edit_course_name" name="course_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_department" class="form-label">Department Name</label>
                        <input type="text" class="form-control" id="edit_department" name="department" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_semester" class="form-label">Semester / Duration</label>
                        <input type="text" class="form-control" id="edit_semester" name="semester" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_total_seats" class="form-label">Total Available Seats</label>
                        <input type="number" min="1" class="form-control" id="edit_total_seats" name="total_seats" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary btn-sm">Update Details</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Populate values in edit form modal dynamically -->
<script>
function editCourse(course) {
    document.getElementById('edit_course_id').value = course.course_id;
    document.getElementById('edit_course_name').value = course.course_name;
    document.getElementById('edit_department').value = course.department;
    document.getElementById('edit_semester').value = course.semester;
    document.getElementById('edit_total_seats').value = course.total_seats;
    
    // Programmatically trigger modal show
    const editModal = new bootstrap.Modal(document.getElementById('editCourseModal'));
    editModal.show();
}
</script>

<?php include '../includes/footer.php'; ?>

