<?php

namespace App\Http\Controllers;

use App\Models\Author;
use App\Models\Sticker;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StickerController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Sticker::query();

        if ($request->filled('stickerable_type')) {
            $query->where('stickers.stickerable_type', $request->input('stickerable_type'));
        }

        if ($request->filled('author_id')) {
            $query->where('stickers.stickerable_type', Author::class)
                ->where('stickers.stickerable_id', (int) $request->input('author_id'));
        }

        return response()->json($query->get());
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'stickerable_type' => ['required', 'string'],
            'stickerable_id' => ['required', 'integer'],
        ]);

        $sticker = Sticker::create($validated);

        return response()->json($sticker, 201);
    }

    public function show(Sticker $sticker): JsonResponse
    {
        return response()->json($sticker);
    }

    public function update(Request $request, Sticker $sticker): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
        ]);

        $sticker->update($validated);

        return response()->json($sticker);
    }

    public function destroy(Sticker $sticker): JsonResponse
    {
        $sticker->delete();

        return response()->json(null, 204);
    }
}
