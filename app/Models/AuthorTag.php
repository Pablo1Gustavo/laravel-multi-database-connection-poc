<?php

namespace App\Models;

use Database\Factories\AuthorTagFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;

class AuthorTag extends Pivot
{
    /** @use HasFactory<AuthorTagFactory> */
    use HasFactory;

    protected $connection = 'primary';

    protected $table = 'author_tag';

    public $incrementing = true;

    protected $fillable = [
        'author_id',
        'tag_id',
    ];
}
