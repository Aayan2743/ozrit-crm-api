<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class customer extends Model
{
    use HasFactory;

    public $guarded=[];


     public function projects()
    {
        return $this->hasMany(Project::class, 'customerId');
    }

}
