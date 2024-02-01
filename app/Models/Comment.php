<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Image\Manipulations;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

//use Spatie\MediaLibrary\MediaCollections\Models\Media;


class Comment extends Model
{
    use HasFactory;

    protected $fillable = ['message', 'post_id', 'user_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the category that owns the post.
     */
    public function post()
    {
        return $this->belongsTo(Post::class,'post_id');
    }
}
