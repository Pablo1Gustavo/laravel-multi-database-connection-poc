<?php

namespace App\Models;

use Database\Factories\StickerFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Sticker extends Model
{
    /** @use HasFactory<StickerFactory> */
    use HasFactory;

    protected $connection = 'secondary';

    protected $table = 'stickers';

    protected $fillable = [
        'name',
        'stickerable_type',
        'stickerable_id',
    ];

    public function stickerable(): MorphTo
    {
        return $this->morphTo();
    }
}
