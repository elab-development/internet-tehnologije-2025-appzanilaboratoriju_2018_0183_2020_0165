<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */

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

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */


    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
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
