<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IstorijaCitiranosti extends Model
{
    use HasFactory;

    protected $table = 'IstorijaCitiranosti';
    protected $primaryKey = 'IstorijaID';

    protected $fillable = ['NRID', 'brojCitata', 'datum'];

    protected $casts = [
        'datum' => 'date',
    ];

    public function naucniRad()
    {
        return $this->belongsTo(NaucniRad::class, 'NRID');
    }
}
