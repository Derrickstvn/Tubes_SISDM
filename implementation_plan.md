# Implementation Plan - Payroll & Attendance (Absensi) Features

This plan outlines the design and implementation for adding the **Employees**, **Attendance (Absensi)**, and **Payroll (Penggajian)** menus to the recruitment system.

---

## Database Initialization

Since command-line MySQL is not globally configured in this environment, I built an automatic database migration system. The first time you open `employees.php`, `attendance.php`, or `payroll.php`, the application will check for the tables and automatically create them and seed them with dummy data. No manual command-line SQL execution is needed.

---

## Proposed Changes

We introduced 3 new database tables, updated the navbar in the 5 existing pages, and added 3 new functional pages.

### 1. Database Schema

We created the following tables in the database:
- `employees`: Holds employee profile details, basic salary, position, department, and link to recruitment (optional).
- `attendance`: Records daily attendance statuses (Present, Absent, Sick, Leave, Permit) for each employee.
- `payroll`: Manages monthly payroll generation, calculating basic salary + allowance - deductions (calculated from attendance), payment date, and status.

We updated the [recruitment_db.sql](file:///c:/Users/derri/Downloads/Recruitment_SISDM/database/recruitment_db.sql) file to include these definitions.

---

### 2. Navbar Integration

We modified the navigation bar in all existing PHP files:
- [index.php](file:///c:/Users/derri/Downloads/Recruitment_SISDM/index.php)
- [jobs.php](file:///c:/Users/derri/Downloads/Recruitment_SISDM/jobs.php)
- [applicants.php](file:///c:/Users/derri/Downloads/Recruitment_SISDM/applicants.php)
- [interviews.php](file:///c:/Users/derri/Downloads/Recruitment_SISDM/interviews.php)
- [reports.php](file:///c:/Users/derri/Downloads/Recruitment_SISDM/reports.php)

The navbar now includes:
1. **Employees** (`employees.php`) - To manage the team profiles and salaries.
2. **Attendance** (`attendance.php`) - To log daily presence and track absenteeism.
3. **Payroll** (`payroll.php`) - To generate monthly salary sheets, calculate net pay, and view/print payslips.

---

### 3. New Features and Pages

#### [employees.php](file:///c:/Users/derri/Downloads/Recruitment_SISDM/employees.php)
- **Employee List**: Table of all employees with their department, position, basic salary, join date, and status (Active/Resigned).
- **CRUD Operations**:
  - Add Employee manually or auto-import hired applicants.
  - Edit basic details and salary configuration.
  - Set status to Resigned/Active.
- **Auto-Import Button**: A button to instantly import applicants who are marked as "Hired" but are not yet in the employee database.

#### [attendance.php](file:///c:/Users/derri/Downloads/Recruitment_SISDM/attendance.php)
- **Daily Logger**: Ability to log or edit attendance for all employees for a selected date.
- **Attendance Statuses**: Present (Hadir), Absent (Alfa), Sick (Sakit), Leave (Cuti), Permit (Izin).
- **Bulk Action**: A button to automatically pre-fill today's attendance as "Present" for all active employees, allowing the HR admin to only adjust the exceptions (e.g. sick or absent employees).
- **Attendance History**: View past attendance records filtered by date.

#### [payroll.php](file:///c:/Users/derri/Downloads/Recruitment_SISDM/payroll.php)
- **Payroll Period Selection**: Select month and year.
- **Generate Payroll**: Calculate payroll for all active employees for that period.
  - **Auto-Deductions**: Automatically deduct a set amount (Rp 150.000) for every "Absent" (Alfa) day recorded in the attendance table for that month!
  - **Allowances**: Field to add custom allowances (tunjangan) for the month.
- **Action Buttons**:
  - Mark as Paid (with payment date).
  - Print/View Payslip (clean, premium invoice-like design).
  - Delete payroll records.

#### [db_init.php](file:///c:/Users/derri/Downloads/Recruitment_SISDM/db_init.php)
- A helper script to detect missing tables and initialize the database with schema updates and dummy records.

---

## Verification Plan

### Automated Verification
PHP CLI syntax checks:
- Clean compilation for all modified and new files (`php -l`).

### Manual Verification
1. Visit `employees.php` to trigger the database migration. Verify that tables are created successfully and dummy employees are loaded.
2. Navigate to `applicants.php`, change an applicant's status to "Hired", and verify they can be imported as an employee.
3. Visit `attendance.php`, log presence for a few employees (e.g. mark one as Absent to test deductions).
4. Visit `payroll.php` for the current month:
   - Click "Generate Payroll".
   - Verify that the basic salary matches the employee's setup.
   - Verify that the deductions count the number of "Absent" days correctly.
   - Edit allowance/deductions and verify that "Net Salary" updates correctly.
   - Click "View Payslip" and verify that the print-friendly slip modal looks clean and professional.
   - Mark a payroll record as Paid and verify status changes.
5. Verify navbar links work correctly across all pages.
