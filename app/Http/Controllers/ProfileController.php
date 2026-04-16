<?php

namespace App\Http\Controllers;

use App\Models\Profile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Profile::query();

        if ($request->filled('author_name')) {
            $query->joinRelationship('author', function ($join) use ($request) {
                $join->where('authors.name', 'like', '%'.$request->input('author_name').'%');
            });
        }

        if ($request->boolean('author_has_articles')) {
            $query->powerJoinWhereHas('author');
        }

        if ($request->boolean('author_without_articles')) {
            $query->joinRelationship('author', fn ($join) => $join->left())
                ->whereNotIn('authors.id', function ($sub) {
                    $sub->select('author_id')->from('articles');
                });
        }

        if ($request->input('sort') === 'author_name') {
            $query->orderByPowerJoins('author.name');
        } elseif ($request->input('sort') === 'author_email') {
            $query->orderByPowerJoins('author.email');
        }

        return response()->json($query->get());
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'author_id' => ['required', 'integer', 'exists:primary.authors,id', 'unique:secondary.profiles,author_id'],
            'website' => ['nullable', 'url', 'max:255'],
            'twitter_handle' => ['nullable', 'string', 'max:255'],
            'avatar_url' => ['nullable', 'url', 'max:255'],
        ]);

        $profile = Profile::create($validated);

        return response()->json($profile, 201);
    }

    public function show(Profile $profile): JsonResponse
    {
        $profile = Profile::leftJoinRelationship('author', fn ($join) => $join->withTrashed())
            ->where('profiles.id', $profile->id)
            ->select(
                'profiles.*',
                'authors.name as author_name',
                'authors.email as author_email',
                'authors.deleted_at as author_deleted_at',
            )
            ->first();

        $profile->author_soft_deleted = (bool) $profile->author_deleted_at;

        return response()->json($profile);
    }

    public function update(Request $request, Profile $profile): JsonResponse
    {
        $validated = $request->validate([
            'website' => ['nullable', 'url', 'max:255'],
            'twitter_handle' => ['nullable', 'string', 'max:255'],
            'avatar_url' => ['nullable', 'url', 'max:255'],
        ]);

        $profile->update($validated);

        return response()->json($profile);
    }

    public function destroy(Profile $profile): JsonResponse
    {
        $profile->delete();

        return response()->json(null, 204);
    }
}
