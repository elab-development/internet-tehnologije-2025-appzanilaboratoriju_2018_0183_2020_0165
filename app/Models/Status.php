<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Status extends Model
{
    const NACRT = 1;
    const CEKA_RECENZIJU = 2;
    const OBJAVLJEN = 3;
    const ODBIJEN = 4;

    /** @use HasFactory<\Database\Factories\StatusFactory> */
    use HasFactory;

    protected $table = 'status';
    protected $primaryKey = 'StatusID';
    protected $fillable = ['Naziv'];

    public function naucniRadovi()
    {
        return $this->hasMany(NaucniRad::class, 'StatusID');
    }

    public function stavkeRecenzije()
    {
        return $this->hasMany(StavkaRecenzije::class, 'StatusID', 'StatusID');
    }

}
