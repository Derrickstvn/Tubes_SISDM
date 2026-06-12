<?php
require_once 'config/database.php';
$page_title = 'Interview Schedule Admin Recruitment System';
$current_page = 'interviews';
$message = '';
$message_type = '';

if (isset($_POST['add_interview'])) {
    $applicant_id = (int)$_POST['applicant_id'];
    $job_id = (int)$_POST['job_id'];
    $interview_date = $conn->real_escape_string($_POST['interview_date']);
    $status = $conn->real_escape_string($_POST['status']);
    $notes = $conn->real_escape_string($_POST['notes']);
    
    $sql = "INSERT INTO interviews (applicant_id, job_id, interview_date, status, notes) VALUES ($applicant_id, $job_id, '$interview_date', '$status', '$notes')";
    if ($conn->query($sql)){
        $message = 'Interview berhasil ditambahkan!';
        $message_type = 'success';
    } else {
        $message = 'Gagal menambahkan interview: ' . $conn->error;
        $message_type = 'danger';
    }
}

if (isset($_POST['edit_interview'])) {
    $interview_id = (int)$_POST['interview_id'];
    $applicant_id = (int)$_POST['applicant_id'];
    $job_id = (int)$_POST['job_id'];
    $interview_date = $conn->real_escape_string($_POST['interview_date']);
    $status = $conn->real_escape_string($_POST['status']);
    $notes = $conn->real_escape_string($_POST['notes']);

    $sql = "UPDATE interviews SET applicant_id=$applicant_id, job_id=$job_id, interview_date='$interview_date', status='$status', notes='$notes' WHERE interview_id=$interview_id";
    if ($conn->query($sql)) {
        $message = 'Interview berhasil diupdate!';
        $message_type = 'success';
    } else {
        $message = 'Gagal mengupdate interview: ' . $conn->error;
        $message_type = 'danger';
    }
}

if (isset($_GET['delete'])) {
    $interview_id = (int)$_GET['delete'];
    $sql = "DELETE FROM interviews WHERE interview_id=$interview_id";
    if ($conn->query($sql)) {
        $message = 'Interview berhasil dihapus!';
        $message_type = 'success';
    } else {
        $message = 'Gagal menghapus interview: ' . $conn->error;
        $message_type = 'danger';
    }
}

$interviews = $conn->query("SELECT i.*, a.full_name, j.position FROM interviews i JOIN applicants a ON i.applicant_id = a.applicant_id JOIN jobs j ON i.job_id = j.job_id ORDER BY i.interview_id ASC");
$applicants = $conn->query("SELECT applicant_id, full_name FROM applicants ORDER BY full_name");
$jobs = $conn->query("SELECT job_id, position FROM jobs ORDER BY position");
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
        .badge-scheduled { background-color: #dbeafe; color: #1e40af; border: 1px solid #bfdbfe; }
        .badge-completed { background-color: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; }
        .badge-cancelled { background-color: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
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
                <h1 class="h3 fw-bold text-dark mb-1">Interview Schedule</h1>
                <p class="text-muted small mb-0">Kelola jadwal interview kandidat secara terpusat.</p>
            </div>
            <div>
                <button class="btn btn-primary px-4 py-2 shadow-sm" data-bs-toggle="modal" data-bs-target="#addInterviewModal"><i class="bi bi-plus-lg me-2"></i>Add Schedule</button>
            </div>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-<?= $message_type ?> alert-dismissible fade show shadow-sm" role="alert">
                <?= $message ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="card-title mb-0 fw-bold text-secondary"><i class="bi bi-list-ul me-2 text-primary"></i>Daftar Jadwal Interview</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="px-4 py-3">ID</th>
                                <th class="py-3">Applicant Name</th>
                                <th class="py-3">Position</th>
                                <th class="py-3">Interview Date</th>
                                <th class="py-3">Status</th>
                                <th class="py-3">Notes</th>
                                <th class="px-4 py-3 text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($interviews->num_rows == 0): ?>
                                <tr><td colspan="7" class="text-center py-5 text-muted">Belum ada jadwal interview saat ini.</td></tr>
                            <?php else: ?>
                                <?php while ($row = $interviews->fetch_assoc()): ?>
                                <tr>
                                    <td class="px-4"><strong>#<?= $row['interview_id']; ?></strong></td>
                                    <td class="fw-semibold"><?= htmlspecialchars($row['full_name']); ?></td>
                                    <td><span class="badge bg-secondary bg-opacity-10 text-secondary border px-2 py-1"><?= htmlspecialchars($row['position']); ?></span></td>
                                    <td><i class="bi bi-calendar3 text-primary me-2"></i><?= date('d M Y, H:i', strtotime($row['interview_date'])); ?></td>
                                    <td><span class="badge-status badge-<?= strtolower($row['status']); ?>"><?= $row['status']; ?></span></td>
                                    <td><span class="text-muted small"><?= htmlspecialchars(strlen($row['notes']) > 30 ? substr($row['notes'], 0, 30).'...' : $row['notes']); ?></span></td>
                                    <td class="px-4 text-center">
                                        <div class="d-flex gap-2 justify-content-center">
                                            <button class="btn btn-sm btn-outline-warning" data-bs-toggle="modal" data-bs-target="#editInterviewModal" onclick="editInterview(<?= htmlspecialchars(json_encode($row)); ?>)"><i class="bi bi-pencil"></i></button>
                                            <a href="interviews.php?delete=<?= $row['interview_id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus jadwal interview ini?')"><i class="bi bi-trash"></i></a>
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

    <!-- Add Interview Modal -->
    <div class="modal fade" id="addInterviewModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-light">
                    <h5 class="modal-title fw-bold"><i class="bi bi-plus-circle me-2 text-primary"></i>Add New Interview Schedule</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST">
                    <div class="modal-body p-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Applicant</label>
                                <select name="applicant_id" class="form-select" required>
                                    <option value="">-- Select Applicant --</option>
                                    <?php $applicants->data_seek(0); while ($applicant = $applicants->fetch_assoc()): ?><option value="<?= $applicant['applicant_id']; ?>"><?= htmlspecialchars($applicant['full_name']); ?></option><?php endwhile; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Position</label>
                                <select name="job_id" class="form-select" required>
                                    <option value="">-- Select Position --</option>
                                    <?php $jobs->data_seek(0); while ($job = $jobs->fetch_assoc()): ?><option value="<?= $job['job_id']; ?>"><?= htmlspecialchars($job['position']); ?></option><?php endwhile; ?>
                                </select>
                            </div>
                            <div class="col-md-6"><label class="form-label fw-semibold">Interview Date & Time</label><input type="datetime-local" name="interview_date" class="form-control" required></div>
                            <div class="col-md-6"><label class="form-label fw-semibold">Status</label><select name="status" class="form-select" required><option value="Scheduled">Scheduled</option><option value="Completed">Completed</option><option value="Cancelled">Cancelled</option></select></div>
                            <div class="col-12"><label class="form-label fw-semibold">Notes</label><textarea name="notes" class="form-control" rows="3" placeholder="Catatan tambahan..."></textarea></div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light border-top-0">
                        <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="add_interview" class="btn btn-primary px-4">Save Schedule</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Interview Modal -->
    <div class="modal fade" id="editInterviewModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-light">
                    <h5 class="modal-title fw-bold"><i class="bi bi-pencil-square me-2 text-warning"></i>Edit Interview Schedule</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST">
                    <input type="hidden" name="interview_id" id="edit_interview_id">
                    <div class="modal-body p-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Applicant</label>
                                <select name="applicant_id" id="edit_applicant_id" class="form-select" required>
                                    <?php $applicants->data_seek(0); while ($applicant = $applicants->fetch_assoc()): ?><option value="<?= $applicant['applicant_id']; ?>"><?= htmlspecialchars($applicant['full_name']); ?></option><?php endwhile; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Position</label>
                                <select name="job_id" id="edit_job_id" class="form-select" required>
                                    <?php $jobs->data_seek(0); while ($job = $jobs->fetch_assoc()): ?><option value="<?= $job['job_id']; ?>"><?= htmlspecialchars($job['position']); ?></option><?php endwhile; ?>
                                </select>
                            </div>
                            <div class="col-md-6"><label class="form-label fw-semibold">Interview Date & Time</label><input type="datetime-local" name="interview_date" id="edit_interview_date" class="form-control" required></div>
                            <div class="col-md-6"><label class="form-label fw-semibold">Status</label><select name="status" id="edit_interview_status" class="form-select" required><option value="Scheduled">Scheduled</option><option value="Completed">Completed</option><option value="Cancelled">Cancelled</option></select></div>
                            <div class="col-12"><label class="form-label fw-semibold">Notes</label><textarea name="notes" id="edit_notes" class="form-control" rows="3"></textarea></div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light border-top-0">
                        <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="edit_interview" class="btn btn-primary px-4">Update Schedule</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
    function editInterview(interview) {
        document.getElementById('edit_interview_id').value = interview.interview_id;
        document.getElementById('edit_applicant_id').value = interview.applicant_id;
        document.getElementById('edit_job_id').value = interview.job_id;
        if(interview.interview_date) {
            const date = new Date(interview.interview_date);
            const offset = date.getTimezoneOffset() * 60000;
            const localISOTime = (new Date(date.getTime() - offset)).toISOString().slice(0, 16);
            document.getElementById('edit_interview_date').value = localISOTime;
        }
        document.getElementById('edit_interview_status').value = interview.status;
        document.getElementById('edit_notes').value = interview.notes || '';
    }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>