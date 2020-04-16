<?php

namespace TheNonsenseFactory\Translate\Tests\Models;


use Illuminate\Database\Eloquent\Model;
use TheNonsenseFactory\Translate\Traits\Translatable;

class Post extends Model
{
    use Translatable;

    protected $translatable = ['title', 'body'];

    protected $guarded = [];
}

