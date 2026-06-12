<?php
require_once 'config/database.php';
$page_title = 'Reports Admin Recruitment System';
$current_page = 'reports';

$total_jobs = $conn->query("SELECT COUNT(*) as total FROM jobs")->fetch_assoc()['total'];
$total_applicants = $conn->query("SELECT COUNT(*) as total FROM applicants")->fetch_assoc()['total'];
$total_interviews = $conn->query("SELECT COUNT(*) as total FROM interviews")->fetch_assoc()['total'];
$total_hired = $conn->query("SELECT COUNT(*) as total FROM applicants WHERE status = 'Hired'")->fetch_assoc()['total'];

$jobs_open = $conn->query("SELECT COUNT(*) as total FROM jobs WHERE status = 'Open'")->fetch_assoc()['total'];
$jobs_closed = $conn->query("SELECT COUNT(*) as total FROM jobs WHERE status = 'Closed'")->fetch_assoc()['total'];

$applicant_applied = $conn->query("SELECT COUNT(*) as total FROM applicants WHERE status = 'Applied'")->fetch_assoc()['total'];
$applicant_interview = $conn->query("SELECT COUNT(*) as total FROM applicants WHERE status = 'Interview'")->fetch_assoc()['total'];
$applicant_hired = $conn->query("SELECT COUNT(*) as total FROM applicants WHERE status = 'Hired'")->fetch_assoc()['total'];
$applicant_rejected = $conn->query("SELECT COUNT(*) as total FROM applicants WHERE status = 'Rejected'")->fetch_assoc()['total'];

$interview_scheduled = $conn->query("SELECT COUNT(*) as total FROM interviews WHERE status = 'Scheduled'")->fetch_assoc()['total'];
$interview_completed = $conn->query("SELECT COUNT(*) as total FROM interviews WHERE status = 'Completed'")->fetch_assoc()['total'];
$interview_cancelled = $conn->query("SELECT COUNT(*) as total FROM interviews WHERE status = 'Cancelled'")->fetch_assoc()['total'];

$top_departments = $conn->query("SELECT j.department, COUNT(a.applicant_id) as total FROM jobs j LEFT JOIN applicants a ON j.job_id = a.job_id GROUP BY j.department ORDER BY total DESC LIMIT 5");
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
            <h1 class="h3 fw-bold text-dark mb-1">Reports</h1>
            <p class="text-muted small mb-0">Laporan analitik ringkas dan statistik performa rekrutmen.</p>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-6 col-lg-3"><div class="card shadow-sm border-0 text-center py-3"><h3 class="fw-bold text-primary mb-0"><?= $total_jobs ?></h3><p class="text-muted small mb-0 fw-medium">Total Jobs</p></div></div>
            <div class="col-6 col-lg-3"><div class="card shadow-sm border-0 text-center py-3"><h3 class="fw-bold text-primary mb-0"><?= $total_applicants ?></h3><p class="text-muted small mb-0 fw-medium">Total Applicants</p></div></div>
            <div class="col-6 col-lg-3"><div class="card shadow-sm border-0 text-center py-3"><h3 class="fw-bold text-primary mb-0"><?= $total_interviews ?></h3><p class="text-muted small mb-0 fw-medium">Total Interviews</p></div></div>
            <div class="col-6 col-lg-3"><div class="card shadow-sm border-0 text-center py-3"><h3 class="fw-bold text-primary mb-0"><?= $total_hired ?></h3><p class="text-muted small mb-0 fw-medium">Total Hired</p></div></div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-12 col-md-4">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-white py-3 border-0"><h6 class="card-title mb-0 fw-bold text-secondary">Job Status</h6></div>
                    <div class="card-body d-flex align-items-center justify-content-center"><div style="position: relative; height: 220px; width: 100%;"><canvas id="jobStatusChart"></canvas></div></div>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-white py-3 border-0"><h6 class="card-title mb-0 fw-bold text-secondary">Applicant Status</h6></div>
                    <div class="card-body d-flex align-items-center justify-content-center"><div style="position: relative; height: 220px; width: 100%;"><canvas id="applicantStatusChart"></canvas></div></div>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-white py-3 border-0"><h6 class="card-title mb-0 fw-bold text-secondary">Interview Status</h6></div>
                    <div class="card-body d-flex align-items-center justify-content-center"><div style="position: relative; height: 220px; width: 100%;"><canvas id="interviewStatusChart"></canvas></div></div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-12 col-lg-6">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-white py-3 border-0"><h6 class="card-title mb-0 fw-bold text-secondary">Top Departments by Applicants</h6></div>
                    <div class="card-body"><div style="position: relative; height: 280px;"><canvas id="departmentChart"></canvas></div></div>
                </div>
            </div>
            <div class="col-12 col-lg-6">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-white py-3 border-0"><h6 class="card-title mb-0 fw-bold text-secondary">Summary Data</h6></div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light"><tr><th class="px-4">Category</th><th>Status</th><th class="px-4 text-end">Count</th></tr></thead>
                                <tbody>
                                    <tr><td class="px-4 fw-bold text-dark" rowspan="2">Jobs</td><td><span class="badge bg-success bg-opacity-10 text-success px-2 py-1">Open</span></td><td class="px-4 text-end fw-semibold"><?= $jobs_open ?></td></tr>
                                    <tr><td><span class="badge bg-danger bg-opacity-10 text-danger px-2 py-1">Closed</span></td><td class="px-4 text-end fw-semibold"><?= $jobs_closed ?></td></tr>
                                    <tr><td class="px-4 fw-bold text-dark" rowspan="4">Applicants</td><td><span class="badge bg-secondary bg-opacity-10 text-secondary px-2 py-1">Applied</span></td><td class="px-4 text-end fw-semibold"><?= $applicant_applied ?></td></tr>
                                    <tr><td><span class="badge bg-warning bg-opacity-10 text-warning px-2 py-1">Interview</span></td><td class="px-4 text-end fw-semibold"><?= $applicant_interview ?></td></tr>
                                    <tr><td><span class="badge bg-success bg-opacity-10 text-success px-2 py-1">Hired</span></td><td class="px-4 text-end fw-semibold"><?= $applicant_hired ?></td></tr>
                                    <tr><td><span class="badge bg-danger bg-opacity-10 text-danger px-2 py-1">Rejected</span></td><td class="px-4 text-end fw-semibold"><?= $applicant_rejected ?></td></tr>
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
        new Chart(document.getElementById('jobStatusChart'), {
            type: 'doughnut',
            data: { labels: ['Open', 'Closed'], datasets: [{ data: [<?= $jobs_open ?>, <?= $jobs_closed ?>], backgroundColor: ['#10b981', '#ef4444'], borderWidth: 0 }] },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 11 } } } } }
        });
        new Chart(document.getElementById('applicantStatusChart'), {
            type: 'doughnut',
            data: { labels: ['Applied', 'Interview', 'Hired', 'Rejected'], datasets: [{ data: [<?= $applicant_applied ?>, <?= $applicant_interview ?>, <?= $applicant_hired ?>, <?= $applicant_rejected ?>], backgroundColor: ['#6b7280', '#f59e0b', '#10b981', '#ef4444'], borderWidth: 0 }] },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 11 } } } } }
        });
        new Chart(document.getElementById('interviewStatusChart'), {
            type: 'doughnut',
            data: { labels: ['Scheduled', 'Completed', 'Cancelled'], datasets: [{ data: [<?= $interview_scheduled ?>, <?= $interview_completed ?>, <?= $interview_cancelled ?>], backgroundColor: ['#2563eb', '#10b981', '#ef4444'], borderWidth: 0 }] },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 11 } } } } }
        });
        <?php
        $labels = []; $data = [];
        $top_departments->data_seek(0);
        while ($row = $top_departments->fetch_assoc()) { $labels[] = $row['department']; $data[] = $row['total']; }
        ?>
        new Chart(document.getElementById('departmentChart'), {
            type: 'bar',
            data: { labels: <?= json_encode($labels) ?>, datasets: [{ label: 'Jumlah Pelamar', data: <?= json_encode($data) ?>, backgroundColor: 'rgba(37, 99, 235, 0.8)', borderRadius: 4 }] },
            options: { 
                indexAxis: 'y', responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } },
                scales: { x: { beginAtZero: true, ticks: { stepSize: 1 }, grid: { color: 'rgba(0, 0, 0, 0.05)' } }, y: { grid: { display: false } } }
            }
        });
    });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>