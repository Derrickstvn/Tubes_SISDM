<?php
// =============================================
// DASHBOARD - index.php
// =============================================

require_once 'config/database.php';

$page_title = 'Dashboard - Admin Recruitment System';
$current_page = 'dashboard';

// Get statistics
$total_jobs = $conn->query("SELECT COUNT(*) as total FROM jobs")->fetch_assoc()['total'];
$total_applicants = $conn->query("SELECT COUNT(*) as total FROM applicants")->fetch_assoc()['total'];
$total_interviews = $conn->query("SELECT COUNT(*) as total FROM interviews")->fetch_assoc()['total'];
$total_hired = $conn->query("SELECT COUNT(*) as total FROM applicants WHERE status = 'Hired'")->fetch_assoc()['total'];

// Get monthly applicant data for chart
$monthly_data = [];
$months = [];
for ($i = 5; $i >= 0; $i--) {
    $month = date('Y-m', strtotime("-$i months"));
    $months[] = date('M Y', strtotime("-$i months"));
    $query = "SELECT COUNT(*) as total FROM applicants WHERE DATE_FORMAT(created_at, '%Y-%m') = '$month'";
    $result = $conn->query($query);
    $monthly_data[] = $result->fetch_assoc()['total'];
}

// Get recent applicants
$recent_applicants = $conn->query("
    SELECT a.*, j.position 
    FROM applicants a 
    LEFT JOIN jobs j ON a.job_id = j.job_id 
    ORDER BY a.created_at DESC 
    LIMIT 5
");

include 'includes/header.php';
?>

<!-- Page Header -->
<div class="page-header">
    <h1>Dashboard</h1>
    <p>Selamat datang di Admin Recruitment System</p>
</div>

<!-- Statistics Cards -->
<div class="row g-4 mb-4">
    <div class="col-xl-3 col-md-6">
        <div class="stat-card">
            <div class="icon blue">
                <i class="bi bi-briefcase-fill"></i>
            </div>
            <h3><?php echo $total_jobs; ?></h3>
            <p>Total Jobs</p>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="stat-card">
            <div class="icon green">
                <i class="bi bi-people-fill"></i>
            </div>
            <h3><?php echo $total_applicants; ?></h3>
            <p>Total Applicants</p>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="stat-card">
            <div class="icon orange">
                <i class="bi bi-calendar-check-fill"></i>
            </div>
            <h3><?php echo $total_interviews; ?></h3>
            <p>Total Interviews</p>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="stat-card">
            <div class="icon cyan">
                <i class="bi bi-person-check-fill"></i>
            </div>
            <h3><?php echo $total_hired; ?></h3>
            <p>Total Hired</p>
        </div>
    </div>
</div>

<!-- Charts & Tables -->
<div class="row g-4">
    <!-- Chart -->
    <div class="col-xl-8">
        <div class="content-card">
            <div class="card-header">
                <h5><i class="bi bi-graph-up me-2"></i>Statistik Pelamar per Bulan</h5>
            </div>
            <div class="chart-container">
                <canvas id="applicantChart"></canvas>
            </div>
        </div>
    </div>
    
    <!-- Recent Applicants -->
    <div class="col-xl-4">
        <div class="content-card">
            <div class="card-header">
                <h5><i class="bi bi-clock-history me-2"></i>Pelamar Terbaru</h5>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <tbody>
                        <?php while ($row = $recent_applicants->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                        <?php echo strtoupper(substr($row['full_name'], 0, 1)); ?>
                                    </div>
                                    <div>
                                        <h6 class="mb-0" style="font-size: 0.9rem;"><?php echo htmlspecialchars($row['full_name']); ?></h6>
                                        <small class="text-muted"><?php echo htmlspecialchars($row['position'] ?? 'N/A'); ?></small>
                                    </div>
                                </div>
                            </td>
                            <td class="text-end">
                                <span class="badge badge-<?php echo strtolower($row['status']); ?>">
                                    <?php echo $row['status']; ?>
                                </span>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
// Chart.js - Applicant Statistics
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('applicantChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($months); ?>,
            datasets: [{
                label: 'Jumlah Pelamar',
                data: <?php echo json_encode($monthly_data); ?>,
                backgroundColor: 'rgba(37, 99, 235, 0.8)',
                borderColor: 'rgba(37, 99, 235, 1)',
                borderWidth: 1,
                borderRadius: 8,
                barThickness: 40
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    },
                    grid: {
                        color: 'rgba(0, 0, 0, 0.05)'
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            }
        }
    });
});
</script>

<?php include 'includes/footer.php'; ?>
