<?php
// =============================================
// JOB MANAGEMENT - jobs.php
// =============================================

require_once 'config/database.php';

$page_title = 'Job Management - Admin Recruitment System';
$current_page = 'jobs';

// Handle CRUD Operations
$message = '';
$message_type = '';

// ADD JOB
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

// EDIT JOB
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

// DELETE JOB
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

// Get all jobs
$jobs = $conn->query("SELECT * FROM jobs ORDER BY created_at DESC");

include 'includes/header.php';
?>

<!-- Page Header -->
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1>Job Management</h1>
        <p>Kelola data lowongan pekerjaan</p>
    </div>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addJobModal">
        <i class="bi bi-plus-lg me-2"></i>Add Job
    </button>
</div>

<!-- Alert Message -->
<?php if ($message): ?>
<div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
    <?php echo $message; ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<!-- Jobs Table -->
<div class="content-card">
    <div class="card-header">
        <h5><i class="bi bi-list-ul me-2"></i>Daftar Lowongan Pekerjaan</h5>
    </div>
    <div class="table-responsive">
        <table id="dataTable" class="table table-hover">
            <thead>
                <tr>
                    <th>Job ID</th>
                    <th>Position</th>
                    <th>Department</th>
                    <th>Location</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $jobs->fetch_assoc()): ?>
                <tr>
                    <td><strong>#<?php echo $row['job_id']; ?></strong></td>
                    <td><?php echo htmlspecialchars($row['position']); ?></td>
                    <td><?php echo htmlspecialchars($row['department']); ?></td>
                    <td><?php echo htmlspecialchars($row['location']); ?></td>
                    <td>
                        <span class="badge badge-<?php echo strtolower($row['status']); ?>">
                            <?php echo $row['status']; ?>
                        </span>
                    </td>
                    <td class="action-buttons">
                        <button class="btn btn-sm btn-warning btn-action" 
                                data-bs-toggle="modal" 
                                data-bs-target="#editJobModal"
                                onclick="editJob(<?php echo htmlspecialchars(json_encode($row)); ?>)">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <a href="jobs.php?delete=<?php echo $row['job_id']; ?>" 
                           class="btn btn-sm btn-danger btn-action"
                           onclick="return confirm('Apakah Anda yakin ingin menghapus job ini?')">
                            <i class="bi bi-trash"></i>
                        </a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add Job Modal -->
<div class="modal fade" id="addJobModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-plus-circle me-2"></i>Add New Job</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Position</label>
                        <input type="text" name="position" class="form-control" required placeholder="e.g. Software Engineer">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Department</label>
                        <input type="text" name="department" class="form-control" required placeholder="e.g. Engineering">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Location</label>
                        <input type="text" name="location" class="form-control" required placeholder="e.g. Jakarta">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select" required>
                            <option value="Open">Open</option>
                            <option value="Closed">Closed</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="add_job" class="btn btn-primary">
                        <i class="bi bi-check-lg me-2"></i>Save Job
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Job Modal -->
<div class="modal fade" id="editJobModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-pencil-square me-2"></i>Edit Job</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="job_id" id="edit_job_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Position</label>
                        <input type="text" name="position" id="edit_position" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Department</label>
                        <input type="text" name="department" id="edit_department" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Location</label>
                        <input type="text" name="location" id="edit_location" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" id="edit_status" class="form-select" required>
                            <option value="Open">Open</option>
                            <option value="Closed">Closed</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="edit_job" class="btn btn-primary">
                        <i class="bi bi-check-lg me-2"></i>Update Job
                    </button>
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

<?php include 'includes/footer.php'; ?>
