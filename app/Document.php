<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    protected $table = 'documents';
    protected $fillable = ['name', 'extension', 'size'];

    public function getAttributeSize($value){
        return number_format($value/1024, 1);
    }
}
