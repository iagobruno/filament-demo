<?php

use App\Models\Project;
use App\Models\Release;
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

Route::domain('{project}.example.com')->group(function () {
    Route::get('/', function (Project $project) {
        return $project;
    })->name('blog-homepage');

    Route::get('/release/{release}', function (Release $release) {
        return $release;
    })->name('blog-release');
});
