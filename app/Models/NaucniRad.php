<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NaucniRad extends Model
{
    /** @use HasFactory<\Database\Factories\NaucniRadFactory> */
    use HasFactory;
    protected $table = 'NaucniRad';
    protected $fillable = ['naslov','abstrakt', 'godina', 'grupaId', 'verzija', 'StatusID'];

    protected $primaryKey = 'NRID';
    
    public function oblasti() {
        return $this->belongsToMany(
            Oblast::class,    // model
            'OblastiRada',    // pivot tabela
            'NRID',           // FK NaucniRad u pivot tabeli
            'oblastId'        // FK Oblast u pivot tabeli
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

}
