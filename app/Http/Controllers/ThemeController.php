<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ThemeController extends Controller
{
    /**
     * Toggle the user's theme preference.
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function toggle(Request $request)
    {
        $validated = $request->validate([
            'theme' => 'required|in:light,dark'
        ]);

        $request->user()->update([
            'theme_preference' => $validated['theme']
        ]);

        return response()->json([
            'success' => true,
            'theme' => $validated['theme']
        ]);
    }
}
