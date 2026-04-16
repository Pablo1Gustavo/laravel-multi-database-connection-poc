<?php

namespace App\Models;

use Database\Factories\LabelableFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphPivot;

class Labelable extends MorphPivot
{
    /** @use HasFactory<LabelableFactory> */
    use HasFactory;

    protected $connection = 'primary';

    protected $table = 'labelables';

    public $incrementing = true;

    protected $fillable = [
        'label_id',
        'labelable_type',
        'labelable_id',
    ];
}
