<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\AssetController;
use App\Http\Controllers\AssetRequestController;
use App\Http\Controllers\BudgetController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\RealizationController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\VendorController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\BmnCodeController;
use App\Http\Controllers\HandoverReportController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\ProcurementBatchController;
use Illuminate\Support\Facades\Route;

Route::get('/login', [LoginController::class, 'show'])->name('login')->middleware('guest');
Route::post('/login', [LoginController::class, 'authenticate'])->name('login.authenticate')->middleware('guest');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

// Halaman publik, tanpa login — dibuka dari hasil scan QR code DBR yang ditempel di ruangan.
Route::get('/ruangan/{location}', [LocationController::class, 'publicInfo'])->name('locations.public-info');

Route::middleware('auth')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('home');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/assets', [AssetController::class, 'index'])->name('assets.index');
    Route::get('/requests', [AssetRequestController::class, 'index'])->name('requests.index');
    Route::get('/requests/create', [AssetRequestController::class, 'create'])->name('requests.create');
    Route::post('/requests', [AssetRequestController::class, 'store'])->name('requests.store');

    Route::middleware('admin')->group(function () {
        Route::get('/assets/create', [AssetController::class, 'create'])->name('assets.create');
        Route::get('/assets/import', [AssetController::class, 'importForm'])->name('assets.import');
        Route::get('/assets/import/template', [AssetController::class, 'downloadTemplate'])->name('assets.import.template');
        Route::post('/assets/import', [AssetController::class, 'import'])->name('assets.import.process');
        Route::post('/assets', [AssetController::class, 'store'])->name('assets.store');
        Route::get('/assets/{asset}/edit', [AssetController::class, 'edit'])->name('assets.edit');
        Route::put('/assets/{asset}', [AssetController::class, 'update'])->name('assets.update');
        Route::delete('/assets/{asset}', [AssetController::class, 'destroy'])->name('assets.destroy');
        Route::delete('/assets-bulk-destroy', [AssetController::class, 'bulkDestroy'])->name('assets.bulk-destroy');

        Route::post('/requests/{assetRequest}/decide', [AssetRequestController::class, 'decide'])->name('requests.decide');

        Route::get('/budgets', [BudgetController::class, 'index'])->name('budgets.index');
        Route::get('/budgets/{unit}', [BudgetController::class, 'show'])->name('budgets.show');
        Route::post('/budgets/{unit}', [BudgetController::class, 'store'])->name('budgets.store');

        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::post('/users/{user}/toggle-active', [UserController::class, 'toggleActive'])->name('users.toggle-active');

        Route::get('/units', [UnitController::class, 'index'])->name('units.index');
        Route::get('/units/create', [UnitController::class, 'create'])->name('units.create');
        Route::post('/units', [UnitController::class, 'store'])->name('units.store');
        Route::get('/units/{unit}/edit', [UnitController::class, 'edit'])->name('units.edit');
        Route::put('/units/{unit}', [UnitController::class, 'update'])->name('units.update');
        Route::post('/units/{unit}/toggle-active', [UnitController::class, 'toggleActive'])->name('units.toggle-active');
        Route::delete('/units/{unit}', [UnitController::class, 'destroy'])->name('units.destroy');

        Route::get('/realizations', [RealizationController::class, 'index'])->name('realizations.index');
        Route::get('/realizations/create', [RealizationController::class, 'create'])->name('realizations.create');
        Route::post('/realizations', [RealizationController::class, 'store'])->name('realizations.store');
        Route::get('/realizations/{realization}/edit', [RealizationController::class, 'edit'])->name('realizations.edit');
        Route::put('/realizations/{realization}', [RealizationController::class, 'update'])->name('realizations.update');
        Route::delete('/realizations/{realization}', [RealizationController::class, 'destroy'])->name('realizations.destroy');
        Route::get('/realizations/{realization}/finalize', [RealizationController::class, 'finalizeForm'])->name('realizations.finalize-form');
        Route::post('/realizations/{realization}/finalize', [RealizationController::class, 'finalize'])->name('realizations.finalize');

        Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
        Route::get('/categories/create', [CategoryController::class, 'create'])->name('categories.create');
        Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
        Route::get('/categories/{category}/edit', [CategoryController::class, 'edit'])->name('categories.edit');
        Route::put('/categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
        Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');

        Route::get('/vendors', [VendorController::class, 'index'])->name('vendors.index');
        Route::get('/vendors/create', [VendorController::class, 'create'])->name('vendors.create');
        Route::post('/vendors', [VendorController::class, 'store'])->name('vendors.store');
        Route::get('/vendors/{vendor}/edit', [VendorController::class, 'edit'])->name('vendors.edit');
        Route::put('/vendors/{vendor}', [VendorController::class, 'update'])->name('vendors.update');
        Route::post('/vendors/{vendor}/toggle-active', [VendorController::class, 'toggleActive'])->name('vendors.toggle-active');
        Route::delete('/vendors/{vendor}', [VendorController::class, 'destroy'])->name('vendors.destroy');

        Route::get('/locations', [LocationController::class, 'index'])->name('locations.index');
        Route::get('/locations/create', [LocationController::class, 'create'])->name('locations.create');
        Route::post('/locations', [LocationController::class, 'store'])->name('locations.store');
        Route::get('/locations/{location}/dbr', [LocationController::class, 'dbr'])->name('locations.dbr');
        Route::get('/locations/{location}/edit', [LocationController::class, 'edit'])->name('locations.edit');
        Route::get('/locations/{location}', [LocationController::class, 'show'])->name('locations.show');
        Route::put('/locations/{location}', [LocationController::class, 'update'])->name('locations.update');
        Route::delete('/locations/{location}', [LocationController::class, 'destroy'])->name('locations.destroy');

        Route::get('/bmn-codes', [BmnCodeController::class, 'index'])->name('bmn-codes.index');
        Route::get('/bmn-codes/create', [BmnCodeController::class, 'create'])->name('bmn-codes.create');
        Route::post('/bmn-codes', [BmnCodeController::class, 'store'])->name('bmn-codes.store');
        Route::get('/bmn-codes/import', [BmnCodeController::class, 'importForm'])->name('bmn-codes.import');
        Route::get('/bmn-codes/import/template', [BmnCodeController::class, 'downloadTemplate'])->name('bmn-codes.import.template');
        Route::post('/bmn-codes/import', [BmnCodeController::class, 'import'])->name('bmn-codes.import.process');
        Route::get('/bmn-codes/{bmnCode}/edit', [BmnCodeController::class, 'edit'])->name('bmn-codes.edit');
        Route::put('/bmn-codes/{bmnCode}', [BmnCodeController::class, 'update'])->name('bmn-codes.update');
        Route::delete('/bmn-codes/{bmnCode}', [BmnCodeController::class, 'destroy'])->name('bmn-codes.destroy');

        Route::get('/handover-reports', [HandoverReportController::class, 'index'])->name('handover-reports.index');
        Route::get('/handover-reports/create/{unit}', [HandoverReportController::class, 'create'])->name('handover-reports.create');
        Route::post('/handover-reports/{unit}', [HandoverReportController::class, 'store'])->name('handover-reports.store');
        Route::get('/handover-reports/{handoverReport}', [HandoverReportController::class, 'show'])->name('handover-reports.show');
        Route::get('/handover-reports/{handoverReport}/edit', [HandoverReportController::class, 'edit'])->name('handover-reports.edit');
        Route::put('/handover-reports/{handoverReport}', [HandoverReportController::class, 'update'])->name('handover-reports.update');
        Route::get('/handover-reports/{handoverReport}/print', [HandoverReportController::class, 'print'])->name('handover-reports.print');
        Route::post('/handover-reports/{handoverReport}/upload-scan', [HandoverReportController::class, 'uploadScan'])->name('handover-reports.upload-scan');

        Route::get('/settings', [SettingController::class, 'edit'])->name('settings.edit');
        Route::put('/settings', [SettingController::class, 'update'])->name('settings.update');

        Route::get('/procurement-batches', [ProcurementBatchController::class, 'index'])->name('procurement-batches.index');
        Route::get('/procurement-batches/create', [ProcurementBatchController::class, 'create'])->name('procurement-batches.create');
        Route::post('/procurement-batches', [ProcurementBatchController::class, 'store'])->name('procurement-batches.store');
        Route::get('/procurement-batches/{procurementBatch}', [ProcurementBatchController::class, 'show'])->name('procurement-batches.show');
        Route::post('/procurement-batches/{procurementBatch}/attach', [ProcurementBatchController::class, 'attachRealizations'])->name('procurement-batches.attach');
        Route::get('/procurement-batches/{procurementBatch}/edit', [ProcurementBatchController::class, 'edit'])->name('procurement-batches.edit');
        Route::put('/procurement-batches/{procurementBatch}', [ProcurementBatchController::class, 'update'])->name('procurement-batches.update');
    });
});
