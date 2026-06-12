<?php
require_once 'config/database.php';
$page_title = 'Job Management Admin Recruitment System';
$current_page = 'jobs';
$message = '';
$message_type = '';

if (isset($_POST['add_job'])) {
    $position = $conn->real_escape_string($_POST['position']);
    $department = $conn->real_escape_string($_POST['department']);
    $location = $conn->real_escape_string($_POST['location']);
    $status = $conn->real_escape_string($_POST['status']);
    
    $sql = "INSERT INTO jobs (position, department, location, status) VALUES ('$position', '$department', '$location', '$status')";
    if ($conn->query($sql)) {
        $message = 'Job berhasil ditambahkan!';
        $message_type = 'success';
    } else {
        $message = 'Gagal menambahkan job: ' . $conn->error;
        $message_type = 'danger';
    }
}

if (isset($_POST['edit_job'])) {
    $job_id = (int)$_POST['job_id'];
    $position = $conn->real_escape_string($_POST['position']);
    $department = $conn->real_escape_string($_POST['department']);
    $location = $conn->real_escape_string($_POST['location']);
    $status = $conn->real_escape_string($_POST['status']);

    $sql = "UPDATE jobs SET position='$position', department='$department', location='$location', status='$status' WHERE job_id=$job_id";
    if ($conn->query($sql)) {
        $message = 'Job berhasil diupdate!';
        $message_type = 'success';
    } else {
        $message = 'Gagal mengupdate job: ' . $conn->error;
        $message_type = 'danger';
    }
}

if (isset($_GET['delete'])) {
    $job_id = (int)$_GET['delete'];
    $sql = "DELETE FROM jobs WHERE job_id=$job_id";
    if ($conn->query($sql)) {
        $message = 'Job berhasil dihapus!';
        $message_type = 'success';
    } else {
        $message = 'Gagal menghapus job: ' . $conn->error;
        $message_type = 'danger';
    }
}

$jobs = $conn->query("SELECT * FROM jobs ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; color: #333333; font-family: sans-serif; min-height: 100vh; }
        .badge-status { padding: 6px 12px; border-radius: 50px; font-size: 12px; font-weight: 600; display: inline-block; text-transform: uppercase; }
        .badge-open { background-color: #d1e7dd; color: #0f5132; border: 1px solid #badbcc; }
        .badge-closed { background-color: #f8d7da; color: #842029; border: 1px solid #f5c2c7; }
        .table-responsive { border-radius: 8px; }
    </style>
</head>
<body>

    <!-- Navbar Bootstrap -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4 py-3">
        <div class="container">
            <a class="navbar-brand fw-bold text-primary" href="#">Recruitment<span class="text-white">System</span></a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item"><a class="nav-link <?= ($current_page == 'dashboard') ? 'active fw-bold text-white' : '' ?>" href="index.php">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link <?= ($current_page == 'jobs') ? 'active fw-bold text-white' : '' ?>" href="jobs.php">Jobs</a></li>
                    <li class="nav-item"><a class="nav-link <?= ($current_page == 'applicants') ? 'active fw-bold text-white' : '' ?>" href="applicants.php">Applicants</a></li>
                    <li class="nav-item"><a class="nav-link <?= ($current_page == 'interviews') ? 'active fw-bold text-white' : '' ?>" href="interviews.php">Interviews</a></li>
                    <li class="nav-item"><a class="nav-link <?= ($current_page == 'employees') ? 'active fw-bold text-white' : '' ?>" href="employees.php">Employees</a></li>
                    <li class="nav-item"><a class="nav-link <?= ($current_page == 'attendance') ? 'active fw-bold text-white' : '' ?>" href="attendance.php">Attendance</a></li>
                    <li class="nav-item"><a class="nav-link <?= ($current_page == 'payroll') ? 'active fw-bold text-white' : '' ?>" href="payroll.php">Payroll</a></li>
                    <li class="nav-item"><a class="nav-link <?= ($current_page == 'reports') ? 'active fw-bold text-white' : '' ?>" href="reports.php">Reports</a></li>
                </ul>
                <div class="d-flex align-items-center">
                    <a href="logout.php" class="btn btn-outline-danger btn-sm fw-bold px-3"><i class="bi bi-box-arrow-right me-1"></i> LOGOUT</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="container mb-5">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
            <div>
                <h1 class="h3 fw-bold text-dark mb-1">Job Management</h1>
                <p class="text-muted small mb-0">Kelola dan publikasikan data lowongan pekerjaan perusahaan.</p>
            </div>
            <div>
                <button class="btn btn-primary px-4 py-2 shadow-sm" data-bs-toggle="modal" data-bs-target="#addJobModal"><i class="bi bi-plus-lg me-2"></i>Add Job</button>
            </div>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-<?= $message_type ?> alert-dismissible fade show shadow-sm" role="alert">
                <?= $message ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="card-title mb-0 fw-bold text-secondary"><i class="bi bi-list-ul me-2 text-primary"></i>Daftar Lowongan Pekerjaan</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="px-4 py-3">Job ID</th>
                                <th class="py-3">Position</th>
                                <th class="py-3">Department</th>
                                <th class="py-3">Location</th>
                                <th class="py-3">Status</th>
                                <th class="px-4 py-3 text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($jobs->num_rows == 0): ?>
                                <tr><td colspan="6" class="text-center py-5 text-muted">Belum ada lowongan pekerjaan saat ini.</td></tr>
                            <?php else: ?>
                                <?php while ($row = $jobs->fetch_assoc()): ?>
                                <tr>
                                    <td class="px-4"><strong>#<?= $row['job_id']; ?></strong></td>
                                    <td class="fw-semibold text-dark"><?= htmlspecialchars($row['position']); ?></td>
                                    <td><span class="badge bg-secondary bg-opacity-10 text-secondary border px-2 py-1"><?= htmlspecialchars($row['department']); ?></span></td>
                                    <td><i class="bi bi-geo-alt text-muted me-1"></i><?= htmlspecialchars($row['location']); ?></td>
                                    <td><span class="badge-status badge-<?= strtolower($row['status']); ?>"><?= $row['status']; ?></span></td>
                                    <td class="px-4 text-center">
                                        <div class="d-flex gap-2 justify-content-center">
                                            <button class="btn btn-sm btn-outline-warning" data-bs-toggle="modal" data-bs-target="#editJobModal" onclick="editJob(<?= htmlspecialchars(json_encode($row)); ?>)"><i class="bi bi-pencil"></i></button>
                                            <a href="jobs.php?delete=<?= $row['job_id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus job ini?')"><i class="bi bi-trash"></i></a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Job Modal -->
    <div class="modal fade" id="addJobModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-light">
                    <h5 class="modal-title fw-bold"><i class="bi bi-plus-circle me-2 text-primary"></i>Add New Job</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST">
                    <div class="modal-body p-4">
                        <div class="row g-3">
                            <div class="col-12"><label class="form-label fw-semibold">Position</label><input type="text" name="position" class="form-control" required placeholder="e.g. Software Engineer"></div>
                            <div class="col-12"><label class="form-label fw-semibold">Department</label><input type="text" name="department" class="form-control" required placeholder="e.g. Engineering"></div>
                            <div class="col-12"><label class="form-label fw-semibold">Location</label><input type="text" name="location" class="form-control" required placeholder="e.g. Jakarta"></div>
                            <div class="col-12"><label class="form-label fw-semibold">Status</label><select name="status" class="form-select" required><option value="Open">Open</option><option value="Closed">Closed</option></select></div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light border-top-0">
                        <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="add_job" class="btn btn-primary px-4">Save Job</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Job Modal -->
    <div class="modal fade" id="editJobModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-light">
                    <h5 class="modal-title fw-bold"><i class="bi bi-pencil-square me-2 text-warning"></i>Edit Job</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST">
                    <input type="hidden" name="job_id" id="edit_job_id">
                    <div class="modal-body p-4">
                        <div class="row g-3">
                            <div class="col-12"><label class="form-label fw-semibold">Position</label><input type="text" name="position" id="edit_position" class="form-control" required></div>
                            <div class="col-12"><label class="form-label fw-semibold">Department</label><input type="text" name="department" id="edit_department" class="form-control" required></div>
                            <div class="col-12"><label class="form-label fw-semibold">Location</label><input type="text" name="location" id="edit_location" class="form-control" required></div>
                            <div class="col-12"><label class="form-label fw-semibold">Status</label><select name="status" id="edit_status" class="form-select" required><option value="Open">Open</option><option value="Closed">Closed</option></select></div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light border-top-0">
                        <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="edit_job" class="btn btn-primary px-4">Update Job</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
    function editJob(job) {
        document.getElementById('edit_job_id').value = job.job_id;
        document.getElementById('edit_position').value = job.position;
        document.getElementById('edit_department').value = job.department;
        document.getElementById('edit_location').value = job.location;
        document.getElementById('edit_status').value = job.status;
    }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>