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

// Default controllers

Route::get('/', function () {
    return view('welcome');
});

Route::get('/alertBox', function (){ // The page on which the event is shown
    return view('eventListener');
});

Route::get('/fireEvent', function (){ // Event trigger
    event(new eventTrigger); // Create new event. This can be done from any part of the code
});

// Custom controllers

// Api request to bitfinex
Route::get('/history/{param}', 'HistoryFinex@index')->name('history.get'); // Controller is called using the given name and passing {param} to it

// Load data from DB and return it to the chart
route::get('/loaddata', 'LoadDataFromDB@index')->name('loadJsonFromDB');

// Calculate price channel
route::get('/pricechannelcalc', 'indicatorPriceChannel@index');

// Place order and pass volume of the trade to it
route::get('/placeorder/{volume}/{direction}', 'PlaceOrder@index');