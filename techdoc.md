# 📝 Technical Document: Payroll System Application

**Version:** 1.0
**Date:** September 27, 2025
**Technology Stack:** Laravel 10, Filament 3, MySQL 8

## 1. Project Goal and Scope

This document details the technical specifications and architecture for the Payroll System application. The main goal is to automate employee salary calculations based on external attendance data (CSV), while accurately accounting for all salary components, deductions, and mandatory obligations such as tax (PPh 21) and insurance (BPJS) in compliance with Indonesian regulations.

## 2. Technology Stack

| Component         | Technology/Version                     | Description                                                    |
| :---------------- | :------------------------------------- | :------------------------------------------------------------- |
| **Framework**     | Laravel 10/11                          | Primary back-end foundation.                                   |
| **Admin Panel**   | **Filament 3**                         | Used for Admin/Manager interface (CRUD, Forms, Tables, Pages). |
| **Database**      | **MySQL**                              | Storage for relational and transactional data.                 |
| **Data Import**   | Laravel Excel / Custom Job             | Processes attendance CSV files.                                |
| **Authorization** | Laravel Gates/Policies, Filament Roles | Manages access rights.                                         |

---

## 3. Data Architecture (MySQL Schema)

The following are the key tables to be used. All tables must include standard Laravel columns (`id`, `created_at`, `updated_at`).

### A. Core User & Authorization Tables

| Table         | Key Relationships        | Description                                                                 |
| :------------ | :----------------------- | :-------------------------------------------------------------------------- |
| `users`       | `belongsTo -> employees` | System users who can log in. Must be linked to employee data.               |
| `roles`       |                          | Used for Filament/Laravel authorization. Roles: **`admin`**, **`manager`**. |
| `permissions` |                          | Used for granular access control.                                           |

### B. Employee and Payroll Master Data Tables

| Table                     | Key Columns (in addition to standard)                                                                                                                                                         | Key Relationships                                           |
| :------------------------ | :-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | :---------------------------------------------------------- |
| **`employees`**           | `nip`, `full_name`, `position`, `department_id (FK)`, **`basic_salary`** (decimal), `bank_account_no`, `npwp`, `ptkp_status` (enum: K/0, TK/0, K/1, etc.), `bpjs_kesehatan_no`, `bpjs_tk_no`. | `hasMany -> attendances`, `hasMany -> payroll_details`.     |
| **`salary_components`**   | `name`, **`type`** (enum: 'allowance', 'deduction', 'tax', 'insurance'), `is_fixed` (boolean), `formula_code` (optional, for custom formulas).                                                |                                                             |
| **`employee_components`** | `employee_id (FK)`, `component_id (FK)`, `amount` (decimal), `is_percentage` (boolean).                                                                                                       | `belongsTo -> employees`, `belongsTo -> salary_components`. |
| `deductions`              | `employee_id (FK)`, `type` (e.g., 'loan'), `amount`, `start_date`, `end_date`.                                                                                                                | Non-regular deductions (loans, penalties).                  |

### C. Transactional Data Tables

| Table                 | Key Columns (in addition to standard)                                                                                                                                                                                   | Key Relationships             |
| :-------------------- | :---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | :---------------------------- |
| **`payroll_periods`** | `period_name` (e.g., July 2025), `start_date`, `end_date`, **`status`** (enum: 'Draft', 'Calculated', 'Paid').                                                                                                          | `hasMany -> payroll_details`. |
| **`attendances`**     | `employee_id (FK)`, `date`, **`status`** (enum: 'Present', 'Sick', 'Permission', 'Leave', 'Absent'), `hours_worked` (decimal), `overtime_hours` (decimal), `note`.                                                      | **Input from CSV import.**    |
| **`payroll_details`** | `payroll_period_id (FK)`, `employee_id (FK)`, **`gross_salary`**, **`total_allowances`**, **`total_deductions`**, **`pph_21`**, **`bpjs_kesehatan_emp`**, **`bpjs_tk_emp`**, **`net_salary`**, `payslip_path` (string). | Final calculation results.    |

---

## 4. Key Functional Modules (Filament Resources & Custom Pages)

### 4.1. Attendance Data Import Module (Custom Page)

-   **Access:** Admin.
-   **Task:** Allows Admin to upload the external **CSV** attendance data file.
-   **Technical Flow:**
    1.  User uploads the CSV file.
    2.  A **Laravel Job** (e.g., `ProcessAttendanceImport`) is used to process the file in the **background** (to prevent timeouts).
    3.  The Job validates: file format, column headers, and the existence of `employee_id` in the `employees` table.
    4.  Attendance data (Present, Leave, Permission, Sick, Absent) is parsed and stored in the **`attendances`** table.
    5.  Success/failure report is displayed on the Filament UI.

### 4.2. Payroll Calculation Module (Custom Page/Action)

-   **Access:** Admin.
-   **Core Logic:** A Service Class, **`PayrollCalculationService`**, must handle all calculation complexities.
-   **Computation Steps:**

| Step                                              | Technical Description                                                                                                                                                                                                     |
| :------------------------------------------------ | :------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| **Gross Salary Calculation**                      | Calculate Basic Salary (pro-rated based on working days/attendance), Fixed Allowances, Non-Fixed Allowances (e.g., overtime from `attendances`), and Ad-hoc Allowances.                                                   |
| **BPJS Ketenagakerjaan (Employment) Calculation** | Calculate JHT (2% Employee, 3.7% Employer), JP (1% Employee, 2% Employer - _capped_), JKK, JKM. Store the Employee deduction component.                                                                                   |
| **BPJS Kesehatan (Health) Calculation**           | Calculate Contribution (1% Employee, 4% Employer - _capped_). Store the Employee deduction component.                                                                                                                     |
| **PPh 21 (Income Tax) Calculation**               | Apply PPh 21 calculation method (Gross, Net, or Gross-Up, based on policy), considering Job Position Deduction (5%, max IDR 500k/month), PTKP status (`employees.ptkp_status`), and the prevailing progressive tax rates. |
| **Finalization**                                  | Store the final results (Gross, Total Deductions, PPh 21, Net Salary) in **`payroll_details`**. Change `payroll_periods` status to 'Calculated'.                                                                          |

### 4.3. Reporting Module (Custom Page/Resource)

-   **Payslip Report:** Generate individual PDF payslips from **`payroll_details`** data per period.
-   **Recap Report:** Export CSV/Excel summarizing net salary, total deductions, and PPh 21 for all employees per period.
-   **Compliance Reports:** Separate reports for PPh 21 (e-SPT ready format) and BPJS Contributions (Employee & Employer contributions).

### 4.4. Access Rights (Authorization)

| User Role   | Resource/Page Access                                                                                                                                                             | Description                                    |
| :---------- | :------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | :--------------------------------------------- |
| **Admin**   | All (CRUD `employees`, `salary_components`, `payroll_periods`), **`Import Attendance`**, **`Process Payroll`**, All Reports.                                                     | Full responsibility for data and calculations. |
| **Manager** | Read-Only for `employees`, Read-Only for `payroll_details`, Access to **Payroll Reports** (only for managed departments), **Leave/Permission Approval** (if ESS is implemented). | Focuses on monitoring and authorization.       |

---

## 5. Indonesian Regulatory Compliance Aspects

The developer **MUST** pay attention to and implement the following regulations:

1.  **Basic Salary & Fixed Allowance:** Basic Salary minimum $\ge 75\%$ of the Basic Salary plus Fixed Allowance.
2.  **Overtime:** Calculation must adhere to Government Regulation No. 35 Year 2021 (especially for working days and holidays).
3.  **BPJS Ketenagakerjaan (4 Programs):** JKK, JKM, JHT (2% Employee, 3.7% Employer), JP (1% Employee, 2% Employer - _capped_).
4.  **BPJS Kesehatan (Health):** Employee 1% (max salary IDR 12 Million), Employer 4%.
5.  **PPh 21 (Income Tax):**
    -   Uses the progressive tax rates (5% to 35%).
    -   Implements Job Position Deduction (_Biaya Jabatan_) (5% of Gross, max IDR 500,000/month).
    -   The accuracy of the **PTKP** status (`employees.ptkp_status`) is crucial.

## 6. Filament Implementation and Development

-   Use **Filament Resources** for simple CRUD operations (`EmployeeResource`, `SalaryComponentResource`).
-   Use **Filament Custom Pages** for complex workflows (CSV Import, Payroll Calculation).
-   Utilize **Filament Actions** and **Bulk Actions** to trigger processes such as `Calculate Payroll` or `Generate Payslips`.

---

# 🛠️ Developer Checklist

| Item                                  | Status  | Notes                                               |
| :------------------------------------ | :------ | :-------------------------------------------------- |
| Create MySQL DB Schema (Migrations)   | **[ ]** | Ensure correct FK relationships.                    |
| Configure Filament & Roles            | **[ ]** | Set up `admin` and `manager` roles.                 |
| Implement `ImportAttendancePage`      | **[ ]** | Must utilize a Laravel Job/Queue.                   |
| Implement `PayrollCalculationService` | **[ ]** | Unit testing for PPh 21 and BPJS logic is required. |
| Create `PayrollPeriodsResource`       | **[ ]** | Main resource for managing payroll periods.         |
| Create PDF Payslip Generator          | **[ ]** | Use DomPDF/Snappy for PDF output.                   |
| Apply Laravel Policies/Gates          | **[ ]** | Restrict `manager` to read-only access and reports. |
