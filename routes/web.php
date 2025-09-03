<?php

use App\Http\Controllers\Admin\ApiKeyController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

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
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
});

Route::get('/dashboard', [App\Http\Controllers\DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');
Route::get('/dashboard/api/data', [App\Http\Controllers\DashboardController::class, 'apiData'])->middleware(['auth', 'verified'])->name('dashboard.api.data');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // Rotas do painel administrativo
    Route::prefix('admin')->name('admin.')->group(function () {
        // Gerenciamento de chaves de API
        Route::resource('apikeys', ApiKeyController::class);
        
        // Rotas de planos, billing e assignments removidas - Sistema simplificado v2.4.0
        
        // Gerenciamento de Uso de Tokens
        Route::resource('token-usage', App\Http\Controllers\Admin\TokenUsageController::class);
        Route::get('/token-usage-stats', [App\Http\Controllers\Admin\TokenUsageController::class, 'stats'])->name('token-usage.stats');
        Route::get('/token-usage/reports/by-api-key', [App\Http\Controllers\Admin\TokenUsageController::class, 'reportByApiKey'])->name('token-usage.report-by-api-key');
        Route::get('/token-usage/reports/by-period', [App\Http\Controllers\Admin\TokenUsageController::class, 'reportByPeriod'])->name('token-usage.report-by-period');
        Route::get('/token-usage-alerts', [App\Http\Controllers\Admin\TokenUsageController::class, 'alerts'])->name('token-usage.alerts');
        
        // Teste de IA
        Route::get('/test-ai', [App\Http\Controllers\Admin\TestAIController::class, 'index'])->name('test-ai.index');
        Route::post('/test-ai/test', [App\Http\Controllers\Admin\TestAIController::class, 'test'])->name('test-ai.test');
        Route::get('/test-ai/status', [App\Http\Controllers\Admin\TestAIController::class, 'status'])->name('test-ai.status');

        // Rotinas - Monitoramento do Sistema
        Route::get('/routines', [App\Http\Controllers\Admin\RoutinesController::class, 'index'])->name('routines.index');
        Route::post('/routines/test-gpt', [App\Http\Controllers\Admin\RoutinesController::class, 'testGPT'])->name('routines.test-gpt');
        Route::post('/routines/test-intelligent-cache', [App\Http\Controllers\Admin\RoutinesController::class, 'testIntelligentCache'])->name('routines.test-intelligent-cache');
        Route::get('/routines/system-status', [App\Http\Controllers\Admin\RoutinesController::class, 'systemStatus'])->name('routines.system-status');
        Route::get('/routines/redis-status', [App\Http\Controllers\Admin\RoutinesController::class, 'redisStatus'])->name('routines.redis-status');
        Route::get('/routines/database-status', [App\Http\Controllers\Admin\RoutinesController::class, 'databaseStatus'])->name('routines.database-status');
    });
});

require __DIR__.'/auth.php';
