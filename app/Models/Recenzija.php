<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Recenzija extends Model
{
    /** @use HasFactory<\Database\Factories\RecenzijaFactory> */
    use HasFactory;

    protected $table = 'recenzija';
    protected $primaryKey = 'RecenzijaID';
    protected $fillable = ['Datum', 'ZapID', 'NRID'];


    public function stavke()
    {
        return $this->hasMany(StavkaRecenzije::class, 'RecenzijaID');
    }


    public function korisnik()
    {
        return $this->belongsTo(User::class, 'ZapID');
    }

    
    public function naucniRad()
    {
        return $this->belongsTo(NaucniRad::class, 'NRID');
    }
}
