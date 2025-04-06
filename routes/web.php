<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AuthController;
use App\Http\Middleware\RefreshPageMiddleware;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;

use Inertia\Inertia;

// Authentication Routes
Route::inertia('/', 'Auth/Login')->name('Login');
Route::middleware(['guest', RefreshPageMiddleware::class])->group(function() { 
    Route::inertia('/register', 'Auth/Register')->name('register');
    Route::post('/register', [AuthController::class, 'register']);
    Route::inertia('/login', 'Auth/Login')->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

// Profile Routes
Route::middleware(['auth', RefreshPageMiddleware::class])->group(function() {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/profile', [ProfileController::class, 'updateInfo'])->name('profile.info');
    Route::put('/profile', [ProfileController::class, 'updatePassword'])->name('profile.password');
});


// Inertia Routes
Route::middleware(['auth', RefreshPageMiddleware::class])->group(function() {
    Route::inertia('/home', 'Home')->name('home');
    Route::inertia('/dashboard', 'Dashboard')->name('dashboard');
   
    Route::inertia('/attendance', 'Attendance')->name('attendance');
    Route::post('/logout', [AuthController::class, 'logout']);
});

//Student Routes
Route::middleware(['auth', RefreshPageMiddleware::class])->group(function() {
    Route::inertia('/students', 'Students')->name('students');
    Route::get('/students-data', [StudentController::class, 'retrieve'])->name('students-data');
    Route::post('/students', [StudentController::class, 'store'])->name('students');
    Route::delete('/students/{id}', [StudentController::class, 'destroy'])->name('students-delete');
    Route::put('/students/{id}', [StudentController::class, 'update'])->name('students-update');
    Route::post('/students/check-name', [StudentController::class, 'checkName'])->name('students.checkName');
    Route::get('/students/count', [StudentController::class, 'countStudents'])->name('students.count');
});

//Scanned Codes Routes
Route::middleware(['auth', RefreshPageMiddleware::class])->group(function() {
    Route::post('/attendance/scan', [AttendanceController::class, 'scan']);
    Route::get('/attendance/scanned-codes-data', [AttendanceController::class, 'getAllScannedCodes'])->name('attendance.scanned-codes-data');
    Route::get('/attendance/total-visitors-today', [AttendanceController::class, 'countVisitorsToday'])->name('attendance.total-visitors-today');
    Route::get('/attendance/total-visitors', [AttendanceController::class, 'getTotalVisitors'])->name('attendance.total-visitors');
    Route::get('/attendance/currently-inside', [AttendanceController::class, 'countStudentsWithoutTimeOut'])->name('attendance.currently-inside');
    Route::get('/attendance/weekly-attendance', [AttendanceController::class, 'getWeeklyAttendance'])->name('attendance.students-by-level');

});
// Admin Approval Routes
Route::middleware(['auth', RefreshPageMiddleware::class])->group(function () {
    //Confirm users display
    Route::inertia('confirm-users', 'UserConfirmation/View')->name('confirm-users');
    
    // User Management
    Route::inertia('user-management', 'UserManagement')->name('user-management');

    Route::get('/admin/pending-users', [AuthController::class, 'getPendingUsers'])->name('admin.pendingUsers');
    Route::post('/admin/approve/{id}', [AuthController::class, 'approveUser'])->name('admin.approveUser');
    Route::post('/admin/reject/{id}', [AuthController::class, 'rejectUser'])->name('admin.rejectUser');

    Route::put('/admin/users/{id}', [UserController::class, 'update'])->name('admin.updateUser');
    Route::delete('/delete-users/{id}', [UserController::class, 'destroy'])->name('admin.deleteUser');
    Route::post('/admin/users/{id}/reset-password', [UserController::class, 'resetPassword'])->name('admin.resetPassword');
    Route::inertia('/list-of-users', 'Users/List')->name('list-of-users');
    Route::put('/users/{id}/toggle-activation', [UserController::class, 'toggleActivation'])->name('users.toggleActivation');
    Route::get('/users-data', [UserController::class, 'getUsers'])->name('admin.get-users');
});







