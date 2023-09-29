<?php

use App\Livewire\SejourConfirmed;
use Illuminate\Support\Facades\Route;
use App\Livewire\VisitorForm;

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
})->name('home');

Route::get('/confirmation/{link_token}', VisitorForm::class)->name('confirmation');

Route::get('/confirmed/{link_token}', SejourConfirmed::class)->name('confirmed');

//Route::middleware('confirmationLinkIsValid')->get('/confirmation', [ ConfirmationController::class, 'showConfirmationForm'])->name('confirmation');
