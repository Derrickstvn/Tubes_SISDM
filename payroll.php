<?php
require_once 'config/database.php';
require_once 'db_init.php';

// Initialize tables if they don't exist
check_and_init_db($conn);

$page_title = 'Payroll Management Admin Recruitment System';
$current_page = 'payroll';
$message = '';
$message_type = '';

// Get selected period (defaults to current month and year)
$selected_month = isset($_GET['month']) ? (int)$_GET['month'] : (int)date('m');
$selected_year = isset($_GET['year']) ? (int)$_GET['year'] : (int)date('Y');

// Month names list
$months_list = [
    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 
    5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus', 
    9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
];

// Deduction rate per Absent (Alfa) day
$absent_deduction_rate = 150000.00; 

// Handle Auto-Generate Payroll for all active employees
if (isset($_POST['generate_payroll'])) {
    // Fetch active employees
    $emp_query = $conn->query("SELECT * FROM employees WHERE status = 'Active'");
    $generated = 0;
    
    if ($emp_query && $emp_query->num_rows > 0) {
        while ($emp = $emp_query->fetch_assoc()) {
            $emp_id = $emp['employee_id'];
            $basic_salary = $emp['basic_salary'];
            
            // Count absence days for the selected month and year
            $absent_query = $conn->query("SELECT COUNT(*) as absent_count 
                                         FROM attendance 
                                         WHERE employee_id = $emp_id 
                                         AND MONTH(date) = $selected_month 
                                         AND YEAR(date) = $selected_year 
                                         AND status = 'Absent'");
            $absent_count = $absent_query ? $absent_query->fetch_assoc()['absent_count'] : 0;
            $auto_deduction = $absent_count * $absent_deduction_rate;
            
            // Check if payroll already exists for this period
            $exist_query = $conn->query("SELECT payroll_id, allowance, deductions, status FROM payroll 
                                         WHERE employee_id = $emp_id 
                                         AND month = $selected_month 
                                         AND year = $selected_year");
            
            if ($exist_query && $exist_query->num_rows > 0) {
                // Keep existing allowance and status, just update basic salary and deductions
                $existing = $exist_query->fetch_assoc();
                $allowance = $existing['allowance'];
                // Only update deduction if it is the auto calculated one or merge it
                $deductions = $auto_deduction; 
                $net_salary = $basic_salary + $allowance - $deductions;
                
                $sql = "UPDATE payroll SET 
                            basic_salary = $basic_salary, 
                            deductions = $deductions, 
                            net_salary = $net_salary 
                        WHERE payroll_id = " . $existing['payroll_id'];
            } else {
                // Create new pending record
                $allowance = 0.00;
                $deductions = $auto_deduction;
                $net_salary = $basic_salary + $allowance - $deductions;
                
                $sql = "INSERT INTO payroll (employee_id, month, year, basic_salary, allowance, deductions, net_salary, status) 
                        VALUES ($emp_id, $selected_month, $selected_year, $basic_salary, $allowance, $deductions, $net_salary, 'Pending')";
            }
            
            if ($conn->query($sql)) {
                $generated++;
            }
        }
        $message = "Berhasil membuat/memperbarui $generated slip gaji karyawan untuk periode " . $months_list[$selected_month] . " $selected_year!";
        $message_type = 'success';
    } else {
        $message = "Tidak ada karyawan aktif untuk diproses.";
        $message_type = 'warning';
    }
}

// Handle Update Single Payroll (Allowance, Deduction, Status)
if (isset($_POST['edit_payroll'])) {
    $payroll_id = (int)$_POST['payroll_id'];
    $allowance = (double)$_POST['allowance'];
    $deductions = (double)$_POST['deductions'];
    $status = $conn->real_escape_string($_POST['status']);
    
    // Fetch basic salary first to recalculate net
    $p_query = $conn->query("SELECT basic_salary FROM payroll WHERE payroll_id = $payroll_id");
    if ($p_query && $p_query->num_rows > 0) {
        $p_data = $p_query->fetch_assoc();
        $basic_salary = $p_data['basic_salary'];
        $net_salary = $basic_salary + $allowance - $deductions;
        
        $payment_date_val = ($status == 'Paid') ? "'" . date('Y-m-d') . "'" : "NULL";
        
        $sql = "UPDATE payroll SET 
                    allowance = $allowance, 
                    deductions = $deductions, 
                    net_salary = $net_salary, 
                    status = '$status', 
                    payment_date = $payment_date_val 
                WHERE payroll_id = $payroll_id";
                
        if ($conn->query($sql)) {
            $message = "Slip gaji berhasil diperbarui!";
            $message_type = 'success';
        } else {
            $message = "Gagal memperbarui slip gaji: " . $conn->error;
            $message_type = 'danger';
        }
    }
}

// Handle Mark as Paid Quick Action
if (isset($_GET['pay'])) {
    $payroll_id = (int)$_GET['pay'];
    $today = date('Y-m-d');
    $sql = "UPDATE payroll SET status = 'Paid', payment_date = '$today' WHERE payroll_id = $payroll_id";
    if ($conn->query($sql)) {
        $message = "Gaji berhasil ditandai sebagai Lunas (Paid)!";
        $message_type = 'success';
    } else {
        $message = "Gagal memproses pembayaran: " . $conn->error;
        $message_type = 'danger';
    }
}

// Handle Delete Single Payroll
if (isset($_GET['delete'])) {
    $payroll_id = (int)$_GET['delete'];
    $sql = "DELETE FROM payroll WHERE payroll_id = $payroll_id";
    if ($conn->query($sql)) {
        $message = "Slip gaji berhasil dihapus dari periode ini.";
        $message_type = 'success';
    } else {
        $message = "Gagal menghapus slip gaji: " . $conn->error;
        $message_type = 'danger';
    }
}

// Fetch Payroll Data for current period
$payroll_query = $conn->query("SELECT p.*, e.full_name, e.position, e.department, e.employee_id as emp_code
                              FROM payroll p 
                              JOIN employees e ON p.employee_id = e.employee_id 
                              WHERE p.month = $selected_month AND p.year = $selected_year
                              ORDER BY e.full_name ASC");

$payroll_list = [];
$total_expense = 0;
$total_paid = 0;
$total_pending = 0;

if ($payroll_query) {
    while ($row = $payroll_query->fetch_assoc()) {
        $payroll_list[] = $row;
        $total_expense += $row['net_salary'];
        if ($row['status'] == 'Paid') {
            $total_paid += $row['net_salary'];
        } else {
            $total_pending += $row['net_salary'];
        }
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
        .badge-status { padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: bold; text-transform: uppercase; display: inline-block; }
        .badge-paid { background-color: #d1e7dd; color: #0f5132; border: 1px solid #badbcc; }
        .badge-pending { background-color: #fff3cd; color: #664d03; border: 1px solid #ffecb5; }
        
        /* Print Slip Styles */
        @media print {
            body * { visibility: hidden; }
            #payslipPrintArea, #payslipPrintArea * { visibility: visible; }
            #payslipPrintArea { position: absolute; left: 0; top: 0; width: 100%; }
            .no-print { display: none !important; }
        }
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
                <h1 class="h3 fw-bold text-dark mb-1">Payroll Management</h1>
                <p class="text-muted small mb-0">Kelola slip gaji bulanan, tunjangan, potongan absensi, dan slip pembayaran.</p>
            </div>
            
            <div class="d-flex gap-2 align-items-center flex-wrap">
                <!-- Period Selector -->
                <form method="GET" class="d-flex gap-2">
                    <select name="month" class="form-select form-select-sm" style="width: 140px;" onchange="this.form.submit()">
                        <?php foreach ($months_list as $num => $name): ?>
                            <option value="<?= $num ?>" <?= ($selected_month == $num) ? 'selected' : '' ?>><?= $name ?></option>
                        <?php endforeach; ?>
                    </select>
                    <select name="year" class="form-select form-select-sm" style="width: 100px;" onchange="this.form.submit()">
                        <?php for ($y = date('Y') - 2; $y <= date('Y') + 1; $y++): ?>
                            <option value="<?= $y ?>" <?= ($selected_year == $y) ? 'selected' : '' ?>><?= $y ?></option>
                        <?php endfor; ?>
                    </select>
                </form>
                
                <form method="POST">
                    <button type="submit" name="generate_payroll" class="btn btn-primary px-3 py-2 fw-semibold shadow-sm text-nowrap">
                        <i class="bi bi-gear-fill me-2 animate-spin"></i>Generate / Recalculate
                    </button>
                </form>
            </div>
        </div>

        <!-- Notification -->
        <?php if ($message): ?>
            <div class="alert alert-<?= $message_type ?> alert-dismissible fade show shadow-sm" role="alert">
                <?= $message ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Summary Stats -->
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="card shadow-sm border-0 h-100 bg-primary bg-opacity-10 text-primary">
                    <div class="card-body py-3">
                        <small class="text-secondary fw-semibold text-uppercase" style="font-size: 11px;">Total Pengeluaran Gaji</small>
                        <h4 class="fw-bold mb-0 mt-1 text-dark">Rp <?= number_format($total_expense, 0, ',', '.') ?></h4>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card shadow-sm border-0 h-100 bg-success bg-opacity-10 text-success">
                    <div class="card-body py-3">
                        <small class="text-secondary fw-semibold text-uppercase" style="font-size: 11px;">Sudah Dibayar (Paid)</small>
                        <h4 class="fw-bold mb-0 mt-1 text-dark">Rp <?= number_format($total_paid, 0, ',', '.') ?></h4>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card shadow-sm border-0 h-100 bg-warning bg-opacity-10 text-warning">
                    <div class="card-body py-3">
                        <small class="text-secondary fw-semibold text-uppercase" style="font-size: 11px;">Tertunda (Pending)</small>
                        <h4 class="fw-bold mb-0 mt-1 text-dark">Rp <?= number_format($total_pending, 0, ',', '.') ?></h4>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payroll List Card -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="card-title mb-0 fw-bold text-secondary">
                    <i class="bi bi-wallet2 me-2 text-primary"></i>Daftar Gaji Karyawan - Periode <?= $months_list[$selected_month] ?> <?= $selected_year ?>
                </h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="px-4 py-3">Nama Karyawan</th>
                                <th class="py-3">Jabatan & Dep.</th>
                                <th class="py-3 text-end">Gaji Pokok</th>
                                <th class="py-3 text-end text-success">Tunjangan</th>
                                <th class="py-3 text-end text-danger">Potongan</th>
                                <th class="py-3 text-end fw-bold">Gaji Bersih</th>
                                <th class="py-3 text-center">Status</th>
                                <th class="px-4 py-3 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($payroll_list)): ?>
                                <tr>
                                    <td colspan="8" class="text-center py-5 text-muted">Belum ada slip gaji yang dibuat untuk periode ini. Klik tombol "Generate" di atas.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($payroll_list as $row): ?>
                                <tr>
                                    <td class="px-4 fw-semibold text-dark"><?= htmlspecialchars($row['full_name']) ?></td>
                                    <td>
                                        <div class="fw-medium text-dark"><?= htmlspecialchars($row['position']) ?></div>
                                        <small class="text-muted" style="font-size: 11px;"><?= htmlspecialchars($row['department']) ?></small>
                                    </td>
                                    <td class="text-end text-secondary">Rp <?= number_format($row['basic_salary'], 0, ',', '.') ?></td>
                                    <td class="text-end text-success">+ Rp <?= number_format($row['allowance'], 0, ',', '.') ?></td>
                                    <td class="text-end text-danger">- Rp <?= number_format($row['deductions'], 0, ',', '.') ?></td>
                                    <td class="text-end fw-bold text-primary">Rp <?= number_format($row['net_salary'], 0, ',', '.') ?></td>
                                    <td class="text-center">
                                        <span class="badge-status badge-<?= strtolower($row['status']) ?>">
                                            <?= $row['status'] ?>
                                        </span>
                                    </td>
                                    <td class="px-4 text-center">
                                        <div class="d-flex gap-2 justify-content-center">
                                            <!-- Payslip button -->
                                            <button class="btn btn-sm btn-outline-info" data-bs-toggle="modal" data-bs-target="#payslipModal" onclick="viewPayslip(<?= htmlspecialchars(json_encode($row)) ?>)">
                                                <i class="bi bi-file-earmark-pdf"></i> Slip
                                            </button>
                                            
                                            <!-- Mark paid button -->
                                            <?php if ($row['status'] == 'Pending'): ?>
                                                <a href="payroll.php?month=<?= $selected_month ?>&year=<?= $selected_year ?>&pay=<?= $row['payroll_id'] ?>" class="btn btn-sm btn-success" onclick="return confirm('Tandai gaji karyawan <?= htmlspecialchars($row['full_name']) ?> ini sebagai Paid?')">
                                                    <i class="bi bi-cash"></i> Bayar
                                                </a>
                                            <?php endif; ?>
                                            
                                            <!-- Edit button -->
                                            <button class="btn btn-sm btn-outline-warning" data-bs-toggle="modal" data-bs-target="#editPayrollModal" onclick="editPayroll(<?= htmlspecialchars(json_encode($row)) ?>)">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            
                                            <!-- Delete button -->
                                            <a href="payroll.php?month=<?= $selected_month ?>&year=<?= $selected_year ?>&delete=<?= $row['payroll_id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus catatan gaji karyawan <?= htmlspecialchars($row['full_name']) ?> dari periode ini?')">
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

    <!-- Edit Payroll Modal -->
    <div class="modal fade" id="editPayrollModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-light">
                    <h5 class="modal-title fw-bold"><i class="bi bi-pencil-square me-2 text-warning"></i>Sesuaikan Gaji Bulanan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST">
                    <input type="hidden" name="payroll_id" id="edit_payroll_id">
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Nama Karyawan</label>
                            <input type="text" id="edit_employee_name" class="form-control bg-light" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Gaji Pokok</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="text" id="edit_basic_salary" class="form-control bg-light" readonly>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Tunjangan Tambahan (Allowance)</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" name="allowance" id="edit_allowance" class="form-control" required min="0">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Potongan Gaji (Deductions)</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" name="deductions" id="edit_deductions" class="form-control" required min="0">
                            </div>
                            <small class="text-muted">Potongan otomatis absensi: Rp 150.000 / hari absen.</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Status Gaji</label>
                            <select name="status" id="edit_payroll_status" class="form-select" required>
                                <option value="Pending">Pending</option>
                                <option value="Paid">Paid</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer bg-light border-top-0">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" name="edit_payroll" class="btn btn-primary">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Payslip Modal (Slip Gaji) -->
    <div class="modal fade" id="payslipModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-light no-print">
                    <h5 class="modal-title fw-bold"><i class="bi bi-file-earmark-pdf-fill text-danger me-2"></i>Slip Gaji Karyawan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-5" id="payslipPrintArea">
                    <!-- Payslip Header -->
                    <div class="d-flex justify-content-between align-items-center border-bottom pb-4 mb-4">
                        <div>
                            <h3 class="fw-bold text-primary mb-1">Recruitment<span class="text-secondary">System</span></h3>
                            <p class="text-muted small mb-0">Departemen Sumber Daya Manusia (HRD)</p>
                            <p class="text-muted small mb-0">Jl. Jenderal Sudirman Kav. 21, Jakarta, Indonesia</p>
                        </div>
                        <div class="text-end">
                            <h4 class="fw-bold text-secondary text-uppercase mb-1">SLIP GAJI</h4>
                            <p class="text-muted small mb-0">Periode: <strong><span id="slip_period"></span></strong></p>
                            <p class="text-muted small mb-0">Status: <span id="slip_status" class="badge"></span></p>
                        </div>
                    </div>

                    <!-- Payslip Details -->
                    <div class="row mb-4">
                        <div class="col-6">
                            <h6 class="fw-bold text-secondary mb-2">Penerima:</h6>
                            <table class="table table-sm table-borderless small mb-0">
                                <tr><td class="fw-semibold py-1 ps-0" style="width: 120px;">ID Karyawan</td><td class="py-1">: <span id="slip_emp_code"></span></td></tr>
                                <tr><td class="fw-semibold py-1 ps-0">Nama Karyawan</td><td class="py-1">: <strong><span id="slip_full_name"></span></strong></td></tr>
                                <tr><td class="fw-semibold py-1 ps-0">Jabatan</td><td class="py-1">: <span id="slip_position"></span></td></tr>
                                <tr><td class="fw-semibold py-1 ps-0">Departemen</td><td class="py-1">: <span id="slip_department"></span></td></tr>
                            </table>
                        </div>
                        <div class="col-6 text-end">
                            <h6 class="fw-bold text-secondary mb-2">Detail Pembayaran:</h6>
                            <table class="table table-sm table-borderless small mb-0 float-end" style="width: auto;">
                                <tr><td class="fw-semibold py-1 pe-2 text-end" style="width: 140px;">Metode Pembayaran</td><td class="py-1 text-start">: Transfer Bank</td></tr>
                                <tr><td class="fw-semibold py-1 pe-2 text-end">Tanggal Pembayaran</td><td class="py-1 text-start">: <span id="slip_payment_date"></span></td></tr>
                            </table>
                        </div>
                    </div>

                    <!-- Salary Calculation Table -->
                    <h6 class="fw-bold text-secondary border-bottom pb-2 mb-2">RINCIAN PENGHASILAN</h6>
                    <table class="table table-bordered mb-4 small">
                        <thead class="table-light">
                            <tr>
                                <th>Keterangan</th>
                                <th class="text-end" style="width: 180px;">Pendapatan (+)</th>
                                <th class="text-end" style="width: 180px;">Potongan (-)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Gaji Pokok bulanan</td>
                                <td class="text-end">Rp <span id="slip_basic_salary"></span></td>
                                <td class="text-end text-muted">-</td>
                            </tr>
                            <tr>
                                <td>Tunjangan tambahan (Allowance)</td>
                                <td class="text-end text-success">+ Rp <span id="slip_allowance"></span></td>
                                <td class="text-end text-muted">-</td>
                            </tr>
                            <tr>
                                <td>Potongan Kehadiran (Deductions)</td>
                                <td class="text-end text-muted">-</td>
                                <td class="text-end text-danger">- Rp <span id="slip_deductions"></span></td>
                            </tr>
                            <tr class="fw-bold table-light">
                                <td>TOTAL PENGHASILAN BERSIH (NET SALARY)</td>
                                <td colspan="2" class="text-end text-primary" style="font-size: 15px;">Rp <span id="slip_net_salary"></span></td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- Footer / Signature -->
                    <div class="row pt-4 mt-5">
                        <div class="col-6">
                            <div class="text-muted small">Catatan: Slip gaji ini digenerate secara komputerisasi dan sah tanpa tanda tangan basah.</div>
                        </div>
                        <div class="col-6 text-end" style="min-height: 100px;">
                            <div class="fw-bold text-dark small mb-5">Manajer HRD,</div>
                            <div class="fw-bold text-dark text-decoration-underline small">Recruitment System Admin</div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light no-print border-top-0">
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Tutup</button>
                    <button type="button" class="btn btn-danger px-4" onclick="window.print()">
                        <i class="bi bi-printer me-2"></i>Cetak Slip Gaji
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script>
    function editPayroll(payroll) {
        document.getElementById('edit_payroll_id').value = payroll.payroll_id;
        document.getElementById('edit_employee_name').value = payroll.full_name;
        document.getElementById('edit_basic_salary').value = numberFormat(payroll.basic_salary);
        document.getElementById('edit_allowance').value = parseInt(payroll.allowance);
        document.getElementById('edit_deductions').value = parseInt(payroll.deductions);
        document.getElementById('edit_payroll_status').value = payroll.status;
    }
    
    function viewPayslip(payroll) {
        document.getElementById('slip_emp_code').innerText = 'EMP-' + String(payroll.employee_id).padStart(4, '0');
        document.getElementById('slip_full_name').innerText = payroll.full_name;
        document.getElementById('slip_position').innerText = payroll.position;
        document.getElementById('slip_department').innerText = payroll.department;
        document.getElementById('slip_period').innerText = '<?= $months_list[$selected_month] ?> <?= $selected_year ?>';
        
        const statusEl = document.getElementById('slip_status');
        statusEl.innerText = payroll.status;
        if (payroll.status === 'Paid') {
            statusEl.className = 'badge bg-success';
        } else {
            statusEl.className = 'badge bg-warning text-dark';
        }
        
        document.getElementById('slip_payment_date').innerText = payroll.payment_date ? formatDate(payroll.payment_date) : '-';
        document.getElementById('slip_basic_salary').innerText = numberFormat(payroll.basic_salary);
        document.getElementById('slip_allowance').innerText = numberFormat(payroll.allowance);
        document.getElementById('slip_deductions').innerText = numberFormat(payroll.deductions);
        document.getElementById('slip_net_salary').innerText = numberFormat(payroll.net_salary);
    }
    
    function numberFormat(val) {
        return new Intl.NumberFormat('id-ID').format(val);
    }
    
    function formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
    }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
