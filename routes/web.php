<?php

use App\Http\Controllers\OutOfDutyPermitController;
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
    return view('welcome');
});
Route::controller(OutOfDutyPermitController::class)->group(function () {
    Route::get('/outofduty/download', [OutOfDutyPermitController::class, 'download']);

});
// Route::post('/outofduty/download', [OutOfDutyPermitController::class, 'download']);