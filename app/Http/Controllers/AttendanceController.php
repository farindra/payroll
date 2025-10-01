<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function testPrint()
    {
        try {
            // Create sample data for testing
            $reportData = [
                'period' => [
                    'start_date' => '2025-07-01',
                    'end_date' => '2025-07-31'
                ],
                'summary' => [
                    'present' => 17,
                    'late' => 0,
                    'absent' => 1,
                    'leave' => 5
                ],
                'attendances' => [
                    [
                        'date' => '2025-07-01',
                        'check_in' => '08:00',
                        'check_out' => '17:00',
                        'status' => 'present',
                        'notes' => 'Normal'
                    ],
                    [
                        'date' => '2025-07-02',
                        'check_in' => '08:30',
                        'check_out' => '17:30',
                        'status' => 'late',
                        'notes' => 'Terlambat 30 menit'
                    ]
                ]
            ];

            return view('reports.attendance-print', [
                'reportData' => $reportData,
                'employeeName' => 'Test Employee',
                'generationDate' => now(),
            ]);
        } catch (\Exception $e) {
            return response('Error: ' . $e->getMessage(), 500, [
                'Content-Type' => 'text/plain; charset=utf-8'
            ]);
        }
    }

    public function printAttendanceReport($token)
    {
        try {
            // Get session key
            $sessionKey = 'attendance_print_token_' . $token;

            // Get data from session
            $sessionData = session($sessionKey);

            if (!$sessionData) {
                return response('Link tidak valid atau sudah kedaluwarsa. Silakan generate laporan kembali.', 404, [
                    'Content-Type' => 'text/plain; charset=utf-8'
                ]);
            }

            // Check if expired
            if (isset($sessionData['expires_at']) && now()->isAfter($sessionData['expires_at'])) {
                // Clean up expired session
                session()->forget($sessionKey);
                return response('Link sudah kedaluwarsa. Silakan generate laporan kembali.', 404, [
                    'Content-Type' => 'text/plain; charset=utf-8'
                ]);
            }

            // Get data from session
            $reportData = $sessionData['reportData'];
            $employeeName = $sessionData['employeeName'];
            $generationDate = $sessionData['generationDate'];

            // Clean up session after successful access (optional - comment out if you want to keep it)
            // session()->forget($sessionKey);

            return view('reports.attendance-print', [
                'reportData' => $reportData,
                'employeeName' => $employeeName,
                'generationDate' => $generationDate,
            ]);
        } catch (\Exception $e) {
            return response('Error: ' . $e->getMessage(), 500, [
                'Content-Type' => 'text/plain; charset=utf-8'
            ]);
        }
    }

    public function printAttendanceReportSimple(Request $request)
    {
        try {
            // Get encoded data from URL
            $encodedData = $request->query('data');
            $employeeName = $request->query('employeeName', 'Employee');

            // Debug: Log the request
            \Log::info('Print request received', [
                'has_data' => !empty($encodedData),
                'data_length' => $encodedData ? strlen($encodedData) : 0,
                'employee_name' => $employeeName,
                'full_url' => $request->fullUrl()
            ]);

            if (!$encodedData) {
                return response('Data tidak ditemukan. Silakan generate laporan kembali.', 404, [
                    'Content-Type' => 'text/plain; charset=utf-8'
                ]);
            }

            // Decode the data
            $decodedData = base64_decode($encodedData);
            if (!$decodedData) {
                \Log::error('Base64 decode failed');
                return response('Data tidak valid. Silakan generate laporan kembali.', 404, [
                    'Content-Type' => 'text/plain; charset=utf-8'
                ]);
            }

            $reportData = json_decode($decodedData, true);
            if (!$reportData) {
                \Log::error('JSON decode failed: ' . json_last_error_msg());
                return response('Format data tidak valid. Silakan generate laporan kembali.', 404, [
                    'Content-Type' => 'text/plain; charset=utf-8'
                ]);
            }

            \Log::info('Successfully decoded print data', [
                'report_data_keys' => array_keys($reportData),
                'has_attendances' => isset($reportData['attendances']),
                'attendances_count' => isset($reportData['attendances']) ? count($reportData['attendances']) : 0
            ]);

            return view('reports.attendance-print', [
                'reportData' => $reportData,
                'employeeName' => $employeeName,
                'generationDate' => now(),
            ]);
        } catch (\Exception $e) {
            \Log::error('Print controller error: ' . $e->getMessage());
            return response('Error: ' . $e->getMessage(), 500, [
                'Content-Type' => 'text/plain; charset=utf-8'
            ]);
        }
    }

    public function printAttendanceReportSecure(Request $request, $token)
    {
        try {
            // Handle POST request (direct form submission)
            if ($request->isMethod('post')) {
                $encodedData = $request->input('report_data');
                $employeeName = $request->input('employee_name', 'Employee');

                if (!$encodedData) {
                    return response('Data tidak ditemukan. Silakan generate laporan kembali.', 404, [
                        'Content-Type' => 'text/plain; charset=utf-8'
                    ]);
                }

                // Decode the data
                $decodedData = base64_decode($encodedData);
                if (!$decodedData) {
                    return response('Data tidak valid. Silakan generate laporan kembali.', 404, [
                        'Content-Type' => 'text/plain; charset=utf-8'
                    ]);
                }

                $reportData = json_decode($decodedData, true);
                if (!$reportData) {
                    return response('Format data tidak valid. Silakan generate laporan kembali.', 404, [
                        'Content-Type' => 'text/plain; charset=utf-8'
                    ]);
                }

                return view('reports.attendance-print', [
                    'reportData' => $reportData,
                    'employeeName' => $employeeName,
                    'generationDate' => now(),
                ]);
            }

            // Handle GET request (session-based token)
            $sessionData = session($token);

            if (!$sessionData) {
                return response('Link tidak valid atau sudah kedaluwarsa. Silakan generate laporan kembali.', 404, [
                    'Content-Type' => 'text/plain; charset=utf-8'
                ]);
            }

            // Check if expired
            if (isset($sessionData['expires_at']) && now()->isAfter($sessionData['expires_at'])) {
                // Clean up expired session
                session()->forget($token);
                return response('Link sudah kedaluwarsa. Silakan generate laporan kembali.', 404, [
                    'Content-Type' => 'text/plain; charset=utf-8'
                ]);
            }

            // Get data from session
            $reportData = $sessionData['reportData'];
            $employeeName = $sessionData['employeeName'];
            $generationDate = now();

            // Clean up session after successful access (optional - comment out if you want to keep it)
            // session()->forget($token);

            return view('reports.attendance-print', [
                'reportData' => $reportData,
                'employeeName' => $employeeName,
                'generationDate' => $generationDate,
            ]);
        } catch (\Exception $e) {
            return response('Error: ' . $e->getMessage(), 500, [
                'Content-Type' => 'text/plain; charset=utf-8'
            ]);
        }
    }
}