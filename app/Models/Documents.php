<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Documents extends Model
{
    protected $guarded = [];

    public function content()
    {
        return $this->belongsTo(Content::class);
    }
}
