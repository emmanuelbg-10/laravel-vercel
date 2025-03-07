<?php

namespace App\Models;

use App\Models\Theme as ModelsTheme;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Psy\Output\Theme;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'summary',
        'body',
        'published_at',
        'theme_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function votedUsers()
    {
        return $this->belongsToMany(User::class, 'post_votes')->withPivot('vote_type');
    }

    public function downvotes()
{
    return $this->belongsToMany(User::class, 'post_votes')->wherePivot('vote_type', 'dislike');
}

public function theme()
{
    return $this->belongsTo(ModelsTheme::class);
}
    
}
