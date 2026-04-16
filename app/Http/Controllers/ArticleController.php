<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ArticleController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Article::query();

        if ($request->filled('author_name')) {
            $query->joinRelationship('author', function ($join) use ($request) {
                $join->where('authors.name', 'like', '%'.$request->input('author_name').'%');
            });
        }

        if ($request->boolean('has_comments')) {
            $query->powerJoinWhereHas('comments');
        }

        if ($request->filled('min_comments')) {
            $query->powerJoinHas('comments', '>=', (int) $request->input('min_comments'));
        }

        if ($request->boolean('without_comments')) {
            $query->powerJoinDoesntHave('comments');
        }

        if ($request->boolean('published')) {
            $query->published();
        }

        if ($request->input('sort') === 'author_name') {
            $query->orderByPowerJoins('author.name');
        } elseif ($request->input('sort') === 'comments_count') {
            $query->orderByPowerJoinsCount('comments.id', 'desc');
        } elseif ($request->input('sort') === 'latest_comment') {
            $query->orderByPowerJoinsMax('comments.created_at', 'desc');
        }

        return response()->json($query->get());
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'author_id' => ['required', 'integer', 'exists:primary.authors,id'],
            'title' => ['required', 'string', 'max:255'],
            'body' => ['nullable', 'string'],
            'published' => ['boolean'],
        ]);

        $article = Article::create($validated);

        return response()->json($article, 201);
    }

    public function show(Article $article): JsonResponse
    {
        $article = Article::leftJoinRelationship('authorWithTrashed')
            ->where('articles.id', $article->id)
            ->select('articles.*', 'authors.name as author_name', 'authors.deleted_at as author_deleted_at')
            ->withCount('comments')
            ->first();

        $article->author_soft_deleted = (bool) $article->author_deleted_at;

        return response()->json($article);
    }

    public function update(Request $request, Article $article): JsonResponse
    {
        $validated = $request->validate([
            'author_id' => ['sometimes', 'integer', 'exists:primary.authors,id'],
            'title' => ['sometimes', 'string', 'max:255'],
            'body' => ['nullable', 'string'],
            'published' => ['boolean'],
        ]);

        $article->update($validated);

        return response()->json($article);
    }

    public function destroy(Article $article): JsonResponse
    {
        $article->delete();

        return response()->json(null, 204);
    }
}
