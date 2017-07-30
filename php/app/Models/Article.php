<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    protected $fillable = [
        'title', 'content', 'read','catalog_id'
    ];

    public function catalog()
    {
        return $this->belongsTo('App\Catalog');
    }

    public function tags()
    {
        return $this->belongsToMany('App\Tag');//, 'article_tag', 'article_id', 'tag_id');
    }

    public function comments()
    {
        return $this->hasMany('App\Comment');
    }
}
