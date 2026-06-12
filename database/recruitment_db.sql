-- =============================================
-- DATABASE: Recruitment System
-- =============================================

CREATE DATABASE IF NOT EXISTS recruitment_db;
USE recruitment_db;

-- =============================================
-- TABLE: jobs
-- =============================================
CREATE TABLE jobs (
    job_id INT AUTO_INCREMENT PRIMARY KEY,
    position VARCHAR(100) NOT NULL,
    department VARCHAR(100) NOT NULL,
    location VARCHAR(100) NOT NULL,
    status ENUM('Open', 'Closed') DEFAULT 'Open',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- =============================================
-- TABLE: applicants
-- =============================================
CREATE TABLE applicants (
    applicant_id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone_number VARCHAR(20) NOT NULL,
    education VARCHAR(100) NOT NULL,
    job_id INT,
    status ENUM('Applied', 'Interview', 'Hired', 'Rejected') DEFAULT 'Applied',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (job_id) REFERENCES jobs(job_id) ON DELETE SET NULL
);

-- =============================================
-- TABLE: interviews
-- =============================================
CREATE TABLE interviews (
    interview_id INT AUTO_INCREMENT PRIMARY KEY,
    applicant_id INT NOT NULL,
    job_id INT NOT NULL,
    interview_date DATETIME NOT NULL,
    status ENUM('Scheduled', 'Completed', 'Cancelled') DEFAULT 'Scheduled',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (applicant_id) REFERENCES applicants(applicant_id) ON DELETE CASCADE,
    FOREIGN KEY (job_id) REFERENCES jobs(job_id) ON DELETE CASCADE
);

-- =============================================
-- DUMMY DATA: jobs (10 data)
-- =============================================
INSERT INTO jobs (position, department, location, status) VALUES
('Software Engineer', 'Engineering', 'Jakarta', 'Open'),
('Product Manager', 'Product', 'Bandung', 'Open'),
('UI/UX Designer', 'Design', 'Jakarta', 'Open'),
('Data Analyst', 'Data', 'Surabaya', 'Open'),
('HR Manager', 'Human Resources', 'Jakarta', 'Closed'),
('Marketing Specialist', 'Marketing', 'Yogyakarta', 'Open'),
('DevOps Engineer', 'Engineering', 'Jakarta', 'Open'),
('Content Writer', 'Marketing', 'Bandung', 'Closed'),
('Financial Analyst', 'Finance', 'Jakarta', 'Open'),
('Customer Support', 'Operations', 'Semarang', 'Open');

-- =============================================
-- DUMMY DATA: applicants (10 data)
-- =============================================
INSERT INTO applicants (full_name, email, phone_number, education, job_id, status, created_at) VALUES
('Budi Santoso', 'budi.santoso@email.com', '081234567890', 'S1 Teknik Informatika', 1, 'Interview', '2024-01-15 10:00:00'),
('Siti Rahayu', 'siti.rahayu@email.com', '081234567891', 'S1 Manajemen', 2, 'Applied', '2024-01-20 11:30:00'),
('Andi Wijaya', 'andi.wijaya@email.com', '081234567892', 'S1 Desain Komunikasi Visual', 3, 'Hired', '2024-02-01 09:00:00'),
('Dewi Lestari', 'dewi.lestari@email.com', '081234567893', 'S1 Statistika', 4, 'Interview', '2024-02-10 14:00:00'),
('Rudi Hermawan', 'rudi.hermawan@email.com', '081234567894', 'S1 Psikologi', 5, 'Rejected', '2024-02-15 10:30:00'),
('Nina Kusuma', 'nina.kusuma@email.com', '081234567895', 'S1 Ilmu Komunikasi', 6, 'Applied', '2024-03-01 08:45:00'),
('Agus Pratama', 'agus.pratama@email.com', '081234567896', 'S1 Teknik Informatika', 7, 'Interview', '2024-03-10 13:00:00'),
('Linda Sari', 'linda.sari@email.com', '081234567897', 'S1 Sastra Inggris', 8, 'Hired', '2024-03-15 09:30:00'),
('Hendra Gunawan', 'hendra.gunawan@email.com', '081234567898', 'S1 Akuntansi', 9, 'Applied', '2024-04-01 11:00:00'),
('Maya Putri', 'maya.putri@email.com', '081234567899', 'S1 Manajemen', 10, 'Interview', '2024-04-10 15:00:00');

-- =============================================
-- DUMMY DATA: interviews (10 data)
-- =============================================
INSERT INTO interviews (applicant_id, job_id, interview_date, status, notes) VALUES
(1, 1, '2024-01-20 10:00:00', 'Completed', 'Kandidat memiliki skill yang baik'),
(2, 2, '2024-01-25 14:00:00', 'Scheduled', 'Interview tahap pertama'),
(3, 3, '2024-02-05 09:00:00', 'Completed', 'Lolos dan diterima'),
(4, 4, '2024-02-15 11:00:00', 'Scheduled', 'Interview dengan tim data'),
(5, 5, '2024-02-20 13:00:00', 'Cancelled', 'Kandidat membatalkan'),
(6, 6, '2024-03-05 10:00:00', 'Scheduled', 'Interview marketing'),
(7, 7, '2024-03-15 14:00:00', 'Completed', 'Technical interview'),
(8, 8, '2024-03-20 09:00:00', 'Completed', 'Lolos dan diterima'),
(9, 9, '2024-04-05 11:00:00', 'Scheduled', 'Interview finance'),
(10, 10, '2024-04-15 15:00:00', 'Scheduled', 'Interview customer support');

-- =============================================
-- TABLE: employees
-- =============================================
CREATE TABLE employees (
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
);

-- =============================================
-- TABLE: attendance
-- =============================================
CREATE TABLE attendance (
    attendance_id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT NOT NULL,
    date DATE NOT NULL,
    check_in TIME NULL,
    check_out TIME NULL,
    status ENUM('Present', 'Absent', 'Sick', 'Leave', 'Permit') DEFAULT 'Present',
    notes VARCHAR(255) NULL,
    FOREIGN KEY (employee_id) REFERENCES employees(employee_id) ON DELETE CASCADE,
    UNIQUE KEY unique_employee_date (employee_id, date)
);

-- =============================================
-- TABLE: payroll
-- =============================================
CREATE TABLE payroll (
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
);

-- =============================================
-- DUMMY DATA: employees (4 data)
-- =============================================
INSERT INTO employees (applicant_id, full_name, email, phone_number, position, department, basic_salary, join_date) VALUES
(3, 'Andi Wijaya', 'andi.wijaya@email.com', '081234567892', 'UI/UX Designer', 'Design', 6500000.00, '2024-02-01'),
(8, 'Linda Sari', 'linda.sari@email.com', '081234567897', 'Content Writer', 'Marketing', 5500000.00, '2024-03-15'),
(NULL, 'Budi Santoso', 'budi.santoso@email.com', '081234567890', 'Software Engineer', 'Engineering', 8000000.00, '2024-01-20'),
(NULL, 'Siti Rahayu', 'siti.rahayu@email.com', '081234567891', 'Product Manager', 'Product', 10000000.00, '2024-01-25');

-- =============================================
-- DUMMY DATA: attendance (8 data)
-- =============================================
INSERT INTO attendance (employee_id, date, check_in, check_out, status, notes) VALUES
(1, CURDATE() - INTERVAL 1 DAY, '08:00:00', '17:00:00', 'Present', ''),
(2, CURDATE() - INTERVAL 1 DAY, '08:00:00', '17:00:00', 'Present', ''),
(3, CURDATE() - INTERVAL 1 DAY, '08:00:00', '17:00:00', 'Present', ''),
(4, CURDATE() - INTERVAL 1 DAY, '08:00:00', '17:00:00', 'Present', ''),
(1, CURDATE() - INTERVAL 2 DAY, '08:00:00', '17:00:00', 'Present', ''),
(2, CURDATE() - INTERVAL 2 DAY, NULL, NULL, 'Sick', 'Demam'),
(3, CURDATE() - INTERVAL 2 DAY, NULL, NULL, 'Absent', 'Tanpa Keterangan'),
(4, CURDATE() - INTERVAL 2 DAY, '08:00:00', '17:00:00', 'Present', '');

