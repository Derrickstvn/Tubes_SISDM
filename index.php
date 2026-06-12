<?php
require_once 'config/database.php';
$page_title = 'Dashboard Admin Recruitment System';
$current_page = 'dashboard';

$total_jobs = $conn->query("SELECT COUNT(*) as total FROM jobs")->fetch_assoc()['total'];
$total_applicants = $conn->query("SELECT COUNT(*) as total FROM applicants")->fetch_assoc()['total'];
$total_interviews = $conn->query("SELECT COUNT(*) as total FROM interviews")->fetch_assoc()['total'];
$total_hired = $conn->query("SELECT COUNT(*) as total FROM applicants WHERE status = 'Hired'")->fetch_assoc()['total'];

$monthly_data = [];
$months = [];
for ($i = 5; $i >= 0; $i--) {
    $month = date('Y-m', strtotime("-$i months"));
    $months[] = date('M Y', strtotime("-$i months"));
    $query = "SELECT COUNT(*) as total FROM applicants WHERE DATE_FORMAT(created_at, '%Y-%m') = '$month'";
    $result = $conn->query($query);
    $monthly_data[] = $result->fetch_assoc()['total'];
}

$recent_applicants_query = $conn->query("SELECT a.*, j.position FROM applicants a LEFT JOIN jobs j ON a.job_id = j.job_id ORDER BY a.created_at DESC LIMIT 5");
$applicants_list = [];
while ($row = $recent_applicants_query->fetch_assoc()) {
    $applicants_list[] = $row;
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
        .stat-icon { width: 48px; height: 48px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 22px; }
        .avatar { width: 40px; height: 40px; border-radius: 50%; background-color: #2563eb; color: #ffffff; font-weight: bold; display: flex; align-items: center; justify-content: center; }
        .badge-status { padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: bold; text-transform: uppercase; display: inline-block; }
        .badge-applied { background-color: #e9ecef; color: #495057; border: 1px solid #ced4da; }
        .badge-interview { background-color: #fff3cd; color: #664d03; border: 1px solid #ffecb5; }
        .badge-hired { background-color: #d1e7dd; color: #0f5132; border: 1px solid #badbcc; }
        .badge-rejected { background-color: #f8d7da; color: #842029; border: 1px solid #f5c2c7; }
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
        <div class="mb-4">
            <h1 class="h3 fw-bold text-dark mb-1">Dashboard</h1>
            <p class="text-muted small mb-0">Selamat datang di Admin Recruitment System</p>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-6 col-lg-3">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="stat-icon bg-primary bg-opacity-10 text-primary"><i class="bi bi-briefcase-fill"></i></div>
                        <div><h3 class="fw-bold text-dark mb-0"><?= $total_jobs ?></h3><p class="text-muted small mb-0">Total Jobs</p></div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="stat-icon bg-success bg-opacity-10 text-success"><i class="bi bi-people-fill"></i></div>
                        <div><h3 class="fw-bold text-dark mb-0"><?= $total_applicants ?></h3><p class="text-muted small mb-0">Total Applicants</p></div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="stat-icon bg-warning bg-opacity-10 text-warning"><i class="bi bi-calendar-check-fill"></i></div>
                        <div><h3 class="fw-bold text-dark mb-0"><?= $total_interviews ?></h3><p class="text-muted small mb-0">Total Interviews</p></div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="stat-icon bg-info bg-opacity-10 text-info"><i class="bi bi-person-check-fill"></i></div>
                        <div><h3 class="fw-bold text-dark mb-0"><?= $total_hired ?></h3><p class="text-muted small mb-0">Total Hired</p></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-12 col-lg-8">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-white py-3 border-bottom-0">
                        <h5 class="card-title mb-0 fw-bold text-secondary"><i class="bi bi-graph-up me-2 text-primary"></i>Statistik Pelamar per Bulan</h5>
                    </div>
                    <div class="card-body">
                        <div style="position: relative; height: 300px;"><canvas id="applicantChart"></canvas></div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-lg-4">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-white py-3 border-bottom-0">
                        <h5 class="card-title mb-0 fw-bold text-secondary"><i class="bi bi-clock-history me-2 text-primary"></i>Pelamar Terbaru</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <tbody>
                                    <?php if (empty($applicants_list)): ?>
                                        <tr><td class="text-center py-5 text-muted small">Belum ada pelamar terbaru.</td></tr>
                                    <?php else: ?>
                                        <?php foreach ($applicants_list as $applicant):
                                            $initial = strtoupper(substr($applicant['full_name'], 0, 1));
                                            $status_class = strtolower($applicant['status']);
                                        ?>
                                        <tr>
                                            <td class="px-3 border-0">
                                                <div class="d-flex align-items-center gap-3">
                                                    <div class="avatar flex-shrink-0"><?= $initial ?></div>
                                                    <div class="overflow-hidden">
                                                        <h6 class="mb-0 text-truncate small fw-bold text-dark"><?= htmlspecialchars($applicant['full_name']) ?></h6>
                                                        <small class="text-muted text-truncate d-block" style="font-size: 11px;"><?= htmlspecialchars($applicant['position'] ?? 'N/A') ?></small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-3 text-end border-0"><span class="badge-status badge-<?= $status_class ?>"><?= $applicant['status'] ?></span></td>
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

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('applicantChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?= json_encode($months) ?>,
                datasets: [{
                    label: 'Jumlah Pelamar',
                    data: <?= json_encode($monthly_data) ?>,
                    backgroundColor: 'rgba(37, 99, 235, 0.8)',
                    borderColor: 'rgba(37, 99, 235, 1)',
                    borderWidth: 1,
                    borderRadius: 8,
                    barThickness: window.innerWidth > 768 ? 40 : 20
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, ticks: { stepSize: 1 }, grid: { color: 'rgba(0, 0, 0, 0.05)' } },
                    x: { grid: { display: false } }
                }
            }
        });
    });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>