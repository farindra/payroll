<?php

use App\Mcp\Servers\LaravelFilamentMcpServer;
use Illuminate\Support\Facades\Route;
use Laravel\Mcp\Facades\Mcp;
use App\Http\Controllers\PayslipController;
use App\Http\Controllers\AttendanceController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

// Payslip sharing routes
Route::get('/payslip/share/{id}', [PayslipController::class, 'sharePayslip'])
    ->name('payslip.share')
    ->middleware('signed');

// Secure sharing routes (hash-based)
Route::get('/payslip/share/secure/{hash}', [PayslipController::class, 'sharePayslipSecure'])
    ->name('payslip.share.secure');

// Secure sharing routes (encrypted token)
Route::get('/payslip/share/encrypted/{token}', [PayslipController::class, 'sharePayslipEncrypted'])
    ->name('payslip.share.encrypted');

// Secure sharing routes (signed URL with hash)
Route::get('/payslip/share/signed/{hash}', [PayslipController::class, 'sharePayslipSecure'])
    ->name('payslip.share.signed')
    ->middleware('signed');

// Direct PDF download route (bypasses Livewire)
Route::get('/payslip/download/{periodId}', [PayslipController::class, 'downloadPayslipDirect'])
    ->name('payslip.download.direct')
    ->middleware('web', 'auth');

// Payslip print view route (secure hash-based)
Route::get('/payslip/print/secure/{hash}', [PayslipController::class, 'printPayslipSecure'])
    ->name('payslip.print.secure');

// Legacy print route (keep for backward compatibility but redirect)
Route::get('/payslip/print/{periodId}', [PayslipController::class, 'printPayslip'])
    ->name('payslip.print')
    ->middleware('web', 'auth');

// Attendance report print route
Route::get('/attendance/print/{token}', [AttendanceController::class, 'printAttendanceReport'])
    ->name('attendance.print')
    ->middleware('web', 'auth');

// Test direct route
Route::get('/attendance/print/direct', [AttendanceController::class, 'testPrint'])
    ->name('attendance.print.test')
    ->middleware('web', 'auth');

// Simple print route with encoded data
Route::get('/attendance/print/simple', [AttendanceController::class, 'printAttendanceReportSimple'])
    ->name('attendance.print.simple')
    ->middleware('web', 'auth');

// Secure print route with session token
Route::match(['get', 'post'], '/attendance/print/secure/{token}', [AttendanceController::class, 'printAttendanceReportSecure'])
    ->name('attendance.print.secure')
    ->middleware('web', 'auth');

Mcp::local('laravelFilamentMcp', LaravelFilamentMcpServer::class);
