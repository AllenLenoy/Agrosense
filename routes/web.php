<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SensorController;
use App\Http\Controllers\IrrigationController;
use App\Http\Controllers\FarmController;
use App\Http\Controllers\AlertController;
use App\Http\Controllers\WeatherController;
use App\Http\Controllers\DiseaseController;
use App\Http\Controllers\RecommendationController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Password Reset
Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])->name('password.email');
Route::get('/reset-password/{token}', [AuthController::class, 'showResetPassword'])->name('password.reset');
Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');

// Protected routes
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Sensors
    Route::get('/sensors', [SensorController::class, 'index'])->name('sensors.index');
    Route::get('/sensors/{sensor}', [SensorController::class, 'show'])->name('sensors.show');

    // Irrigation
    Route::get('/irrigation', [IrrigationController::class, 'index'])->name('irrigation.index');
    Route::post('/irrigation/start', [IrrigationController::class, 'start'])->name('irrigation.start');
    Route::post('/irrigation/{log}/toggle', [IrrigationController::class, 'toggle'])->name('irrigation.toggle');

    // Farms
    Route::get('/farms', [FarmController::class, 'index'])->name('farms.index');
    Route::get('/farms/{farm}', [FarmController::class, 'show'])->name('farms.show');
    Route::post('/farms', [FarmController::class, 'store'])->name('farms.store');
    Route::put('/farms/{farm}', [FarmController::class, 'update'])->name('farms.update');
    Route::delete('/farms/{farm}', [FarmController::class, 'destroy'])->name('farms.destroy');
    Route::post('/farms/{farm}/fields', [\App\Http\Controllers\FieldController::class, 'store'])->name('fields.store');
    Route::post('/farms/{farm}/sensors', [SensorController::class, 'store'])->name('sensors.store');

    // Alerts
    Route::get('/alerts', [AlertController::class, 'index'])->name('alerts.index');
    Route::post('/alerts/{alert}/read', [AlertController::class, 'markRead'])->name('alerts.read');
    Route::post('/alerts/{alert}/resolve', [AlertController::class, 'resolve'])->name('alerts.resolve');
    Route::post('/alerts/read-all', [AlertController::class, 'markAllRead'])->name('alerts.readAll');

    // Weather
    Route::get('/weather', [WeatherController::class, 'index'])->name('weather.index');

    // Disease detection
    Route::get('/diseases', [DiseaseController::class, 'index'])->name('diseases.index');
    Route::get('/diseases/upload', [DiseaseController::class, 'create'])->name('diseases.create');
    Route::post('/diseases', [DiseaseController::class, 'store'])->name('diseases.store');

    // AI Recommendations
    Route::get('/recommendations', [RecommendationController::class, 'index'])->name('recommendations.index');

    // Reports & Compliance
    Route::get('/reports', [\App\Http\Controllers\ReportController::class, 'index'])->name('reports.index');
    Route::post('/reports/export-csv', [\App\Http\Controllers\ReportController::class, 'exportCsv'])->name('reports.export-csv');
    Route::post('/reports/export-pdf', [\App\Http\Controllers\ReportController::class, 'exportPdf'])->name('reports.export-pdf');

    // Profile
    Route::get('/profile', [\App\Http\Controllers\ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [\App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [\App\Http\Controllers\ProfileController::class, 'updatePassword'])->name('profile.password');

    // Global Search
    Route::get('/search', [\App\Http\Controllers\SearchController::class, 'index'])->name('search');

});

// Admin routes (blade-based admin panel)
Route::middleware(['auth', \App\Http\Middleware\AdminMiddleware::class])->prefix('admin-panel')->group(function () {
    Route::get('/', [\App\Http\Controllers\AdminController::class, 'index'])->name('admin.index');
    Route::get('/users', [\App\Http\Controllers\AdminController::class, 'users'])->name('admin.users');
    Route::get('/devices', [\App\Http\Controllers\AdminController::class, 'devices'])->name('admin.devices');
    Route::post('/devices', [\App\Http\Controllers\AdminController::class, 'storeDevice'])->name('admin.devices.store');
    Route::put('/devices/{sensor}', [\App\Http\Controllers\AdminController::class, 'updateDevice'])->name('admin.devices.update');
    Route::delete('/devices/{sensor}', [\App\Http\Controllers\AdminController::class, 'deleteDevice'])->name('admin.devices.delete');
    Route::get('/alerts', [\App\Http\Controllers\AdminController::class, 'alerts'])->name('admin.alerts');
    Route::post('/users', [\App\Http\Controllers\AdminController::class, 'storeUser'])->name('admin.users.store');
    Route::put('/users/{user}', [\App\Http\Controllers\AdminController::class, 'updateUser'])->name('admin.users.update');
    Route::post('/users/{user}/toggle', [\App\Http\Controllers\AdminController::class, 'toggleUser'])->name('admin.users.toggle');
    Route::delete('/users/{user}', [\App\Http\Controllers\AdminController::class, 'deleteUser'])->name('admin.users.delete');
});
