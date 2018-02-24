<?php

use App\Events\eventTrigger; // Linked the event

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
    return view('welcome');
});

Route::get('/alertBox', function (){ // The page on which the event is shown
    return view('eventListener');
});

Route::get('/fireEvent', function (){ // Event trigger
    event(new eventTrigger); // Create new event. This can be done from any part of the code
});