<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StavkaRecenzije extends Model
{

    use HasFactory;

    protected $table = 'StavkaRecenzije';
    protected $primaryKey = 'StavkaID';

    protected $fillable = ['RecenzijaID', 'Komentar', 'StatusID'];

    public function recenzija()
    {
        return $this->belongsTo(Recenzija::class, 'RecenzijaID');
    }

    public function status()
    {
        return $this->belongsTo(Status::class, 'StatusID');
    }
}
