<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['word_id', 'form_type', 'value'])]
class WordForm extends Model
{
    /**
     * Get the word that owns the form.
     */
    public function word(): BelongsTo
    {
        return $this->belongsTo(Word::class);
    }
}
