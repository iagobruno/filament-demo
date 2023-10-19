<?php

use App\Models\Blog;
use App\Models\Post;
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

Route::domain('{blog}.example.com')->group(function () {
    Route::get('/', function (Blog $blog) {
        return $blog;
    })->name('blog-homepage');

    Route::get('/post/{post}', function (Post $post) {
        return $post;
    })->name('blog-post');
});
