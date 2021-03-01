<?php

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
    if(Auth::check())
        return redirect('/home');
    else
        return redirect('/login');
});

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');
Route::get('/home/{id}', function($id) {
    return view('/home')
        ->with('id', $id);
})->middleware('auth');

Route::get('/user/{us}', 'UserController@user');
Route::get('/user', function() {
    if(Auth::check())
        return redirect('/home');
    else
        return redirect('/login');
});

Route::post('/start', 'ServerController@startAI')->middleware('auth');
Route::post('/stop', 'ServerController@stopAI')->middleware('auth');
Route::post('/update', 'ServerController@update')->middleware('auth');

Route::get('/archive', function() {
    return view('archive');
})->middleware('auth');
Route::post('/load', 'HistoryController@load')->middleware('auth');
Route::post('/delete', 'HistoryController@delete')->middleware('auth');
Route::post('/idCheck', 'HistoryController@idCheck')->middleware('auth');

Route::get('/temp', 'ServerController@temp')->middleware('auth');
