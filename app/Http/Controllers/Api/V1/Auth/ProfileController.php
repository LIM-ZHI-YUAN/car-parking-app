<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

/**
 * @group Auth
 */
class ProfileController extends Controller
{
    public function show(Request $request)
    {
        $url = Storage::url($request->user()->profile_img) ?? '';
        
        return response()->json([
            'name' => $request->user()->name,
            'email' => $request->user()->email,
            'url' => $url,
        ]);
    }

    public function update(Request $request)
    {
        $validatedData = $request->validate([
            'name' => ['required', 'string'],
            'email' => [
                'required',
                'email',
                Rule::unique('users')->ignore(auth()->user()),
            ],
        ]);

        auth()->user()->update($validatedData);

        return response()->json($validatedData, Response::HTTP_OK);
    }


    // can be extracted to a seperate controller
    public function downloadProfileImg(Request $request)
    {
        $path = $request->user()->profile_img;

        if (!$path) {
            return response()->json(['message' => 'Image not found.'], Response::HTTP_NOT_FOUND);
        }

        /**
         * both are working fine
         * https://stackoverflow.com/questions/19012832/laravel-download-pdf-in-public-folder
         * https://stackoverflow.com/questions/76258056/laravel-unable-to-retrieve-the-file-size-for-file-at-location
         */
        
        return response()->download(public_path('storage/' . $path));
        //return Storage::disk('public')->download($path);
    }
}
