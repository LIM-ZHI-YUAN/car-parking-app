<?php

use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Route::get('profile/downloadProfileImg', function () {
//     $user = User::query()->latest()->first();
//     $path = $user->profile_img;

//     // return Storage::disk('public')->download($path);
//     return response()->download(public_path('storage/' . $path));
// });
