<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
class project extends Model
{
    use HasFactory;

       public $guarded=[];

       protected $casts = [
            'services' => 'array',
            'attachments'=>'array',
            'assignedTo'=>'array'
        ];

     public function customer()
    {
        return $this->belongsTo(Customer::class, 'customerId');
    }

    public function getDeadlineAttribute($value): ?string
    {
        if (! $value) {
            return null;
        }

        return Carbon::parse($value)->format('F j, Y');
    }

     public function documents()
    {
        // The default foreign key is "project_id", and local primary key is "id"
        return $this->hasMany(project_document::class);
    }

    
}
