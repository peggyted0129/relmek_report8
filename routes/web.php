<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\CustomerController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return redirect()->route('cdrbc0');
});

// ** [ 業績 ]
Route::get('cdrbc0', [ReportController::class, 'cdrbc0'])->name('cdrbc0');
Route::get('cdrbc0/{yymm}', [ReportController::class, 'cdrbc0']);
// 印出 "上個月" 業績的 Excel 表
Route::get('cdrbc0_excel', [ReportController::class, 'cdrbc0_excel']);

// ** [ 單品 ]
Route::get('cdrbb0', [ReportController::class, 'cdrbb0']);
Route::get('cdrbb0/{yymm}', [ReportController::class, 'cdrbb0']);
// 業務單品明細排行 API | GET
Route::get('cdrbb0/{yymm}/{itnbr}', [ReportController::class, 'cdrb96']);

// ** [ 訂單 ]
Route::get('cdrba0', [ReportController::class, 'cdrba0']);
Route::post('cdrba0', [ReportController::class, 'cdrba0'])->name('cdrba0');
// 訂單明細 API | GET
Route::get('cdrba0/{shpno}', [ReportController::class, 'cdrdta']);

// ** [ 展示 ]
Route::get('cdrbd0', [ReportController::class, 'cdrbd0']);
Route::get('cdrbd0/{yymm}', [ReportController::class, 'cdrbd0']);
Route::get('cdrbd0_cdrhad/{yymm}/{mandsc}', [ReportController::class, 'cdrbd0_cdrhad']);
Route::get('cdrbd0_eip/{yymm}/{mandsc}', [ReportController::class, 'cdrbd0_eip']);

// ** [ 業務新客戶統計 ]
Route::get('cdrbe0', [ReportController::class, 'cdrbe0']);
Route::get('cdrbe0/{yymm}', [ReportController::class, 'cdrbe0']);

// ** [ OTC 連鎖客戶業績統計 ]
Route::get('cdrca0', [ReportController::class, 'cdrca0']);
Route::get('cdrca0/{yymm}', [ReportController::class, 'cdrca0']);

// ** [ 客戶出貨查詢 ] : 可查詢一個品號
Route::get('customer/itnbr', [CustomerController::class, 'itnbr']);
Route::post('customer/itnbr', [CustomerController::class, 'itnbr'])->name('customer.itnbr');

// ** [ 客戶出貨查詢2 ] : 可查詢二個品號
Route::get('customer/itnbr2', [CustomerController::class, 'itnbr2']);
Route::post('customer/itnbr2', [CustomerController::class, 'itnbr2'])->name('customer.itnbr2');

// ** [ 媽媽寶寶俱樂部 ]
Route::get('customer/itnbr3', [CustomerController::class, 'itnbr3']);
Route::post('customer/itnbr3', [CustomerController::class, 'itnbr3'])->name('customer.itnbr3');