<?php
require_once 'config/database.php';
require_once 'db_init.php';

// Initialize tables if they don't exist
check_and_init_db($conn);

$page_title = 'Attendance Management Admin Recruitment System';
$current_page = 'attendance';
$message = '';
$message_type = '';

// Get selected date (defaults to today)
$selected_date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');

// Fetch Active Employees
$active_employees_query = $conn->query("SELECT * FROM employees WHERE status = 'Active' ORDER BY full_name ASC");
$active_employees = [];
if ($active_employees_query) {
    while ($row = $active_employees_query->fetch_assoc()) {
        $active_employees[] = $row;
    }
}

// Handle Bulk Save/Update Attendance
if (isset($_POST['save_attendance'])) {
    $date = $conn->real_escape_string($_POST['attendance_date']);
    $statuses = $_POST['status'] ?? [];
    $check_ins = $_POST['check_in'] ?? [];
    $check_outs = $_POST['check_out'] ?? [];
    $notes_arr = $_POST['notes'] ?? [];
    
    $success_count = 0;
    
    foreach ($active_employees as $emp) {
        $emp_id = $emp['employee_id'];
        $status = $conn->real_escape_string($statuses[$emp_id] ?? 'Present');
        
        // Setup default times for Present, otherwise NULL
        if ($status == 'Present') {
            $check_in = !empty($check_ins[$emp_id]) ? "'" . $conn->real_escape_string($check_ins[$emp_id]) . "'" : "'08:00:00'";
            $check_out = !empty($check_outs[$emp_id]) ? "'" . $conn->real_escape_string($check_outs[$emp_id]) . "'" : "'17:00:00'";
        } else {
            $check_in = "NULL";
            $check_out = "NULL";
        }
        
        $notes = $conn->real_escape_string($notes_arr[$emp_id] ?? '');
        
        $sql = "INSERT INTO attendance (employee_id, date, check_in, check_out, status, notes) 
                VALUES ($emp_id, '$date', $check_in, $check_out, '$status', '$notes') 
                ON DUPLICATE KEY UPDATE 
                    status = '$status', 
                    check_in = $check_in, 
                    check_out = $check_out, 
                    notes = '$notes'";
        
        if ($conn->query($sql)) {
            $success_count++;
        }
    }
    
    $message = "Absensi untuk tanggal " . date('d-m-Y', strtotime($date)) . " berhasil disimpan!";
    $message_type = 'success';
    $selected_date = $date; // update display date
}

// Handle Auto Fill Present All
if (isset($_POST['fill_present'])) {
    $date = $conn->real_escape_string($_POST['attendance_date']);
    $success_count = 0;
    
    foreach ($active_employees as $emp) {
        $emp_id = $emp['employee_id'];
        
        $sql = "INSERT INTO attendance (employee_id, date, check_in, check_out, status, notes) 
                VALUES ($emp_id, '$date', '08:00:00', '17:00:00', 'Present', '') 
                ON DUPLICATE KEY UPDATE 
                    status = 'Present', 
                    check_in = '08:00:00', 
                    check_out = '17:00:00', 
                    notes = ''";
        
        if ($conn->query($sql)) {
            $success_count++;
        }
    }
    
    $message = "Berhasil menandai semua karyawan sebagai Hadir untuk tanggal " . date('d-m-Y', strtotime($date)) . "!";
    $message_type = 'success';
    $selected_date = $date;
}

// Fetch existing attendance for the selected date
$attendance_query = $conn->query("SELECT * FROM attendance WHERE date = '$selected_date'");
$existing_attendance = [];
if ($attendance_query) {
    while ($row = $attendance_query->fetch_assoc()) {
        $existing_attendance[$row['employee_id']] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?></title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; color: #333333; font-family: sans-serif; min-height: 100vh; }
        .table-responsive { border-radius: 8px; }
        .status-radio { display: none; }
        .status-btn { 
            padding: 4px 12px; 
            border-radius: 20px; 
            font-size: 12px; 
            font-weight: 600; 
            cursor: pointer;
            border: 1px solid #dee2e6;
            background-color: #ffffff;
            color: #6c757d;
            transition: all 0.2s ease;
        }
        .radio-present:checked + .btn-present { background-color: #d1e7dd; color: #0f5132; border-color: #badbcc; }
        .radio-absent:checked + .btn-absent { background-color: #f8d7da; color: #842029; border-color: #f5c2c7; }
        .radio-sick:checked + .btn-sick { background-color: #fff3cd; color: #664d03; border-color: #ffecb5; }
        .radio-leave:checked + .btn-leave { background-color: #cff4fc; color: #055160; border-color: #b6effb; }
        .radio-permit:checked + .btn-permit { background-color: #e2e3e5; color: #41464b; border-color: #d3d6d8; }
    </style>
</head>
<body>

    <!-- Navbar Bootstrap -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4 py-3">
        <div class="container">
            <a class="navbar-brand fw-bold text-primary" href="index.php">Recruitment<span class="text-white">System</span></a>
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
        
        <!-- Header -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
            <div>
                <h1 class="h3 fw-bold text-dark mb-1">Attendance Management</h1>
                <p class="text-muted small mb-0">Kelola dan rekam kehadiran harian karyawan secara praktis.</p>
            </div>
            <div class="d-flex align-items-center gap-2">
                <form method="GET" class="d-flex align-items-center gap-2">
                    <label class="fw-semibold text-secondary small flex-shrink-0">Pilih Tanggal:</label>
                    <input type="date" name="date" class="form-control form-control-sm" value="<?= $selected_date ?>" onchange="this.form.submit()">
                </form>
            </div>
        </div>

        <!-- Notification -->
        <?php if ($message): ?>
            <div class="alert alert-<?= $message_type ?> alert-dismissible fade show shadow-sm" role="alert">
                <?= $message ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Attendance Controls and Grid -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h5 class="card-title mb-0 fw-bold text-secondary">
                    <i class="bi bi-calendar-check me-2 text-primary"></i>Absensi: <?= date('d F Y', strtotime($selected_date)) ?>
                </h5>
                <form method="POST" class="d-inline">
                    <input type="hidden" name="attendance_date" value="<?= $selected_date ?>">
                    <button type="submit" name="fill_present" class="btn btn-sm btn-outline-primary fw-semibold" onclick="return confirm('Tindakan ini akan mengatur semua karyawan aktif menjadi Hadir (08:00 - 17:00). Lanjutkan?')">
                        <i class="bi bi-check-all me-1"></i>Set Semua Hadir
                    </button>
                </form>
            </div>
            <div class="card-body p-0">
                <form method="POST">
                    <input type="hidden" name="attendance_date" value="<?= $selected_date ?>">
                    
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="px-4 py-3">Nama Karyawan</th>
                                    <th class="py-3">Posisi</th>
                                    <th class="py-3 text-center" style="width: 380px;">Status Kehadiran</th>
                                    <th class="py-3" style="width: 120px;">Jam Masuk</th>
                                    <th class="py-3" style="width: 120px;">Jam Keluar</th>
                                    <th class="px-4 py-3">Keterangan / Catatan</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($active_employees)): ?>
                                    <tr>
                                        <td colspan="6" class="text-center py-5 text-muted">Tidak ada karyawan aktif untuk dicatat absensinya.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($active_employees as $emp): 
                                        $emp_id = $emp['employee_id'];
                                        $record = $existing_attendance[$emp_id] ?? null;
                                        $current_status = $record ? $record['status'] : 'Present';
                                        $c_in = $record ? $record['check_in'] : '08:00';
                                        $c_out = $record ? $record['check_out'] : '17:00';
                                        $note = $record ? $record['notes'] : '';
                                    ?>
                                    <tr>
                                        <td class="px-4 fw-semibold text-dark"><?= htmlspecialchars($emp['full_name']) ?></td>
                                        <td>
                                            <span class="badge bg-secondary bg-opacity-10 text-secondary border px-2 py-1"><?= htmlspecialchars($emp['position']) ?></span>
                                        </td>
                                        <td class="text-center">
                                            <div class="d-flex justify-content-center gap-1">
                                                <!-- Present -->
                                                <input type="radio" name="status[<?= $emp_id ?>]" value="Present" id="pres_<?= $emp_id ?>" class="status-radio radio-present" <?= ($current_status == 'Present') ? 'checked' : '' ?> onchange="toggleTimeInputs(<?= $emp_id ?>, true)">
                                                <label for="pres_<?= $emp_id ?>" class="status-btn btn-present">Hadir</label>
                                                
                                                <!-- Sick -->
                                                <input type="radio" name="status[<?= $emp_id ?>]" value="Sick" id="sick_<?= $emp_id ?>" class="status-radio radio-sick" <?= ($current_status == 'Sick') ? 'checked' : '' ?> onchange="toggleTimeInputs(<?= $emp_id ?>, false)">
                                                <label for="sick_<?= $emp_id ?>" class="status-btn btn-sick">Sakit</label>
                                                
                                                <!-- Permit -->
                                                <input type="radio" name="status[<?= $emp_id ?>]" value="Permit" id="perm_<?= $emp_id ?>" class="status-radio radio-permit" <?= ($current_status == 'Permit') ? 'checked' : '' ?> onchange="toggleTimeInputs(<?= $emp_id ?>, false)">
                                                <label for="perm_<?= $emp_id ?>" class="status-btn btn-permit">Izin</label>
                                                
                                                <!-- Leave -->
                                                <input type="radio" name="status[<?= $emp_id ?>]" value="Leave" id="leav_<?= $emp_id ?>" class="status-radio radio-leave" <?= ($current_status == 'Leave') ? 'checked' : '' ?> onchange="toggleTimeInputs(<?= $emp_id ?>, false)">
                                                <label for="leav_<?= $emp_id ?>" class="status-btn btn-leave">Cuti</label>
                                                
                                                <!-- Absent -->
                                                <input type="radio" name="status[<?= $emp_id ?>]" value="Absent" id="abse_<?= $emp_id ?>" class="status-radio radio-absent" <?= ($current_status == 'Absent') ? 'checked' : '' ?> onchange="toggleTimeInputs(<?= $emp_id ?>, false)">
                                                <label for="abse_<?= $emp_id ?>" class="status-btn btn-absent">Alfa</label>
                                            </div>
                                        </td>
                                        <td>
                                            <input type="time" name="check_in[<?= $emp_id ?>]" id="in_<?= $emp_id ?>" class="form-control form-control-sm time-field-<?= $emp_id ?>" value="<?= substr($c_in, 0, 5) ?>" <?= ($current_status != 'Present') ? 'disabled' : '' ?>>
                                        </td>
                                        <td>
                                            <input type="time" name="check_out[<?= $emp_id ?>]" id="out_<?= $emp_id ?>" class="form-control form-control-sm time-field-<?= $emp_id ?>" value="<?= substr($c_out, 0, 5) ?>" <?= ($current_status != 'Present') ? 'disabled' : '' ?>>
                                        </td>
                                        <td class="px-4">
                                            <input type="text" name="notes[<?= $emp_id ?>]" class="form-control form-control-sm" placeholder="Catatan opsional..." value="<?= htmlspecialchars($note) ?>">
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="card-footer bg-light py-3 text-end border-top-0">
                        <button type="submit" name="save_attendance" class="btn btn-primary px-5 fw-bold shadow-sm">
                            <i class="bi bi-save2 me-2"></i>Simpan Semua Absensi
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>

    <!-- Scripts -->
    <script>
    function toggleTimeInputs(empId, isPresent) {
        const fields = document.querySelectorAll('.time-field-' + empId);
        fields.forEach(field => {
            field.disabled = !isPresent;
            if (!isPresent) {
                field.value = '';
            } else {
                if (field.id.startsWith('in_')) {
                    field.value = '08:00';
                } else if (field.id.startsWith('out_')) {
                    field.value = '17:00';
                }
            }
        });
    }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
