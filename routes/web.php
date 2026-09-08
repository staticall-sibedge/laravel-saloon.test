<?php declare(strict_types=1);

use Illuminate\Support\Facades\Route;


Route::prefix('api')->group(static function() {
    require base_path('routes/web-api.php');
});

Route::get('/', function () {
    return view('welcome');
});
