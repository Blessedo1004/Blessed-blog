<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TiptapImageController extends Controller
{
    public function upload(Request $request)
    {
        try {
            $request->validate([
                'image' => 'required|image|max:2048', // 2MB Max
            ]);

            if ($request->hasFile('image')) {
                // This stores in storage/app/public/editor-images
                $path = $request->file('image')->store('editor-images', 'public');
                
                // Use asset() to match how your working featured images are loaded
                return response()->json([
                    'url' => asset('storage/' . $path),
                ]);
            }
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json(['message' => 'Upload failed'], 400);
    }
}
