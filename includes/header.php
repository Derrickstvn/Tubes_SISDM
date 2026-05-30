<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'Admin Recruitment System'; ?></title>
    
    <!-- Google Fonts - Poppins -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="wrapper">
        <!-- Sidebar -->
        <nav class="sidebar">
            <div class="sidebar-header">
                <h3><i class="bi bi-briefcase-fill me-2"></i>Recruit</h3>
                <p>Admin Panel</p>
            </div>
            <div class="sidebar-menu">
                <ul>
                    <li>
                        <a href="index.php" class="<?php echo ($current_page == 'dashboard') ? 'active' : ''; ?>">
                            <i class="bi bi-grid-1x2-fill"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li>
                        <a href="jobs.php" class="<?php echo ($current_page == 'jobs') ? 'active' : ''; ?>">
                            <i class="bi bi-file-earmark-text-fill"></i>
                            <span>Job Management</span>
                        </a>
                    </li>
                    <li>
                        <a href="applicants.php" class="<?php echo ($current_page == 'applicants') ? 'active' : ''; ?>">
                            <i class="bi bi-people-fill"></i>
                            <span>Applicant Management</span>
                        </a>
                    </li>
                    <li>
                        <a href="interviews.php" class="<?php echo ($current_page == 'interviews') ? 'active' : ''; ?>">
                            <i class="bi bi-calendar-check-fill"></i>
                            <span>Interview Schedule</span>
                        </a>
                    </li>
                    <li>
                        <a href="reports.php" class="<?php echo ($current_page == 'reports') ? 'active' : ''; ?>">
                            <i class="bi bi-bar-chart-fill"></i>
                            <span>Reports</span>
                        </a>
                    </li>
                </ul>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="main-content">
