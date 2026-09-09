<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{

    use HasFactory, Notifiable, HasApiTokens;

    protected $table = 'korisnik';

    protected $primaryKey = 'ZapID';

protected $fillable = [
        'ImePrezime',
        'email',
        'password',
        'Biografija',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function uloge() {
            return $this->belongsToMany(Uloga::class, 'DodelaUloge', 'ZapID', 'UlogaID')
                        ->withPivot('Datum');
    }

    public function naucniRadovi()
    {
        return $this->belongsToMany(
            NaucniRad::class,
            'Autorstvo',
            'ZapID',
            'NRID'
        );
    }

    public function recenzije()
    {
        return $this->hasMany(Recenzija::class, 'ZapID', 'ZapID');
    }

}
