<?php
// =============================================
// INTERVIEW SCHEDULE - interviews.php
// =============================================

require_once 'config/database.php';

$page_title = 'Interview Schedule - Admin Recruitment System';
$current_page = 'interviews';

// Handle CRUD Operations
$message = '';
$message_type = '';

// ADD INTERVIEW
if (isset($_POST['add_interview'])) {
    $applicant_id = (int)$_POST['applicant_id'];
    $job_id = (int)$_POST['job_id'];
    $interview_date = $conn->real_escape_string($_POST['interview_date']);
    $status = $conn->real_escape_string($_POST['status']);
    $notes = $conn->real_escape_string($_POST['notes']);
    
    $sql = "INSERT INTO interviews (applicant_id, job_id, interview_date, status, notes) VALUES ($applicant_id, $job_id, '$interview_date', '$status', '$notes')";
    if ($conn->query($sql)) {
        $message = 'Interview berhasil ditambahkan!';
        $message_type = 'success';
    } else {
        $message = 'Gagal menambahkan interview: ' . $conn->error;
        $message_type = 'danger';
    }
}

// EDIT INTERVIEW
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

// DELETE INTERVIEW
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

// Get all interviews with applicant and job info
$interviews = $conn->query("
    SELECT i.*, a.full_name, j.position 
    FROM interviews i 
    JOIN applicants a ON i.applicant_id = a.applicant_id 
    JOIN jobs j ON i.job_id = j.job_id 
    ORDER BY i.interview_date DESC
");

// Get all applicants for dropdown
$applicants = $conn->query("SELECT applicant_id, full_name FROM applicants ORDER BY full_name");

// Get all jobs for dropdown
$jobs = $conn->query("SELECT job_id, position FROM jobs ORDER BY position");

include 'includes/header.php';
?>

<!-- Page Header -->
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1>Interview Schedule</h1>
        <p>Kelola jadwal interview kandidat</p>
    </div>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addInterviewModal">
        <i class="bi bi-plus-lg me-2"></i>Add Schedule
    </button>
</div>

<!-- Alert Message -->
<?php if ($message): ?>
<div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
    <?php echo $message; ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<!-- Interviews Table -->
<div class="content-card">
    <div class="card-header">
        <h5><i class="bi bi-list-ul me-2"></i>Daftar Jadwal Interview</h5>
    </div>
    <div class="table-responsive">
        <table id="dataTable" class="table table-hover">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Applicant Name</th>
                    <th>Position</th>
                    <th>Interview Date</th>
                    <th>Status</th>
                    <th>Notes</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $interviews->fetch_assoc()): ?>
                <tr>
                    <td><strong>#<?php echo $row['interview_id']; ?></strong></td>
                    <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['position']); ?></td>
                    <td>
                        <i class="bi bi-calendar3 me-1"></i>
                        <?php echo date('d M Y, H:i', strtotime($row['interview_date'])); ?>
                    </td>
                    <td>
                        <span class="badge badge-<?php echo strtolower($row['status']); ?>">
                            <?php echo $row['status']; ?>
                        </span>
                    </td>
                    <td>
                        <?php 
                        $notes = $row['notes'];
                        echo htmlspecialchars(strlen($notes) > 30 ? substr($notes, 0, 30) . '...' : $notes);
                        ?>
                    </td>
                    <td class="action-buttons">
                        <button class="btn btn-sm btn-warning btn-action" 
                                data-bs-toggle="modal" 
                                data-bs-target="#editInterviewModal"
                                onclick="editInterview(<?php echo htmlspecialchars(json_encode($row)); ?>)">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <a href="interviews.php?delete=<?php echo $row['interview_id']; ?>" 
                           class="btn btn-sm btn-danger btn-action"
                           onclick="return confirm('Apakah Anda yakin ingin menghapus jadwal interview ini?')">
                            <i class="bi bi-trash"></i>
                        </a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add Interview Modal -->
<div class="modal fade" id="addInterviewModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-plus-circle me-2"></i>Add New Interview Schedule</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Applicant</label>
                            <select name="applicant_id" class="form-select" required>
                                <option value="">-- Select Applicant --</option>
                                <?php 
                                $applicants->data_seek(0);
                                while ($applicant = $applicants->fetch_assoc()): 
                                ?>
                                <option value="<?php echo $applicant['applicant_id']; ?>"><?php echo htmlspecialchars($applicant['full_name']); ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Position</label>
                            <select name="job_id" class="form-select" required>
                                <option value="">-- Select Position --</option>
                                <?php 
                                $jobs->data_seek(0);
                                while ($job = $jobs->fetch_assoc()): 
                                ?>
                                <option value="<?php echo $job['job_id']; ?>"><?php echo htmlspecialchars($job['position']); ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Interview Date & Time</label>
                            <input type="datetime-local" name="interview_date" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select" required>
                                <option value="Scheduled">Scheduled</option>
                                <option value="Completed">Completed</option>
                                <option value="Cancelled">Cancelled</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control" rows="3" placeholder="Catatan tambahan..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="add_interview" class="btn btn-primary">
                        <i class="bi bi-check-lg me-2"></i>Save Schedule
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Interview Modal -->
<div class="modal fade" id="editInterviewModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-pencil-square me-2"></i>Edit Interview Schedule</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="interview_id" id="edit_interview_id">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Applicant</label>
                            <select name="applicant_id" id="edit_applicant_id" class="form-select" required>
                                <option value="">-- Select Applicant --</option>
                                <?php 
                                $applicants->data_seek(0);
                                while ($applicant = $applicants->fetch_assoc()): 
                                ?>
                                <option value="<?php echo $applicant['applicant_id']; ?>"><?php echo htmlspecialchars($applicant['full_name']); ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Position</label>
                            <select name="job_id" id="edit_job_id" class="form-select" required>
                                <option value="">-- Select Position --</option>
                                <?php 
                                $jobs->data_seek(0);
                                while ($job = $jobs->fetch_assoc()): 
                                ?>
                                <option value="<?php echo $job['job_id']; ?>"><?php echo htmlspecialchars($job['position']); ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Interview Date & Time</label>
                            <input type="datetime-local" name="interview_date" id="edit_interview_date" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" id="edit_interview_status" class="form-select" required>
                                <option value="Scheduled">Scheduled</option>
                                <option value="Completed">Completed</option>
                                <option value="Cancelled">Cancelled</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" id="edit_notes" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="edit_interview" class="btn btn-primary">
                        <i class="bi bi-check-lg me-2"></i>Update Schedule
                    </button>
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
    
    // Format datetime for input
    const date = new Date(interview.interview_date);
    const formattedDate = date.toISOString().slice(0, 16);
    document.getElementById('edit_interview_date').value = formattedDate;
    
    document.getElementById('edit_interview_status').value = interview.status;
    document.getElementById('edit_notes').value = interview.notes || '';
}
</script>

<?php include 'includes/footer.php'; ?>
