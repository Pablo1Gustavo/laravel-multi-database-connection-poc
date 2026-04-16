<?php

use App\Http\Controllers\ArticleController;
use App\Http\Controllers\AuthorController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\LabelController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StickerController;
use App\Http\Controllers\TagController;
use Illuminate\Support\Facades\Route;

Route::apiResource('authors', AuthorController::class);
Route::apiResource('articles', ArticleController::class);
Route::apiResource('comments', CommentController::class);
Route::apiResource('profiles', ProfileController::class);
Route::apiResource('tags', TagController::class);
Route::apiResource('labels', LabelController::class);
Route::apiResource('stickers', StickerController::class);
