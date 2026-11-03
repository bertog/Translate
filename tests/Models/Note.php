<?php

namespace TheNonsenseFactory\Translate\Tests\Models;

use Illuminate\Database\Eloquent\Model;
use TheNonsenseFactory\Translate\Traits\Translatable;

/**
 * A model that uses the trait without declaring the $translatable property.
 */
class Note extends Model
{
    use Translatable;

    protected $guarded = [];
}
