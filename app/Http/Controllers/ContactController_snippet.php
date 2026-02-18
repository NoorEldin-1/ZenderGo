/**
* Update the label for the specified contact.
*/
public function updateLabel(Request $request, Contact $contact)
{
// Ensure user owns the contact
if ($contact->user_id !== Auth::id()) {
return response()->json(['success' => false, 'message' => 'غير مصرح'], 403);
}

$validated = $request->validate([
'label_text' => 'nullable|string|max:20',
'label_color' => 'nullable|string|max:7',
]);

$contact->update([
'label_text' => $validated['label_text'] ?? null,
'label_color' => $validated['label_color'] ?? null,
]);

return response()->json([
'success' => true,
'message' => 'تم تحديث العلامة بنجاح',
'label_text' => $contact->label_text,
'label_color' => $contact->label_color,
]);
}