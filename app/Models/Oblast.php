<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Oblast extends Model
{
    /** @use HasFactory<\Database\Factories\OblastFactory> */
    use HasFactory;

    protected $fillable = ['naziv'];

    protected $primaryKey = 'oblastId';

    public function radovi(){
    return $this->belongsToMany(
        NaucniRad::class,
        'OblastiRada',
        'oblastId',
        'NRID'
    );
}


}
