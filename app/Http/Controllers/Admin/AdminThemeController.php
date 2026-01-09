<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminThemeController extends Controller
{
    /**
     * Toggle the admin's theme preference.
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function toggle(Request $request)
    {
        $validated = $request->validate([
            'theme' => 'required|in:light,dark'
        ]);

        Auth::guard('admin')->user()->update([
            'theme_preference' => $validated['theme']
        ]);

        return response()->json([
            'success' => true,
            'theme' => $validated['theme']
        ]);
    }
}
