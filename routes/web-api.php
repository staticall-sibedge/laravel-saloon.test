<?php declare(strict_types=1);

use App\Http\Controllers\ExchangeRateController;
use Illuminate\Support\Facades\Route;

Route::prefix('exchange-rate')->group(static function() {
    Route::get('', [ExchangeRateController::class, 'showAction']);
    Route::post('', [ExchangeRateController::class, 'parseAllAction']);
    Route::post('cbr', [ExchangeRateController::class, 'cbrAction']);
    Route::post('nbrb', [ExchangeRateController::class, 'nbrbAction']);
});
