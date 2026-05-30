<?php
// =============================================
// REPORTS - reports.php
// =============================================

require_once 'config/database.php';

$page_title = 'Reports - Admin Recruitment System';
$current_page = 'reports';

// Get statistics
$total_jobs = $conn->query("SELECT COUNT(*) as total FROM jobs")->fetch_assoc()['total'];
$total_applicants = $conn->query("SELECT COUNT(*) as total FROM applicants")->fetch_assoc()['total'];
$total_interviews = $conn->query("SELECT COUNT(*) as total FROM interviews")->fetch_assoc()['total'];
$total_hired = $conn->query("SELECT COUNT(*) as total FROM applicants WHERE status = 'Hired'")->fetch_assoc()['total'];

// Get jobs open vs closed
$jobs_open = $conn->query("SELECT COUNT(*) as total FROM jobs WHERE status = 'Open'")->fetch_assoc()['total'];
$jobs_closed = $conn->query("SELECT COUNT(*) as total FROM jobs WHERE status = 'Closed'")->fetch_assoc()['total'];

// Get applicant status distribution
$applicant_applied = $conn->query("SELECT COUNT(*) as total FROM applicants WHERE status = 'Applied'")->fetch_assoc()['total'];
$applicant_interview = $conn->query("SELECT COUNT(*) as total FROM applicants WHERE status = 'Interview'")->fetch_assoc()['total'];
$applicant_hired = $conn->query("SELECT COUNT(*) as total FROM applicants WHERE status = 'Hired'")->fetch_assoc()['total'];
$applicant_rejected = $conn->query("SELECT COUNT(*) as total FROM applicants WHERE status = 'Rejected'")->fetch_assoc()['total'];

// Get interview status distribution
$interview_scheduled = $conn->query("SELECT COUNT(*) as total FROM interviews WHERE status = 'Scheduled'")->fetch_assoc()['total'];
$interview_completed = $conn->query("SELECT COUNT(*) as total FROM interviews WHERE status = 'Completed'")->fetch_assoc()['total'];
$interview_cancelled = $conn->query("SELECT COUNT(*) as total FROM interviews WHERE status = 'Cancelled'")->fetch_assoc()['total'];

// Get top departments by applicants
$top_departments = $conn->query("
    SELECT j.department, COUNT(a.applicant_id) as total 
    FROM jobs j 
    LEFT JOIN applicants a ON j.job_id = a.job_id 
    GROUP BY j.department 
    ORDER BY total DESC 
    LIMIT 5
");

include 'includes/header.php';
?>

<!-- Page Header -->
<div class="page-header">
    <h1>Reports</h1>
    <p>Laporan dan statistik recruitment</p>
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

<!-- Charts Row -->
<div class="row g-4 mb-4">
    <!-- Job Status Chart -->
    <div class="col-xl-4">
        <div class="content-card">
            <div class="card-header">
                <h5><i class="bi bi-pie-chart-fill me-2"></i>Job Status</h5>
            </div>
            <div class="chart-container" style="height: 250px;">
                <canvas id="jobStatusChart"></canvas>
            </div>
        </div>
    </div>
    
    <!-- Applicant Status Chart -->
    <div class="col-xl-4">
        <div class="content-card">
            <div class="card-header">
                <h5><i class="bi bi-pie-chart-fill me-2"></i>Applicant Status</h5>
            </div>
            <div class="chart-container" style="height: 250px;">
                <canvas id="applicantStatusChart"></canvas>
            </div>
        </div>
    </div>
    
    <!-- Interview Status Chart -->
    <div class="col-xl-4">
        <div class="content-card">
            <div class="card-header">
                <h5><i class="bi bi-pie-chart-fill me-2"></i>Interview Status</h5>
            </div>
            <div class="chart-container" style="height: 250px;">
                <canvas id="interviewStatusChart"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Department Stats -->
<div class="row g-4">
    <div class="col-xl-6">
        <div class="content-card">
            <div class="card-header">
                <h5><i class="bi bi-building me-2"></i>Top Departments by Applicants</h5>
            </div>
            <div class="chart-container" style="height: 300px;">
                <canvas id="departmentChart"></canvas>
            </div>
        </div>
    </div>
    
    <!-- Summary Table -->
    <div class="col-xl-6">
        <div class="content-card">
            <div class="card-header">
                <h5><i class="bi bi-table me-2"></i>Summary</h5>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Category</th>
                            <th>Status</th>
                            <th class="text-end">Count</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td rowspan="2"><strong>Jobs</strong></td>
                            <td><span class="badge badge-open">Open</span></td>
                            <td class="text-end"><?php echo $jobs_open; ?></td>
                        </tr>
                        <tr>
                            <td><span class="badge badge-closed">Closed</span></td>
                            <td class="text-end"><?php echo $jobs_closed; ?></td>
                        </tr>
                        <tr>
                            <td rowspan="4"><strong>Applicants</strong></td>
                            <td><span class="badge badge-applied">Applied</span></td>
                            <td class="text-end"><?php echo $applicant_applied; ?></td>
                        </tr>
                        <tr>
                            <td><span class="badge badge-interview">Interview</span></td>
                            <td class="text-end"><?php echo $applicant_interview; ?></td>
                        </tr>
                        <tr>
                            <td><span class="badge badge-hired">Hired</span></td>
                            <td class="text-end"><?php echo $applicant_hired; ?></td>
                        </tr>
                        <tr>
                            <td><span class="badge badge-rejected">Rejected</span></td>
                            <td class="text-end"><?php echo $applicant_rejected; ?></td>
                        </tr>
                        <tr>
                            <td rowspan="3"><strong>Interviews</strong></td>
                            <td><span class="badge badge-scheduled">Scheduled</span></td>
                            <td class="text-end"><?php echo $interview_scheduled; ?></td>
                        </tr>
                        <tr>
                            <td><span class="badge badge-completed">Completed</span></td>
                            <td class="text-end"><?php echo $interview_completed; ?></td>
                        </tr>
                        <tr>
                            <td><span class="badge badge-cancelled">Cancelled</span></td>
                            <td class="text-end"><?php echo $interview_cancelled; ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Job Status Chart
    new Chart(document.getElementById('jobStatusChart'), {
        type: 'doughnut',
        data: {
            labels: ['Open', 'Closed'],
            datasets: [{
                data: [<?php echo $jobs_open; ?>, <?php echo $jobs_closed; ?>],
                backgroundColor: ['#10b981', '#ef4444'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
    
    // Applicant Status Chart
    new Chart(document.getElementById('applicantStatusChart'), {
        type: 'doughnut',
        data: {
            labels: ['Applied', 'Interview', 'Hired', 'Rejected'],
            datasets: [{
                data: [<?php echo $applicant_applied; ?>, <?php echo $applicant_interview; ?>, <?php echo $applicant_hired; ?>, <?php echo $applicant_rejected; ?>],
                backgroundColor: ['#6b7280', '#f59e0b', '#10b981', '#ef4444'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
    
    // Interview Status Chart
    new Chart(document.getElementById('interviewStatusChart'), {
        type: 'doughnut',
        data: {
            labels: ['Scheduled', 'Completed', 'Cancelled'],
            datasets: [{
                data: [<?php echo $interview_scheduled; ?>, <?php echo $interview_completed; ?>, <?php echo $interview_cancelled; ?>],
                backgroundColor: ['#2563eb', '#10b981', '#ef4444'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
    
    // Department Chart
    new Chart(document.getElementById('departmentChart'), {
        type: 'bar',
        data: {
            labels: [<?php 
                $labels = [];
                $data = [];
                $top_departments->data_seek(0);
                while ($row = $top_departments->fetch_assoc()) {
                    $labels[] = "'" . $row['department'] . "'";
                    $data[] = $row['total'];
                }
                echo implode(', ', $labels);
            ?>],
            datasets: [{
                label: 'Jumlah Pelamar',
                data: [<?php echo implode(', ', $data); ?>],
                backgroundColor: 'rgba(37, 99, 235, 0.8)',
                borderRadius: 8,
                barThickness: 40
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                x: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    },
                    grid: {
                        color: 'rgba(0, 0, 0, 0.05)'
                    }
                },
                y: {
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
