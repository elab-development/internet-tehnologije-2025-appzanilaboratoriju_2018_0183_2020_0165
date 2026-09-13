# NILApp — aplikacija za upravljanje naučnom laboratorijom

Veb aplikacija za vođenje rada naučne laboratorije: predaja naučnih radova, recenzija,
verzionisanje, referenciranje radova i praćenje citiranosti kroz spoljne servise.

Seminarski rad iz predmeta **Internet tehnologije**, Fakultet organizacionih nauka.

- Mihajlo Aćimović, 2018/0183
- Lazar Fibišan, 2020/0165

## Tehnologije

**Backend:** Laravel 12, PHP 8.2, MySQL, Laravel Sanctum (token autentifikacija)
**Frontend:** React 19, Vite 7, React Router 7, Bootstrap 5.3, Axios, Recharts

## Šta aplikacija radi

Tri uloge, svaka sa svojim delom aplikacije:

| Uloga | Može da |
|---|---|
| **Posetilac** (bez prijave) | pregleda i pretražuje objavljene radove, čita PDF, gleda biografije istraživača, citiranost i srodne radove |
| **Istraživač** | predaje rad kao nacrt, dodaje koautore i citirane radove, prilaže PDF, šalje rad na recenziju, pravi nove verzije, čita recenzije svojih radova |
| **Recenzent** | vidi radove koji čekaju njegovu ocenu, čita PDF, ocenjuje rad (objavljen / odbijen / vraćen na doradu) |
| **Administrator** | upravlja korisnicima i ulogama, pregleda sve radove, briše radove, dodeljuje i skida recenzente, gleda statistiku |

Korisnik može imati više uloga i bira jednu pri prijavi. Izabrana uloga se upisuje u token, pa
prijava kao Istraživač ne daje pristup administratorskim rutama.

## Zahtevi

- PHP **8.2** ili noviji, sa ekstenzijama `pdo_mysql`, `mbstring`, `fileinfo`, `openssl`
- Composer 2
- Node.js **20** ili noviji, sa npm
- MySQL 8 ili MariaDB 10.4+ (npr. kroz XAMPP)

## Pokretanje

Backend i frontend se pokreću kao **dva odvojena procesa**. Frontend očekuje backend na
`http://localhost:8000` — ta adresa je podešena u `frontend/src/api/axios.js`.

### 1. Backend

```bash
git clone <adresa-repozitorijuma> NILApp
cd NILApp

composer install

cp .env.example .env
php artisan key:generate
```

U `.env` podesi pristup bazi:

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nil_app
DB_USERNAME=root
DB_PASSWORD=
```

Napravi praznu bazu (`nil_app`), pa je popuni:

```bash
php artisan migrate --seed
php artisan serve
```

Backend radi na `http://localhost:8000`.

### 2. Frontend

U drugom terminalu:

```bash
cd frontend
npm install
npm run dev
```

Aplikacija je na adresi koju ispiše Vite, obično `http://localhost:5173`.

## Nalozi za prijavu

Posle `migrate --seed` postoje tri naloga, po jedan za svaku ulogu:

| Email | Lozinka | Uloga |
|---|---|---|
| `admin@gmail.com` | `123` | Administrator |
| `istrazivac@gmail.com` | `123` | Istraživač |
| `recenzent@gmail.com` | `123` | Recenzent |

Uz njih seeder pravi i deset nasumičnih korisnika sa različitim kombinacijama uloga. **Svi imaju
lozinku `123`.** To je korisno kad sistem rad dodeli recenzentu koji nije `recenzent@gmail.com`.

## Šta seeder napravi

- 4 statusa, 3 uloge, oblasti istraživanja
- 13 korisnika
- 16 stvarnih objavljenih radova sa DOI-jevima (podaci su hardkodovani, ne povlače se uživo, pa
  seeder radi i bez interneta) i 7 radova laboratorije raspoređenih po statusima
- veze citiranja između radova
- recenzije sa istorijom ocena koja odgovara statusu rada
- **PDF fajl za svaki rad**, koji generiše `FajlRadaSeeder`

Zadnja stavka je važna: fajlovi se čuvaju u `storage/app/private/radovi`, što nije u gitu. Posle
`git clone` taj folder je prazan, pa seeder PDF-ove generiše sam. Ako na stranici rada nema dugmeta
za čitanje PDF-a, pokreni:

```bash
php artisan db:seed --class=FajlRadaSeeder
```

## Spoljni servisi

Stranica detalja rada poziva dva javna servisa. Rade bez ključa, ali traže internet:

- **CrossRef** — broj citata rada po DOI-ju, uz istoriju merenja kroz vreme
- **OpenAlex** — srodni radovi po ključnim rečima

Odgovori se keširaju 6 sati. Rad bez DOI-ja ne prikazuje grešku nego objašnjenje da DOI još nije
dodeljen.

## Struktura

```
app/Http/Controllers    kontroleri (Auth, NaucniRad, Recenzija, User, Oblast, Statistika)
app/Http/Resources      API resursi, JSON u camelCase
app/Http/Middleware     CheckRole — proverava ulogu i ulogu upisanu u token
app/Models              Eloquent modeli
database/migrations     migracije
database/seeders        seederi, uključujući FajlRadaSeeder
routes/api.php          32 rute
frontend/src/pages      10 stranica
frontend/src/components 16 komponenti
frontend/src/hooks      useAuth, usePaginatedFetch
```

## Korisne komande

```bash
php artisan migrate:fresh --seed     # ponovo napravi bazu i podatke
php artisan route:list --path=api    # spisak API ruta
cd frontend && npm run build         # produkcijski build
cd frontend && npx eslint src        # provera frontend koda
```
