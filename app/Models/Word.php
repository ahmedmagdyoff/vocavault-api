<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable(['word', 'meaning', 'category_id'])]
class Word extends Model
{
    /**
     * Get the user that owns the word.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the category of the word.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the videos associated with the word.
     */
    public function videos(): BelongsToMany
    {
        return $this->belongsToMany(Video::class, 'video_words');
    }

    /**
     * Get the grammatical forms associated with the word.
     */
    public function forms(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(WordForm::class);
    }
}
