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
        // If you store just the filename in `path`:
        // return url('storage/' . $this->file_name);

            //  return Storage::disk('public')->url($this->file_name);

              return env('APP_URL') . $this->file_name;
        
        // Or, if you store the full "public" disk path:
        // return Storage::disk('public')->url($this->path);
    }
}
