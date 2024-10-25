<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class content extends Model
{
    protected $guarded = [];

    public function documents()
    {
        return $this->hasMany(Documents::class);
    }
}
