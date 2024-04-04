<?php

use App\Http\Controllers\SportController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('chart');
});
Route::get('/yearchart',function(){
    return view('yearvise');
});
Route::get('/monthyearchart',[SportController::class,   'monthyearview'])->name('monthyear');
Route::get('/monthchart',[SportController::class,'chartMonthView']);

Route::get('/getData', [SportController::class,'getTotlaPostBySportCategory'])->name('categoryVise');
Route::get('/getAuthor', [SportController::class,'getAuthor'])->name('getAuthor');

Route::get('/getyearvise',[SportController::class, 'chartYearVise'])->name('getyearvise');
Route::get('/getmonthvise',[SportController::class, 'chartMonthVise'])->name('getmonthvise');
Route::get('/getdatechart',[SportController::class, 'dateChart'])->name('getdatechart');
Route::get('/getspecificmonthvise',[SportController::class, 'chartSpecificMonthVise'])->name('getspecificmonthvise');
Route::get('/getmonthyeardata',[SportController::class, 'yearMonthData'])->name('getmonthyeardata');