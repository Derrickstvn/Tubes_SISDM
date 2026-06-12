<?php
// =============================================
// DATABASE INITIALIZER: Payroll & Attendance
// =============================================

function check_and_init_db($conn) {
    // Check if tables already exist
    $table_check = $conn->query("SHOW TABLES LIKE 'employees'");
    if ($table_check && $table_check->num_rows > 0) {
        return; // Already initialized
    }

    // 1. Create employees table
    $sql_employees = "CREATE TABLE IF NOT EXISTS employees (
        employee_id INT AUTO_INCREMENT PRIMARY KEY,
        applicant_id INT NULL,
        full_name VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL,
        phone_number VARCHAR(20) NOT NULL,
        position VARCHAR(100) NOT NULL,
        department VARCHAR(100) NOT NULL,
        basic_salary DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
        join_date DATE NOT NULL,
        status ENUM('Active', 'Resigned') DEFAULT 'Active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (applicant_id) REFERENCES applicants(applicant_id) ON DELETE SET NULL
    )";
    
    if (!$conn->query($sql_employees)) {
        die("Gagal membuat tabel employees: " . $conn->error);
    }

    // 2. Create attendance table
    $sql_attendance = "CREATE TABLE IF NOT EXISTS attendance (
        attendance_id INT AUTO_INCREMENT PRIMARY KEY,
        employee_id INT NOT NULL,
        date DATE NOT NULL,
        check_in TIME NULL,
        check_out TIME NULL,
        status ENUM('Present', 'Absent', 'Sick', 'Leave', 'Permit') DEFAULT 'Present',
        notes VARCHAR(255) NULL,
        FOREIGN KEY (employee_id) REFERENCES employees(employee_id) ON DELETE CASCADE,
        UNIQUE KEY unique_employee_date (employee_id, date)
    )";
    
    if (!$conn->query($sql_attendance)) {
        die("Gagal membuat tabel attendance: " . $conn->error);
    }

    // 3. Create payroll table
    $sql_payroll = "CREATE TABLE IF NOT EXISTS payroll (
        payroll_id INT AUTO_INCREMENT PRIMARY KEY,
        employee_id INT NOT NULL,
        month INT NOT NULL,
        year INT NOT NULL,
        basic_salary DECIMAL(12, 2) NOT NULL,
        allowance DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
        deductions DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
        net_salary DECIMAL(12, 2) NOT NULL,
        payment_date DATE NULL,
        status ENUM('Pending', 'Paid') DEFAULT 'Pending',
        FOREIGN KEY (employee_id) REFERENCES employees(employee_id) ON DELETE CASCADE,
        UNIQUE KEY unique_employee_month_year (employee_id, month, year)
    )";
    
    if (!$conn->query($sql_payroll)) {
        die("Gagal membuat tabel payroll: " . $conn->error);
    }

    // Seed dummy employees
    // Try to get hired applicants first to link them
    $hired_applicants = $conn->query("SELECT * FROM applicants WHERE status = 'Hired'");
    $hired_list = [];
    if ($hired_applicants) {
        while ($row = $hired_applicants->fetch_assoc()) {
            $hired_list[] = $row;
        }
    }

    // Define dummy employees
    $employees_data = [
        [
            'applicant_id' => isset($hired_list[0]) ? $hired_list[0]['applicant_id'] : 'NULL',
            'full_name' => isset($hired_list[0]) ? $hired_list[0]['full_name'] : 'Andi Wijaya',
            'email' => isset($hired_list[0]) ? $hired_list[0]['email'] : 'andi.wijaya@email.com',
            'phone_number' => isset($hired_list[0]) ? $hired_list[0]['phone_number'] : '081234567892',
            'position' => 'UI/UX Designer',
            'department' => 'Design',
            'basic_salary' => 6500000.00,
            'join_date' => '2024-02-01'
        ],
        [
            'applicant_id' => isset($hired_list[1]) ? $hired_list[1]['applicant_id'] : 'NULL',
            'full_name' => isset($hired_list[1]) ? $hired_list[1]['full_name'] : 'Linda Sari',
            'email' => isset($hired_list[1]) ? $hired_list[1]['email'] : 'linda.sari@email.com',
            'phone_number' => isset($hired_list[1]) ? $hired_list[1]['phone_number'] : '081234567897',
            'position' => 'Content Writer',
            'department' => 'Marketing',
            'basic_salary' => 5500000.00,
            'join_date' => '2024-03-15'
        ],
        [
            'applicant_id' => 'NULL',
            'full_name' => 'Budi Santoso',
            'email' => 'budi.santoso@email.com',
            'phone_number' => '081234567890',
            'position' => 'Software Engineer',
            'department' => 'Engineering',
            'basic_salary' => 8000000.00,
            'join_date' => '2024-01-20'
        ],
        [
            'applicant_id' => 'NULL',
            'full_name' => 'Siti Rahayu',
            'email' => 'siti.rahayu@email.com',
            'phone_number' => '081234567891',
            'position' => 'Product Manager',
            'department' => 'Product',
            'basic_salary' => 10000000.00,
            'join_date' => '2024-01-25'
        ]
    ];

    foreach ($employees_data as $emp) {
        $app_id = $emp['applicant_id'];
        $name = $conn->real_escape_string($emp['full_name']);
        $email = $conn->real_escape_string($emp['email']);
        $phone = $conn->real_escape_string($emp['phone_number']);
        $pos = $conn->real_escape_string($emp['position']);
        $dept = $conn->real_escape_string($emp['department']);
        $salary = $emp['basic_salary'];
        $join = $emp['join_date'];

        $insert_emp = "INSERT INTO employees (applicant_id, full_name, email, phone_number, position, department, basic_salary, join_date, status) 
                       VALUES ($app_id, '$name', '$email', '$phone', '$pos', '$dept', $salary, '$join', 'Active')";
        $conn->query($insert_emp);
    }

    // Seed dummy attendance for the last 5 days
    $emp_query = $conn->query("SELECT employee_id FROM employees");
    if ($emp_query) {
        $emp_ids = [];
        while ($r = $emp_query->fetch_assoc()) {
            $emp_ids[] = $r['employee_id'];
        }

        if (!empty($emp_ids)) {
            for ($day = 1; $day <= 5; $day++) {
                $date_str = date('Y-m-d', strtotime("-$day days"));
                foreach ($emp_ids as $idx => $emp_id) {
                    $status = 'Present';
                    $check_in = '08:00:00';
                    $check_out = '17:00:00';
                    $notes = '';

                    // Add variety
                    if ($idx == 2 && $day == 2) {
                        $status = 'Absent';
                        $check_in = 'NULL';
                        $check_out = 'NULL';
                        $notes = 'Tanpa Keterangan';
                    } elseif ($idx == 1 && $day == 3) {
                        $status = 'Sick';
                        $check_in = 'NULL';
                        $check_out = 'NULL';
                        $notes = 'Demam';
                    } elseif ($idx == 0 && $day == 4) {
                        $status = 'Permit';
                        $check_in = 'NULL';
                        $check_out = 'NULL';
                        $notes = 'Urusan Keluarga';
                    }

                    $ci_val = ($check_in == 'NULL') ? "NULL" : "'$check_in'";
                    $co_val = ($check_out == 'NULL') ? "NULL" : "'$check_out'";
                    
                    $insert_att = "INSERT IGNORE INTO attendance (employee_id, date, check_in, check_out, status, notes) 
                                   VALUES ($emp_id, '$date_str', $ci_val, $co_val, '$status', '$notes')";
                    $conn->query($insert_att);
                }
            }
        }
    }
}
?>
