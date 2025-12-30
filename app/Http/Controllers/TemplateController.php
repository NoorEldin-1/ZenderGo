<?php

namespace App\Http\Controllers;

use App\Models\Template;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TemplateController extends Controller
{
    /**
     * Get all templates for the user.
     */
    public function index()
    {
        $templates = Auth::user()->templates()->latest()->get();

        return response()->json($templates);
    }

    /**
     * Store a new template.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'content' => 'required|string|max:4096',
        ]);

        $template = Auth::user()->templates()->create($validated);

        return response()->json([
            'success' => true,
            'message' => 'تم حفظ القالب بنجاح',
            'template' => $template
        ]);
    }

    /**
     * Delete a template.
     */
    public function destroy(Template $template)
    {
        if ($template->user_id !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'غير مصرح'], 403);
        }

        $template->delete();

        return response()->json([
            'success' => true,
            'message' => 'تم حذف القالب'
        ]);
    }
}
