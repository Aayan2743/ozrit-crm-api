<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
class project_document extends Model
{
    use HasFactory;

    public $table="project_documents";

    public $guarded=[];

     public function project()
    {
        
        return $this->belongsTo(Project::class);
    }


      protected $appends = ['url'];

    public function getUrlAttribute()
    {
      

              return env('APP_URL') . $this->file_name;
        
       
    }
}
