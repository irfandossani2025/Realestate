<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\PropertyController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\ValuationRequestController;
use Illuminate\Support\Facades\Route;

Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login.store');
Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

Route::middleware('auth')->get('/', function () {
    return view('dashboard');
})->name('dashboard');

Route::middleware('auth')->group(function (): void {
    Route::get('/leads', [LeadController::class, 'index'])->name('leads.index');
    Route::post('/leads', [LeadController::class, 'store'])->name('leads.store');
    Route::get('/properties', [PropertyController::class, 'index'])->name('properties.index');
    Route::post('/properties', [PropertyController::class, 'store'])->name('properties.store');
    Route::patch('/properties/{property}/publication', [PropertyController::class, 'updatePublication'])->name('properties.publication.update');
    Route::get('/valuations', [ValuationRequestController::class, 'index'])->name('valuations.index');
    Route::post('/valuations', [ValuationRequestController::class, 'store'])->name('valuations.store');
    Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions.index');
    Route::post('/transactions', [TransactionController::class, 'store'])->name('transactions.store');
    Route::post('/transactions/{transaction}/payments', [TransactionController::class, 'storePayment'])->name('transactions.payments.store');
    Route::patch('/payments/{payment}/approve', [TransactionController::class, 'approvePayment'])->name('payments.approve');
});
