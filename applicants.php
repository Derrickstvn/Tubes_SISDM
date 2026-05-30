<?php
// =============================================
// APPLICANT MANAGEMENT - applicants.php
// =============================================

require_once 'config/database.php';

$page_title = 'Applicant Management - Admin Recruitment System';
$current_page = 'applicants';

// Handle CRUD Operations
$message = '';
$message_type = '';

// ADD APPLICANT
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

// EDIT APPLICANT
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

// DELETE APPLICANT
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

// Get all applicants with job info
$applicants = $conn->query("
    SELECT a.*, j.position 
    FROM applicants a 
    LEFT JOIN jobs j ON a.job_id = j.job_id 
    ORDER BY a.created_at DESC
");

// Get all jobs for dropdown
$jobs = $conn->query("SELECT job_id, position FROM jobs WHERE status = 'Open' ORDER BY position");

include 'includes/header.php';
?>

<!-- Page Header -->
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1>Applicant Management</h1>
        <p>Kelola data pelamar pekerjaan</p>
    </div>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addApplicantModal">
        <i class="bi bi-plus-lg me-2"></i>Add Applicant
    </button>
</div>

<!-- Alert Message -->
<?php if ($message): ?>
<div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
    <?php echo $message; ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<!-- Applicants Table -->
<div class="content-card">
    <div class="card-header">
        <h5><i class="bi bi-list-ul me-2"></i>Daftar Pelamar</h5>
    </div>
    <div class="table-responsive">
        <table id="dataTable" class="table table-hover">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Full Name</th>
                    <th>Email</th>
                    <th>Phone Number</th>
                    <th>Education</th>
                    <th>Position Applied</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $applicants->fetch_assoc()): ?>
                <tr>
                    <td><strong>#<?php echo $row['applicant_id']; ?></strong></td>
                    <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                    <td><?php echo htmlspecialchars($row['phone_number']); ?></td>
                    <td><?php echo htmlspecialchars($row['education']); ?></td>
                    <td><?php echo htmlspecialchars($row['position'] ?? 'N/A'); ?></td>
                    <td>
                        <span class="badge badge-<?php echo strtolower($row['status']); ?>">
                            <?php echo $row['status']; ?>
                        </span>
                    </td>
                    <td class="action-buttons">
                        <button class="btn btn-sm btn-warning btn-action" 
                                data-bs-toggle="modal" 
                                data-bs-target="#editApplicantModal"
                                onclick="editApplicant(<?php echo htmlspecialchars(json_encode($row)); ?>)">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <a href="applicants.php?delete=<?php echo $row['applicant_id']; ?>" 
                           class="btn btn-sm btn-danger btn-action"
                           onclick="return confirm('Apakah Anda yakin ingin menghapus applicant ini?')">
                            <i class="bi bi-trash"></i>
                        </a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add Applicant Modal -->
<div class="modal fade" id="addApplicantModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-plus-circle me-2"></i>Add New Applicant</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Full Name</label>
                            <input type="text" name="full_name" class="form-control" required placeholder="Nama lengkap">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" required placeholder="email@example.com">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Phone Number</label>
                            <input type="text" name="phone_number" class="form-control" required placeholder="08xxxxxxxxxx">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Education</label>
                            <input type="text" name="education" class="form-control" required placeholder="e.g. S1 Teknik Informatika">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Position Applied</label>
                            <select name="job_id" class="form-select">
                                <option value="">-- Select Position --</option>
                                <?php 
                                $jobs->data_seek(0);
                                while ($job = $jobs->fetch_assoc()): 
                                ?>
                                <option value="<?php echo $job['job_id']; ?>"><?php echo htmlspecialchars($job['position']); ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select" required>
                                <option value="Applied">Applied</option>
                                <option value="Interview">Interview</option>
                                <option value="Hired">Hired</option>
                                <option value="Rejected">Rejected</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="add_applicant" class="btn btn-primary">
                        <i class="bi bi-check-lg me-2"></i>Save Applicant
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Applicant Modal -->
<div class="modal fade" id="editApplicantModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-pencil-square me-2"></i>Edit Applicant</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="applicant_id" id="edit_applicant_id">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Full Name</label>
                            <input type="text" name="full_name" id="edit_full_name" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" id="edit_email" class="form-control" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Phone Number</label>
                            <input type="text" name="phone_number" id="edit_phone_number" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Education</label>
                            <input type="text" name="education" id="edit_education" class="form-control" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Position Applied</label>
                            <select name="job_id" id="edit_job_id" class="form-select">
                                <option value="">-- Select Position --</option>
                                <?php 
                                $jobs->data_seek(0);
                                while ($job = $jobs->fetch_assoc()): 
                                ?>
                                <option value="<?php echo $job['job_id']; ?>"><?php echo htmlspecialchars($job['position']); ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" id="edit_applicant_status" class="form-select" required>
                                <option value="Applied">Applied</option>
                                <option value="Interview">Interview</option>
                                <option value="Hired">Hired</option>
                                <option value="Rejected">Rejected</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="edit_applicant" class="btn btn-primary">
                        <i class="bi bi-check-lg me-2"></i>Update Applicant
                    </button>
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

<?php include 'includes/footer.php'; ?>
