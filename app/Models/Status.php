<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Status extends Model
{
    /** @use HasFactory<\Database\Factories\StatusFactory> */
    use HasFactory;

    protected $table = 'status';
    protected $primaryKey = 'StatusID';
    protected $fillable = ['Naziv'];

    public function naucniRadovi()
    {
        return $this->hasMany(NaucniRad::class, 'StatusID');
    }
}
