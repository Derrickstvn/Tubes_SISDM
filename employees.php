<?php
require_once 'config/database.php';
require_once 'db_init.php';

// Initialize tables if they don't exist
check_and_init_db($conn);

$page_title = 'Employee Management Admin Recruitment System';
$current_page = 'employees';
$message = '';
$message_type = '';

// Handle Add Employee Manual
if (isset($_POST['add_employee'])) {
    $full_name = $conn->real_escape_string($_POST['full_name']);
    $email = $conn->real_escape_string($_POST['email']);
    $phone_number = $conn->real_escape_string($_POST['phone_number']);
    $position = $conn->real_escape_string($_POST['position']);
    $department = $conn->real_escape_string($_POST['department']);
    $basic_salary = (double)$_POST['basic_salary'];
    $join_date = $conn->real_escape_string($_POST['join_date']);
    $status = $conn->real_escape_string($_POST['status']);

    $sql = "INSERT INTO employees (full_name, email, phone_number, position, department, basic_salary, join_date, status) 
            VALUES ('$full_name', '$email', '$phone_number', '$position', '$department', $basic_salary, '$join_date', '$status')";
    if ($conn->query($sql)) {
        $message = 'Karyawan baru berhasil ditambahkan!';
        $message_type = 'success';
    } else {
        $message = 'Gagal menambahkan karyawan: ' . $conn->error;
        $message_type = 'danger';
    }
}

// Handle Edit Employee
if (isset($_POST['edit_employee'])) {
    $employee_id = (int)$_POST['employee_id'];
    $full_name = $conn->real_escape_string($_POST['full_name']);
    $email = $conn->real_escape_string($_POST['email']);
    $phone_number = $conn->real_escape_string($_POST['phone_number']);
    $position = $conn->real_escape_string($_POST['position']);
    $department = $conn->real_escape_string($_POST['department']);
    $basic_salary = (double)$_POST['basic_salary'];
    $join_date = $conn->real_escape_string($_POST['join_date']);
    $status = $conn->real_escape_string($_POST['status']);

    $sql = "UPDATE employees SET 
                full_name='$full_name', 
                email='$email', 
                phone_number='$phone_number', 
                position='$position', 
                department='$department', 
                basic_salary=$basic_salary, 
                join_date='$join_date', 
                status='$status' 
            WHERE employee_id=$employee_id";
            
    if ($conn->query($sql)) {
        $message = 'Data karyawan berhasil diperbarui!';
        $message_type = 'success';
    } else {
        $message = 'Gagal memperbarui data karyawan: ' . $conn->error;
        $message_type = 'danger';
    }
}

// Handle Auto-Import Hired Applicants
if (isset($_POST['import_hired'])) {
    $import_query = "SELECT a.*, j.position, j.department 
                     FROM applicants a 
                     LEFT JOIN jobs j ON a.job_id = j.job_id 
                     WHERE a.status = 'Hired' 
                     AND a.applicant_id NOT IN (SELECT applicant_id FROM employees WHERE applicant_id IS NOT NULL)";
    
    $to_import = $conn->query($import_query);
    $imported_count = 0;
    
    if ($to_import && $to_import->num_rows > 0) {
        while ($applicant = $to_import->fetch_assoc()) {
            $app_id = $applicant['applicant_id'];
            $name = $conn->real_escape_string($applicant['full_name']);
            $email = $conn->real_escape_string($applicant['email']);
            $phone = $conn->real_escape_string($applicant['phone_number']);
            $pos = $conn->real_escape_string($applicant['position'] ?? 'Staff');
            $dept = $conn->real_escape_string($applicant['department'] ?? 'Operations');
            $default_salary = 5000000.00; // default Rp 5.000.000
            $join_date = date('Y-m-d');
            
            $sql = "INSERT INTO employees (applicant_id, full_name, email, phone_number, position, department, basic_salary, join_date, status) 
                    VALUES ($app_id, '$name', '$email', '$phone', '$pos', '$dept', $default_salary, '$join_date', 'Active')";
            if ($conn->query($sql)) {
                $imported_count++;
            }
        }
        $message = "$imported_count pelamar berhasil diimpor sebagai karyawan!";
        $message_type = 'success';
    } else {
        $message = 'Tidak ada pelamar baru dengan status "Hired" untuk diimpor.';
        $message_type = 'info';
    }
}

// Handle Delete Karyawan
if (isset($_GET['delete'])) {
    $employee_id = (int)$_GET['delete'];
    $sql = "DELETE FROM employees WHERE employee_id=$employee_id";
    if ($conn->query($sql)) {
        $message = 'Karyawan berhasil dihapus!';
        $message_type = 'success';
    } else {
        $message = 'Gagal menghapus karyawan: ' . $conn->error;
        $message_type = 'danger';
    }
}

// Fetch Karyawan
$employees_query = $conn->query("SELECT * FROM employees ORDER BY status ASC, full_name ASC");
$employees_list = [];
if ($employees_query) {
    while ($row = $employees_query->fetch_assoc()) {
        $employees_list[] = $row;
    }
}

// Check for unimported Hired applicants
$unimported_query = "SELECT COUNT(*) as count 
                     FROM applicants 
                     WHERE status = 'Hired' 
                     AND applicant_id NOT IN (SELECT applicant_id FROM employees WHERE applicant_id IS NOT NULL)";
$unimported_result = $conn->query($unimported_query);
$unimported_count = $unimported_result ? $unimported_result->fetch_assoc()['count'] : 0;
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
        .badge-status { padding: 6px 12px; border-radius: 50px; font-size: 12px; font-weight: 600; display: inline-block; text-transform: uppercase; }
        .badge-active { background-color: #d1e7dd; color: #0f5132; border: 1px solid #badbcc; }
        .badge-resigned { background-color: #f8d7da; color: #842029; border: 1px solid #f5c2c7; }
        .table-responsive { border-radius: 8px; }
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
                <h1 class="h3 fw-bold text-dark mb-1">Employee Management</h1>
                <p class="text-muted small mb-0">Kelola informasi profil dan gaji pokok karyawan perusahaan.</p>
            </div>
            <div class="d-flex gap-2">
                <?php if ($unimported_count > 0): ?>
                    <form method="POST" class="d-inline">
                        <button type="submit" name="import_hired" class="btn btn-outline-success px-3 py-2 shadow-sm fw-semibold">
                            <i class="bi bi-person-plus-fill me-2"></i>Import Hired (<?= $unimported_count ?>)
                        </button>
                    </form>
                <?php endif; ?>
                <button class="btn btn-primary px-4 py-2 shadow-sm fw-semibold" data-bs-toggle="modal" data-bs-target="#addEmployeeModal">
                    <i class="bi bi-plus-lg me-2"></i>Add Employee
                </button>
            </div>
        </div>

        <!-- Notification -->
        <?php if ($message): ?>
            <div class="alert alert-<?= $message_type ?> alert-dismissible fade show shadow-sm" role="alert">
                <?= $message ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Employee List Card -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="card-title mb-0 fw-bold text-secondary">
                    <i class="bi bi-people-fill me-2 text-primary"></i>Daftar Karyawan
                </h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="px-4 py-3">ID Karyawan</th>
                                <th class="py-3">Nama Lengkap</th>
                                <th class="py-3">Posisi & Dep.</th>
                                <th class="py-3">Kontak</th>
                                <th class="py-3">Tanggal Bergabung</th>
                                <th class="py-3 text-end">Gaji Pokok</th>
                                <th class="py-3 text-center">Status</th>
                                <th class="px-4 py-3 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($employees_list)): ?>
                                <tr>
                                    <td colspan="8" class="text-center py-5 text-muted">Belum ada data karyawan saat ini.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($employees_list as $row): ?>
                                <tr>
                                    <td class="px-4"><strong>#EMP-<?= str_pad($row['employee_id'], 4, '0', STR_PAD_LEFT) ?></strong></td>
                                    <td class="fw-semibold text-dark"><?= htmlspecialchars($row['full_name']) ?></td>
                                    <td>
                                        <div class="fw-medium text-dark"><?= htmlspecialchars($row['position']) ?></div>
                                        <small class="text-muted" style="font-size: 11px;"><?= htmlspecialchars($row['department']) ?></small>
                                    </td>
                                    <td>
                                        <div><i class="bi bi-envelope-fill text-muted me-1 small"></i> <?= htmlspecialchars($row['email']) ?></div>
                                        <small class="text-muted"><i class="bi bi-telephone-fill text-muted me-1 small"></i> <?= htmlspecialchars($row['phone_number']) ?></small>
                                    </td>
                                    <td><?= date('d M Y', strtotime($row['join_date'])) ?></td>
                                    <td class="text-end fw-semibold text-primary">Rp <?= number_format($row['basic_salary'], 0, ',', '.') ?></td>
                                    <td class="text-center">
                                        <span class="badge-status badge-<?= strtolower($row['status']) ?>">
                                            <?= ($row['status'] == 'Active') ? 'Aktif' : 'Resign' ?>
                                        </span>
                                    </td>
                                    <td class="px-4 text-center">
                                        <div class="d-flex gap-2 justify-content-center">
                                            <button class="btn btn-sm btn-outline-warning" data-bs-toggle="modal" data-bs-target="#editEmployeeModal" onclick="editEmployee(<?= htmlspecialchars(json_encode($row)) ?>)">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <a href="employees.php?delete=<?= $row['employee_id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus karyawan ini? Data absensi dan penggajian terkait akan ikut terhapus.')">
                                                <i class="bi bi-trash"></i>
                                            </a>
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

    <!-- Add Employee Modal -->
    <div class="modal fade" id="addEmployeeModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-light">
                    <h5 class="modal-title fw-bold"><i class="bi bi-person-plus-fill me-2 text-primary"></i>Tambah Karyawan Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST">
                    <div class="modal-body p-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Nama Lengkap</label>
                                <input type="text" name="full_name" class="form-control" required placeholder="Nama lengkap karyawan">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Email</label>
                                <input type="email" name="email" class="form-control" required placeholder="email@perusahaan.com">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">No. Telepon</label>
                                <input type="text" name="phone_number" class="form-control" required placeholder="08xxxxxxxxxx">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Tanggal Bergabung</label>
                                <input type="date" name="join_date" class="form-control" required value="<?= date('Y-m-d') ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Posisi/Jabatan</label>
                                <input type="text" name="position" class="form-control" required placeholder="e.g. Software Engineer">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Departemen</label>
                                <select name="department" class="form-select" required>
                                    <option value="Engineering">Engineering</option>
                                    <option value="Product">Product</option>
                                    <option value="Design">Design</option>
                                    <option value="Data">Data</option>
                                    <option value="Human Resources">Human Resources</option>
                                    <option value="Marketing">Marketing</option>
                                    <option value="Finance">Finance</option>
                                    <option value="Operations">Operations</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Gaji Pokok (Rp)</label>
                                <input type="number" name="basic_salary" class="form-control" required min="0" placeholder="e.g. 5000000">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Status Karyawan</label>
                                <select name="status" class="form-select" required>
                                    <option value="Active">Active (Aktif)</option>
                                    <option value="Resigned">Resigned (Resign)</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light border-top-0">
                        <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" name="add_employee" class="btn btn-primary px-4">Simpan Karyawan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Employee Modal -->
    <div class="modal fade" id="editEmployeeModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-light">
                    <h5 class="modal-title fw-bold"><i class="bi bi-pencil-square me-2 text-warning"></i>Edit Data Karyawan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST">
                    <input type="hidden" name="employee_id" id="edit_employee_id">
                    <div class="modal-body p-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Nama Lengkap</label>
                                <input type="text" name="full_name" id="edit_full_name" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Email</label>
                                <input type="email" name="email" id="edit_email" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">No. Telepon</label>
                                <input type="text" name="phone_number" id="edit_phone_number" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Tanggal Bergabung</label>
                                <input type="date" name="join_date" id="edit_join_date" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Posisi/Jabatan</label>
                                <input type="text" name="position" id="edit_position" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Departemen</label>
                                <select name="department" id="edit_department" class="form-select" required>
                                    <option value="Engineering">Engineering</option>
                                    <option value="Product">Product</option>
                                    <option value="Design">Design</option>
                                    <option value="Data">Data</option>
                                    <option value="Human Resources">Human Resources</option>
                                    <option value="Marketing">Marketing</option>
                                    <option value="Finance">Finance</option>
                                    <option value="Operations">Operations</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Gaji Pokok (Rp)</label>
                                <input type="number" name="basic_salary" id="edit_basic_salary" class="form-control" required min="0">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Status Karyawan</label>
                                <select name="status" id="edit_status" class="form-select" required>
                                    <option value="Active">Active (Aktif)</option>
                                    <option value="Resigned">Resigned (Resign)</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light border-top-0">
                        <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" name="edit_employee" class="btn btn-primary px-4">Update Karyawan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script>
    function editEmployee(employee) {
        document.getElementById('edit_employee_id').value = employee.employee_id;
        document.getElementById('edit_full_name').value = employee.full_name;
        document.getElementById('edit_email').value = employee.email;
        document.getElementById('edit_phone_number').value = employee.phone_number;
        document.getElementById('edit_join_date').value = employee.join_date;
        document.getElementById('edit_position').value = employee.position;
        document.getElementById('edit_department').value = employee.department;
        document.getElementById('edit_basic_salary').value = parseInt(employee.basic_salary);
        document.getElementById('edit_status').value = employee.status;
    }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
