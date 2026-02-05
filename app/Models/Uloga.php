<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Uloga extends Model
{
    /** @use HasFactory<\Database\Factories\UlogaFactory> */
    use HasFactory;

    protected $primaryKey = 'UlogaID';

    protected $fillable = ['Naziv'];

        public function useri(){
		return $this->belongsToMany(User::class, 'dodela_uloge', 'UlogaID', 'ZapID')->withPivot('Datum');
    }

    

}
