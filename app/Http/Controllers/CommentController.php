<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Comment::query();

        if ($request->filled('author_name')) {
            $query->joinRelationship('article.author', [
                'author' => function ($join) use ($request) {
                    $join->where('authors.name', 'like', '%'.$request->input('author_name').'%');
                },
            ]);
        }

        if ($request->boolean('published_articles_only')) {
            $query->powerJoinWhereHas('article', function ($join) {
                $join->where('articles.published', true);
            });
        }

        if ($request->boolean('only_comment_on_article')) {
            $query->powerJoinHas('article', '=', 1);
        }

        if ($request->input('sort') === 'article_title') {
            $query->orderByPowerJoins('article.title');
        } elseif ($request->input('sort') === 'author_name') {
            $query->orderByPowerJoins('article.author.name');
        }

        return response()->json($query->get());
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'article_id' => ['required', 'integer', 'exists:secondary.articles,id'],
            'body' => ['required', 'string'],
            'author_name' => ['required', 'string', 'max:255'],
        ]);

        $comment = Comment::create($validated);

        return response()->json($comment, 201);
    }

    public function show(Comment $comment): JsonResponse
    {
        $comment = Comment::leftJoinRelationship('article.author', [
            'author' => fn ($join) => $join->withTrashed(),
        ])
            ->where('comments.id', $comment->id)
            ->select(
                'comments.*',
                'authors.name as article_author_name',
                'authors.email as article_author_email',
                'authors.deleted_at as article_author_deleted_at',
            )
            ->first();

        $comment->article_author_soft_deleted = (bool) $comment->article_author_deleted_at;

        return response()->json($comment);
    }

    public function update(Request $request, Comment $comment): JsonResponse
    {
        $validated = $request->validate([
            'body' => ['sometimes', 'string'],
            'author_name' => ['sometimes', 'string', 'max:255'],
        ]);

        $comment->update($validated);

        return response()->json($comment);
    }

    public function destroy(Comment $comment): JsonResponse
    {
        $comment->delete();

        return response()->json(null, 204);
    }
}
