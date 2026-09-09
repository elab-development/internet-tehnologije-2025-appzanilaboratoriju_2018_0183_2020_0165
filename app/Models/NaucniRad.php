<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NaucniRad extends Model
{

    use HasFactory;
    protected $table = 'NaucniRad';
    protected $fillable = ['naslov','abstrakt', 'kljucneReci', 'godina', 'DOI', 'spoljniAutori', 'grupaId', 'verzija', 'StatusID'];

    protected $primaryKey = 'NRID';

    public function oblasti() {
        return $this->belongsToMany(
            Oblast::class,
            'OblastiRada',
            'NRID',
            'oblastId'
        );
    }

    public function status()
    {
        return $this->belongsTo(Status::class, 'StatusID');
    }

    public function citira()
    {
        return $this->belongsToMany(
            NaucniRad::class,
            'reference',
            'RadID',
            'CitiraniRadID'
        );
    }

    public function citiranOdStrane()
    {
        return $this->belongsToMany(
                NaucniRad::class,
                'reference',
                'CitiraniRadID',
                'RadID'
            );
    }

    public function autori()
    {
        return $this->belongsToMany(
            User::class,
            'Autorstvo',
            'NRID',
            'ZapID'
        );
    }

    public function recenzije()
    {
        return $this->hasMany(Recenzija::class, 'NRID', 'NRID');
    }

}
