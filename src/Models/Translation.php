<?php

namespace TheNonsenseFactory\Translate\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;

class Translation extends Model
{
    protected $guarded = [];

    public function translatable()
    {
        return $this->morphMany();
    }

    public function scopeCurrentLang($query)
    {
        return $query->whereLang(App::getLocale());
    }

}
