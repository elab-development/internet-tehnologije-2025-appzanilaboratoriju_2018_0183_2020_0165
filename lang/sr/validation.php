<?php

return [

    'required'  => 'Polje :attribute je obavezno.',
    'string'    => 'Polje :attribute mora biti tekst.',
    'integer'   => 'Polje :attribute mora biti ceo broj.',
    'numeric'   => 'Polje :attribute mora biti broj.',
    'array'     => 'Polje :attribute mora biti lista.',
    'file'      => 'Polje :attribute mora biti fajl.',
    'email'     => 'Polje :attribute mora biti ispravna email adresa.',
    'confirmed' => 'Potvrda polja :attribute se ne poklapa.',
    'unique'    => 'Vrednost polja :attribute je već zauzeta.',
    'exists'    => 'Izabrana vrednost za :attribute ne postoji.',
    'in'        => 'Izabrana vrednost za :attribute nije dozvoljena.',
    'date'      => 'Polje :attribute mora biti ispravan datum.',
    'boolean'   => 'Polje :attribute mora biti tačno ili netačno.',
    'mimes'     => 'Polje :attribute mora biti fajl tipa: :values.',
    'mimetypes' => 'Polje :attribute mora biti fajl tipa: :values.',

    'min' => [
        'numeric' => 'Polje :attribute ne može biti manje od :min.',
        'file'    => 'Fajl u polju :attribute ne može biti manji od :min kilobajta.',
        'string'  => 'Polje :attribute mora imati najmanje :min znakova.',
        'array'   => 'Polje :attribute mora imati najmanje :min stavki.',
    ],

    'max' => [
        'numeric' => 'Polje :attribute ne može biti veće od :max.',
        'file'    => 'Fajl u polju :attribute ne može biti veći od :max kilobajta.',
        'string'  => 'Polje :attribute ne može imati više od :max znakova.',
        'array'   => 'Polje :attribute ne može imati više od :max stavki.',
    ],

    'attributes' => [
        'naslov'                => 'naslov',
        'abstrakt'              => 'apstrakt',
        'kljucneReci'           => 'ključne reči',
        'godina'                => 'godina',
        'oblasti'               => 'oblasti',
        'oblasti.*'             => 'oblast',
        'autori'                => 'koautori',
        'autori.*'              => 'koautor',
        'reference'             => 'citirani radovi',
        'reference.*'           => 'citirani rad',
        'fajl'                  => 'fajl rada',
        'DOI'                   => 'DOI',
        'StatusID'              => 'status',
        'ImePrezime'            => 'ime i prezime',
        'email'                 => 'email',
        'password'              => 'lozinka',
        'password_confirmation' => 'potvrda lozinke',
        'Biografija'            => 'biografija',
        'uloge'                 => 'uloge',
        'uloge.*'               => 'uloga',
        'uloga_id'              => 'uloga',
        'Komentar'              => 'komentar',
    ],

];
