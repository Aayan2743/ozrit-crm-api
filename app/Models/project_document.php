<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class project_document extends Model
{
    use HasFactory;

    public $table="project_documents";

    public $guarded=[];

     public function project()
    {
        // If your primary key on projects is something other than "id", pass it as the 3rd argument
        // If your foreign key on project_documents is something other than "project_id", pass it as the 2nd.
        return $this->belongsTo(Project::class);
    }
}
