<?php

namespace App\Http\Controllers;

use App\Models\Label;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LabelController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(Label::get());
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'color' => ['sometimes', 'string', 'max:7'],
        ]);

        $label = Label::create($validated);

        return response()->json($label, 201);
    }

    public function show(Label $label): JsonResponse
    {
        return response()->json($label);
    }

    public function update(Request $request, Label $label): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'color' => ['sometimes', 'string', 'max:7'],
        ]);

        $label->update($validated);

        return response()->json($label);
    }

    public function destroy(Label $label): JsonResponse
    {
        $label->delete();

        return response()->json(null, 204);
    }
}
