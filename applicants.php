<?php
require_once 'config/database.php';
$page_title = 'Applicant Management Admin Recruitment System';
$current_page = 'applicants';
$message = '';
$message_type = '';

if (isset($_POST['add_applicant'])) {
    $full_name = $conn->real_escape_string($_POST['full_name']);
    $email = $conn->real_escape_string($_POST['email']);
    $phone_number = $conn->real_escape_string($_POST['phone_number']);
    $education = $conn->real_escape_string($_POST['education']);
    $job_id = !empty($_POST['job_id']) ? (int)$_POST['job_id'] : 'NULL';
    $status = $conn->real_escape_string($_POST['status']);
    
    $sql = "INSERT INTO applicants (full_name, email, phone_number, education, job_id, status) VALUES ('$full_name', '$email', '$phone_number', '$education', $job_id, '$status')";
    if ($conn->query($sql)) {
        $message = 'Applicant berhasil ditambahkan!';
        $message_type = 'success';
    } else {
        $message = 'Gagal menambahkan applicant: ' . $conn->error;
        $message_type = 'danger';
    }
}

if (isset($_POST['edit_applicant'])) {
    $applicant_id = (int)$_POST['applicant_id'];
    $full_name = $conn->real_escape_string($_POST['full_name']);
    $email = $conn->real_escape_string($_POST['email']);
    $phone_number = $conn->real_escape_string($_POST['phone_number']);
    $education = $conn->real_escape_string($_POST['education']);
    $job_id = !empty($_POST['job_id']) ? (int)$_POST['job_id'] : 'NULL';
    $status = $conn->real_escape_string($_POST['status']);

    $sql = "UPDATE applicants SET full_name='$full_name', email='$email', phone_number='$phone_number', education='$education', job_id=$job_id, status='$status' WHERE applicant_id=$applicant_id";
    if ($conn->query($sql)) {
        $message = 'Applicant berhasil diupdate!';
        $message_type = 'success';
    } else {
        $message = 'Gagal mengupdate applicant: ' . $conn->error;
        $message_type = 'danger';
    }
}

if (isset($_GET['delete'])) {
    $applicant_id = (int)$_GET['delete'];
    $sql = "DELETE FROM applicants WHERE applicant_id=$applicant_id";
    if ($conn->query($sql)) {
        $message = 'Applicant berhasil dihapus!';
        $message_type = 'success';
    } else {
        $message = 'Gagal menghapus applicant: ' . $conn->error;
        $message_type = 'danger';
    }
}

$applicants_query = $conn->query("SELECT a.*, j.position FROM applicants a LEFT JOIN jobs j ON a.job_id = j.job_id ORDER BY a.applicant_id ASC");
$applicants_list = [];
while ($row = $applicants_query->fetch_assoc()) {
    $applicants_list[] = $row;
}

$jobs_query = $conn->query("SELECT job_id, position FROM jobs WHERE status = 'Open' ORDER BY position");
$jobs_list = [];
while ($job = $jobs_query->fetch_assoc()) {
    $jobs_list[] = $job;
}
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
        .badge-applied { background-color: #e9ecef; color: #495057; border: 1px solid #ced4da; }
        .badge-interview { background-color: #fff3cd; color: #664d03; border: 1px solid #ffecb5; }
        .badge-hired { background-color: #d1e7dd; color: #0f5132; border: 1px solid #badbcc; }
        .badge-rejected { background-color: #f8d7da; color: #842029; border: 1px solid #f5c2c7; }
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
                <h1 class="h3 fw-bold text-dark mb-1">Applicant Management</h1>
                <p class="text-muted small mb-0">Kelola data pelamar pekerjaan secara efisien.</p>
            </div>
            <div>
                <button class="btn btn-primary px-4 py-2 shadow-sm" data-bs-toggle="modal" data-bs-target="#addApplicantModal"><i class="bi bi-plus-lg me-2"></i>Add Applicant</button>
            </div>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-<?= $message_type ?> alert-dismissible fade show shadow-sm" role="alert">
                <?= $message ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="card-title mb-0 fw-bold text-secondary"><i class="bi bi-list-ul me-2 text-primary"></i>Daftar Pelamar</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="px-4 py-3">ID</th>
                                <th class="py-3">Full Name</th>
                                <th class="py-3">Email</th>
                                <th class="py-3">Phone Number</th>
                                <th class="py-3">Education</th>
                                <th class="py-3">Position Applied</th>
                                <th class="py-3">Status</th>
                                <th class="px-4 py-3 text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($applicants_list)): ?>
                                <tr><td colspan="8" class="text-center py-5 text-muted">Belum ada data pelamar saat ini.</td></tr>
                            <?php else: ?>
                                <?php foreach ($applicants_list as $row): ?>
                                <tr>
                                    <td class="px-4"><strong>#<?= $row['applicant_id'] ?></strong></td>
                                    <td class="fw-semibold"><?= htmlspecialchars($row['full_name']) ?></td>
                                    <td><?= htmlspecialchars($row['email']) ?></td>
                                    <td><?= htmlspecialchars($row['phone_number']) ?></td>
                                    <td><?= htmlspecialchars($row['education']) ?></td>
                                    <td><span class="badge bg-secondary bg-opacity-10 text-secondary border px-2 py-1"><?= htmlspecialchars($row['position'] ?? 'N/A') ?></span></td>
                                    <td><span class="badge-status badge-<?= strtolower($row['status']) ?>"><?= $row['status'] ?></span></td>
                                    <td class="px-4 text-center">
                                        <div class="d-flex gap-2 justify-content-center">
                                            <button class="btn btn-sm btn-outline-warning" data-bs-toggle="modal" data-bs-target="#editApplicantModal" onclick="editApplicant(<?= htmlspecialchars(json_encode($row)) ?>)"><i class="bi bi-pencil"></i></button>
                                            <a href="applicants.php?delete=<?= $row['applicant_id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus applicant ini?')"><i class="bi bi-trash"></i></a>
                                        </div>
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

    <!-- Add Applicant Modal -->
    <div class="modal fade" id="addApplicantModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-light">
                    <h5 class="modal-title fw-bold"><i class="bi bi-plus-circle me-2 text-primary"></i>Add New Applicant</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST">
                    <div class="modal-body p-4">
                        <div class="row g-3">
                            <div class="col-md-6"><label class="form-label fw-semibold">Full Name</label><input type="text" name="full_name" class="form-control" required placeholder="Nama lengkap"></div>
                            <div class="col-md-6"><label class="form-label fw-semibold">Email</label><input type="email" name="email" class="form-control" required placeholder="email@example.com"></div>
                            <div class="col-md-6"><label class="form-label fw-semibold">Phone Number</label><input type="text" name="phone_number" class="form-control" required placeholder="08xxxxxxxxxx"></div>
                            <div class="col-md-6"><label class="form-label fw-semibold">Education</label><input type="text" name="education" class="form-control" required placeholder="e.g. S1 Teknik Informatika"></div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Position Applied</label>
                                <select name="job_id" class="form-select">
                                    <option value="">-- Select Position --</option>
                                    <?php foreach ($jobs_list as $job): ?><option value="<?= $job['job_id'] ?>"><?= htmlspecialchars($job['position']) ?></option><?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6"><label class="form-label fw-semibold">Status</label><select name="status" class="form-select" required><option value="Applied">Applied</option><option value="Interview">Interview</option><option value="Hired">Hired</option><option value="Rejected">Rejected</option></select></div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light border-top-0">
                        <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="add_applicant" class="btn btn-primary px-4">Save Applicant</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Applicant Modal -->
    <div class="modal fade" id="editApplicantModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-light">
                    <h5 class="modal-title fw-bold"><i class="bi bi-pencil-square me-2 text-warning"></i>Edit Applicant</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST">
                    <input type="hidden" name="applicant_id" id="edit_applicant_id">
                    <div class="modal-body p-4">
                        <div class="row g-3">
                            <div class="col-md-6"><label class="form-label fw-semibold">Full Name</label><input type="text" name="full_name" id="edit_full_name" class="form-control" required></div>
                            <div class="col-md-6"><label class="form-label fw-semibold">Email</label><input type="email" name="email" id="edit_email" class="form-control" required></div>
                            <div class="col-md-6"><label class="form-label fw-semibold">Phone Number</label><input type="text" name="phone_number" id="edit_phone_number" class="form-control" required></div>
                            <div class="col-md-6"><label class="form-label fw-semibold">Education</label><input type="text" name="education" id="edit_education" class="form-control" required></div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Position Applied</label>
                                <select name="job_id" id="edit_job_id" class="form-select">
                                    <option value="">-- Select Position --</option>
                                    <?php foreach ($jobs_list as $job): ?><option value="<?= $job['job_id'] ?>"><?= htmlspecialchars($job['position']) ?></option><?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6"><label class="form-label fw-semibold">Status</label><select name="status" id="edit_applicant_status" class="form-select" required><option value="Applied">Applied</option><option value="Interview">Interview</option><option value="Hired">Hired</option><option value="Rejected">Rejected</option></select></div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light border-top-0">
                        <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="edit_applicant" class="btn btn-primary px-4">Update Applicant</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
    function editApplicant(applicant) {
        document.getElementById('edit_applicant_id').value = applicant.applicant_id;
        document.getElementById('edit_full_name').value = applicant.full_name;
        document.getElementById('edit_email').value = applicant.email;
        document.getElementById('edit_phone_number').value = applicant.phone_number;
        document.getElementById('edit_education').value = applicant.education;
        document.getElementById('edit_job_id').value = applicant.job_id || '';
        document.getElementById('edit_applicant_status').value = applicant.status;
    }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>