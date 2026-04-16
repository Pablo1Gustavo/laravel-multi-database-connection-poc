<?php

namespace App\Http\Controllers;

use App\Models\Author;
use App\Models\Comment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthorController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Author::query();

        if ($request->boolean('has_published_articles')) {
            $query->powerJoinWhereHas('articles', function ($join) {
                $join->where('articles.published', true);
            });
        }

        if ($request->filled('min_articles')) {
            $query->powerJoinHas('articles', '>=', (int) $request->input('min_articles'));
        }

        if ($request->boolean('without_articles')) {
            $query->powerJoinDoesntHave('articles');
        }

        if ($request->boolean('has_comments')) {
            $query->powerJoinWhereHas('commentsThroughArticles');
        }

        if ($request->boolean('has_stickers')) {
            $query->powerJoinWhereHas('stickers');
        }

        if ($request->filled('tag_name')) {
            $query->powerJoinWhereHas('tags', function ($join) use ($request) {
                $join->where('tags.name', $request->input('tag_name'));
            });
        }

        if ($request->boolean('has_profile')) {
            $query->powerJoinWhereHas('profile');
        }

        if ($request->input('sort') === 'articles_count') {
            $query->orderByPowerJoinsCount('articles.id', 'desc');
        } elseif ($request->input('sort') === 'latest_article') {
            $query->orderByPowerJoinsMax('articles.created_at', 'desc');
        }

        return response()->json($query->get());
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:primary.authors,email'],
            'bio' => ['nullable', 'string'],
        ]);

        $author = Author::create($validated);

        return response()->json($author, 201);
    }

    public function show(Author $author): JsonResponse
    {
        $author->loadCount([
            'articles',
            'publishedArticles',
            'stickers',
        ]);

        $author->comments_through_articles_count = Comment::joinRelationship('article')
            ->where('articles.author_id', $author->id)
            ->whereNull('articles.deleted_at')
            ->count();

        return response()->json($author);
    }

    public function update(Request $request, Author $author): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'email', 'unique:primary.authors,email,'.$author->id],
            'bio' => ['nullable', 'string'],
        ]);

        $author->update($validated);

        return response()->json($author);
    }

    public function destroy(Author $author): JsonResponse
    {
        $author->delete();

        return response()->json(null, 204);
    }
}
