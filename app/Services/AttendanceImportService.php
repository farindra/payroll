<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\Attendance;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class AttendanceImportService implements ToCollection
{
    protected $importResults = [];
    protected $totalRows = 0;
    protected $successCount = 0;
    protected $errorCount = 0;

    public function collection(Collection $rows)
    {
        $this->totalRows = $rows->count() - 1; // Subtract header row
        $headerRow = $rows->first();

        // Validate header
        if (!$this->validateHeader($headerRow)) {
            throw new \Exception('Invalid CSV format. Required columns: employee_id, date, status');
        }

        // Process data rows
        $dataRows = $rows->slice(1);
        foreach ($dataRows as $index => $row) {
            $rowNumber = $index + 2; // Add 2 for header and 1-based index
            $this->processRow($row, $rowNumber);
        }

        return $this->getResults();
    }

    protected function validateHeader($header)
    {
        $requiredColumns = ['employee_id', 'date', 'status'];
        $headerArray = $header->toArray();

        foreach ($requiredColumns as $column) {
            if (!in_array($column, $headerArray)) {
                return false;
            }
        }
        return true;
    }

    protected function processRow($row, $rowNumber)
    {
        try {
            $data = $this->mapRowToData($row);

            // Find employee by NIP (employee_id in CSV) and get the actual employee ID
            $employee = Employee::where('nip', $data['employee_id'])->first();
            if (!$employee) {
                $this->addError($rowNumber, "Employee with ID {$data['employee_id']} not found");
                return;
            }
            $data['employee_id'] = $employee->id;

            $validator = Validator::make($data, [
                'employee_id' => 'required|integer|exists:employees,id',
                'date' => 'required|date',
                'status' => 'required|in:Present,Sick,Permission,Leave,Absent',
                'clock_in' => 'nullable|date_format:H:i',
                'clock_out' => 'nullable|date_format:H:i',
                'total_hours' => 'nullable|numeric|min:0',
                'overtime_hours' => 'nullable|numeric|min:0',
                'note' => 'nullable|string|max:255',
            ]);

            if ($validator->fails()) {
                $this->addError($rowNumber, 'Validation failed: ' . $validator->errors()->first());
                return;
            }

            // Check for duplicate attendance
            $existingAttendance = Attendance::where('employee_id', $data['employee_id'])
                ->where('date', $data['date'])
                ->first();

            if ($existingAttendance) {
                // Update existing record
                $existingAttendance->update($data);
                $this->addSuccess($rowNumber, 'Attendance record updated successfully');
            } else {
                // Create new record
                Attendance::create($data);
                $this->addSuccess($rowNumber, 'Attendance record created successfully');
            }

            $this->successCount++;

        } catch (\Exception $e) {
            $this->addError($rowNumber, 'Error: ' . $e->getMessage());
            $this->errorCount++;
        }
    }

    protected function mapRowToData($row)
    {
        return [
            'employee_id' => $row[0] ?? null,
            'date' => $row[1] ?? null,
            'status' => $row[2] ?? null,
            'clock_in' => $this->formatTime($row[3] ?? null),
            'clock_out' => $this->formatTime($row[4] ?? null),
            'total_hours' => is_numeric($row[5] ?? null) ? (float)($row[5]) : 0,
            'overtime_hours' => is_numeric($row[6] ?? null) ? (float)($row[6]) : 0,
            'note' => $row[7] ?? null,
        ];
    }

    protected function formatTime($time)
    {
        if (empty($time)) return null;

        // Handle different time formats
        if (strpos($time, ':') !== false) {
            return date('H:i', strtotime($time));
        }

        return null;
    }

    protected function addSuccess($rowNumber, $message)
    {
        $this->importResults[] = [
            'row' => $rowNumber,
            'status' => 'success',
            'message' => $message,
        ];
    }

    protected function addError($rowNumber, $message)
    {
        $this->importResults[] = [
            'row' => $rowNumber,
            'status' => 'error',
            'message' => $message,
        ];
    }

    public function getResults()
    {
        return [
            'total_rows' => $this->totalRows,
            'success_count' => $this->successCount,
            'error_count' => $this->errorCount,
            'results' => $this->importResults,
        ];
    }

    public static function importFromCsv($filePath)
    {
        try {
            $import = new self();
            Excel::import($import, $filePath);
            return $import->getResults();
        } catch (\Exception $e) {
            Log::error('CSV import failed: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Import failed: ' . $e->getMessage(),
                'results' => []
            ];
        }
    }
}