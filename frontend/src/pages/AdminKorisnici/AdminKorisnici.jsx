import { useEffect, useMemo, useState } from 'react'
import api from '../../api/axios'
import usePaginatedFetch from '../../hooks/usePaginatedFetch'
import useAuth from '../../hooks/useAuth'
import { ULOGE, NAZIV_ULOGE } from '../../context/authKontekst'
import Modal from '../../components/Modal/Modal'
import PotvrdaBrisanja from '../../components/PotvrdaBrisanja/PotvrdaBrisanja'
import Input from '../../components/Input/Input'
import Select from '../../components/Select/Select'
import Button from '../../components/Button/Button'
import Pagination from '../../components/Pagination/Pagination'
import Poruka from '../../components/Poruka/Poruka'
import Ucitavanje from '../../components/Ucitavanje/Ucitavanje'

const PO_STRANI = 8

const SVE_ULOGE = [
  { id: ULOGE.ADMINISTRATOR, naziv: NAZIV_ULOGE[ULOGE.ADMINISTRATOR] },
  { id: ULOGE.RECENZENT, naziv: NAZIV_ULOGE[ULOGE.RECENZENT] },
  { id: ULOGE.ISTRAZIVAC, naziv: NAZIV_ULOGE[ULOGE.ISTRAZIVAC] },
]

const PRAZNA_FORMA = {
  ImePrezime: '',
  email: '',
  password: '',
  password_confirmation: '',
  Biografija: '',
  uloge: [ULOGE.ISTRAZIVAC],
}

export default function AdminKorisnici() {
  const { korisnik: prijavljeni } = useAuth()

  const [filterUloga, setFilterUloga] = useState('')
  const [obavestenje, setObavestenje] = useState(null)

  const [formaOtvorena, setFormaOtvorena] = useState(false)
  const [forma, setForma] = useState(PRAZNA_FORMA)
  const [greskeForme, setGreskeForme] = useState({})
  const [cuva, setCuva] = useState(false)

  const [ulogeKorisnik, setUlogeKorisnik] = useState(null)
  const [izabraneUloge, setIzabraneUloge] = useState([])

  const [izmenaKorisnik, setIzmenaKorisnik] = useState(null)
  const [izmena, setIzmena] = useState({
    ImePrezime: '',
    email: '',
    Biografija: '',
    password: '',
    password_confirmation: '',
  })

  const [zaBrisanje, setZaBrisanje] = useState(null)
  const [brise, setBrise] = useState(false)

  const { sviPodaci, ucitava, greska, strana, promeniStranu, osvezi } = usePaginatedFetch(
    '/admin/korisnici',
    {},
    PO_STRANI
  )

  const filtrirani = useMemo(() => {
    if (!filterUloga) return sviPodaci
    return sviPodaci.filter((k) => k.uloge?.some((u) => String(u.id) === String(filterUloga)))
  }, [sviPodaci, filterUloga])

  const ukupnoStrana = Math.max(1, Math.ceil(filtrirani.length / PO_STRANI))

  const trenutnaStrana = Math.min(strana, ukupnoStrana)

  const zaPrikaz = useMemo(() => {
    const pocetak = (trenutnaStrana - 1) * PO_STRANI
    return filtrirani.slice(pocetak, pocetak + PO_STRANI)
  }, [filtrirani, trenutnaStrana])

  useEffect(() => {
    promeniStranu(1)
  }, [filterUloga, promeniStranu])

  const prebaciUloguNaFormi = (idUloge) => {
    setForma((prethodna) => ({
      ...prethodna,
      uloge: prethodna.uloge.includes(idUloge)
        ? prethodna.uloge.filter((x) => x !== idUloge)
        : [...prethodna.uloge, idUloge],
    }))
  }

  const sacuvajKorisnika = async (dogadjaj) => {
    dogadjaj.preventDefault()
    setCuva(true)
    setGreskeForme({})

    try {
      await api.post('/admin/korisnici', forma)
      setFormaOtvorena(false)
      setForma(PRAZNA_FORMA)
      setObavestenje({ vrsta: 'uspeh', tekst: 'Korisnik je uspešno kreiran.' })
      osvezi()
    } catch (error) {
      const greske = error.response?.data?.errors
      if (greske) {
        setGreskeForme(Object.fromEntries(Object.entries(greske).map(([k, v]) => [k, v[0]])))
      } else {
        setObavestenje({
          vrsta: 'greska',
          tekst: error.response?.data?.message ?? 'Kreiranje korisnika nije uspelo.',
        })
      }
    } finally {
      setCuva(false)
    }
  }

  const otvoriIzmenu = async (korisnik) => {
    setGreskeForme({})
    setIzmenaKorisnik(korisnik)
    setIzmena({
      ImePrezime: korisnik.imePrezime ?? '',
      email: korisnik.email ?? '',
      Biografija: korisnik.biografija ?? '',
      password: '',
      password_confirmation: '',
    })

    try {
      const { data } = await api.get(`/admin/korisnici/${korisnik.id}`)
      const svez = data?.data ?? data?.podaci ?? data

      setIzmena((prethodna) => ({
        ...prethodna,
        ImePrezime: svez.imePrezime ?? prethodna.ImePrezime,
        email: svez.email ?? prethodna.email,
        Biografija: svez.biografija ?? prethodna.Biografija,
      }))
    } catch {
      setObavestenje({
        vrsta: 'greska',
        tekst: 'Nije moguće učitati sveže podatke korisnika, prikazani su podaci iz tabele.',
      })
    }
  }

  const sacuvajIzmenu = async () => {
    setCuva(true)
    setGreskeForme({})

    try {
      const telo = {
        ImePrezime: izmena.ImePrezime,
        email: izmena.email,
        Biografija: izmena.Biografija,
      }

      if (izmena.password) {
        telo.password = izmena.password
        telo.password_confirmation = izmena.password_confirmation
      }

      await api.put(`/admin/korisnici/${izmenaKorisnik.id}`, telo)
      setObavestenje({ vrsta: 'uspeh', tekst: 'Podaci korisnika su izmenjeni.' })
      setIzmenaKorisnik(null)
      osvezi()
    } catch (error) {
      const greske = error.response?.data?.errors

      if (greske) {
        setGreskeForme(Object.fromEntries(Object.entries(greske).map(([k, v]) => [k, v[0]])))
      } else {
        setObavestenje({
          vrsta: 'greska',
          tekst: error.response?.data?.message ?? 'Izmena korisnika nije uspela.',
        })
      }
    } finally {
      setCuva(false)
    }
  }

  const otvoriUloge = (korisnik) => {
    setUlogeKorisnik(korisnik)
    setIzabraneUloge((korisnik.uloge ?? []).map((u) => u.id))
  }

  const prebaciUlogu = (idUloge) => {
    setIzabraneUloge((prethodne) =>
      prethodne.includes(idUloge)
        ? prethodne.filter((x) => x !== idUloge)
        : [...prethodne, idUloge]
    )
  }

  const sacuvajUloge = async () => {
    setCuva(true)

    try {
      await api.put(`/admin/korisnici/${ulogeKorisnik.id}/uloge`, { uloge: izabraneUloge })
      setUlogeKorisnik(null)
      setObavestenje({ vrsta: 'uspeh', tekst: 'Uloge su ažurirane.' })
      osvezi()
    } catch (error) {
      setObavestenje({
        vrsta: 'greska',
        tekst: error.response?.data?.message ?? 'Izmena uloga nije uspela.',
      })
    } finally {
      setCuva(false)
    }
  }

  const obrisiKorisnika = async () => {
    setBrise(true)

    try {
      await api.delete(`/admin/korisnici/${zaBrisanje.id}`)
      setZaBrisanje(null)
      setObavestenje({ vrsta: 'uspeh', tekst: 'Korisnik je obrisan.' })
      osvezi()
    } catch (error) {
      setZaBrisanje(null)
      setObavestenje({
        vrsta: 'greska',
        tekst: error.response?.data?.message ?? 'Brisanje nije uspelo.',
      })
    } finally {
      setBrise(false)
    }
  }

  return (
    <div className="container py-4">
      <div className="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
        <div>
          <h2 className="mb-1">Upravljanje korisnicima</h2>
          <p className="text-secondary mb-0">Kreiranje naloga, dodela uloga i brisanje.</p>
        </div>

        <Button onClick={() => setFormaOtvorena(true)}>Dodaj korisnika</Button>
      </div>

      {obavestenje && (
        <Poruka vrsta={obavestenje.vrsta}>{obavestenje.tekst}</Poruka>
      )}

      <div className="row mb-3">
        <div className="col-12 col-md-4">
          <Select
            naziv="filterUloga"
            labela="Filter po ulozi"
            vrednost={filterUloga}
            onChange={(e) => setFilterUloga(e.target.value)}
            prazanTekst="Sve uloge"
            opcije={SVE_ULOGE.map((u) => ({ vrednost: u.id, tekst: u.naziv }))}
          />
        </div>
      </div>

      {ucitava && <Ucitavanje tekst="Učitavanje korisnika..." />}

      {!ucitava && greska && <Poruka vrsta="greska">{greska}</Poruka>}

      {!ucitava && !greska && filtrirani.length === 0 && (
        <Poruka vrsta="prazno" naslov="Nema rezultata">
          Nijedan korisnik nema izabranu ulogu.
        </Poruka>
      )}

      {!ucitava && !greska && filtrirani.length > 0 && (
        <>
          <div className="table-responsive">
            <table className="table align-middle">
              <thead>
                <tr>
                  <th>Ime i prezime</th>
                  <th>Email</th>
                  <th>Uloge</th>
                  <th className="text-end">Akcije</th>
                </tr>
              </thead>
              <tbody>
                {zaPrikaz.map((k) => (
                  <tr key={k.id}>
                    <td>{k.imePrezime}</td>
                    <td className="text-secondary small">{k.email}</td>
                    <td>
                      <div className="d-flex flex-wrap gap-1">
                        {(k.uloge ?? []).map((u) => (
                          <span key={u.id} className="badge bg-light text-dark border">
                            {u.naziv}
                          </span>
                        ))}
                      </div>
                    </td>
                    <td className="text-end">
                      <div className="d-flex gap-2 justify-content-end flex-wrap">
                        <Button varijanta="outline" velicina="mala" onClick={() => otvoriIzmenu(k)}>
                          Izmeni
                        </Button>
                        <Button varijanta="outline" velicina="mala" onClick={() => otvoriUloge(k)}>
                          Uloge
                        </Button>
                        <Button
                          varijanta="danger"
                          velicina="mala"
                          onemoguceno={k.id === prijavljeni?.ZapID}
                          onClick={() => setZaBrisanje(k)}
                        >
                          Obriši
                        </Button>
                      </div>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>

          <div className="mt-3">
            <Pagination
              trenutnaStrana={trenutnaStrana}
              ukupnoStrana={ukupnoStrana}
              naPromenu={promeniStranu}
            />
          </div>
        </>
      )}

      <Modal
        naslov="Novi korisnik"
        otvoren={formaOtvorena}
        naZatvaranje={() => setFormaOtvorena(false)}
        podnozje={
          <>
            <Button varijanta="outline" onClick={() => setFormaOtvorena(false)}>
              Odustani
            </Button>
            <Button
              tip="submit"
              ucitava={cuva}
              onemoguceno={forma.uloge.length === 0}
              onClick={sacuvajKorisnika}
            >
              Sačuvaj
            </Button>
          </>
        }
      >
        <form onSubmit={sacuvajKorisnika}>
          <Input
            naziv="ImePrezime"
            labela="Ime i prezime"
            vrednost={forma.ImePrezime}
            onChange={(e) => setForma({ ...forma, ImePrezime: e.target.value })}
            greska={greskeForme.ImePrezime}
            obavezno
          />
          <Input
            naziv="email"
            labela="Email"
            tip="email"
            vrednost={forma.email}
            onChange={(e) => setForma({ ...forma, email: e.target.value })}
            greska={greskeForme.email}
            obavezno
          />
          <Input
            naziv="password"
            labela="Lozinka"
            tip="password"
            vrednost={forma.password}
            onChange={(e) => setForma({ ...forma, password: e.target.value })}
            greska={greskeForme.password}
            obavezno
          />
          <Input
            naziv="password_confirmation"
            labela="Potvrda lozinke"
            tip="password"
            vrednost={forma.password_confirmation}
            onChange={(e) => setForma({ ...forma, password_confirmation: e.target.value })}
            obavezno
          />
          <div className="mb-3">
            <span className="form-label d-block">
              Uloge <span className="text-danger">*</span>
            </span>
            {greskeForme.uloge && (
              <div className="text-danger small mb-1">{greskeForme.uloge}</div>
            )}
            {SVE_ULOGE.map((uloga) => (
              <div className="form-check" key={uloga.id}>
                <input
                  className="form-check-input"
                  type="checkbox"
                  id={`nova-uloga-${uloga.id}`}
                  checked={forma.uloge.includes(uloga.id)}
                  onChange={() => prebaciUloguNaFormi(uloga.id)}
                />
                <label className="form-check-label" htmlFor={`nova-uloga-${uloga.id}`}>
                  {uloga.naziv}
                </label>
              </div>
            ))}
            <div className="form-text">
              Korisnik mora imati bar jednu ulogu. Ulogu bira pri prijavi.
            </div>
          </div>
          <Input
            naziv="Biografija"
            labela="Biografija"
            tip="textarea"
            vrednost={forma.Biografija}
            onChange={(e) => setForma({ ...forma, Biografija: e.target.value })}
          />
        </form>
      </Modal>

      <Modal
        naslov={`Izmena korisnika — ${izmenaKorisnik?.imePrezime ?? ''}`}
        otvoren={Boolean(izmenaKorisnik)}
        naZatvaranje={() => (cuva ? null : setIzmenaKorisnik(null))}
        zatvaranjeKlikomVan={!cuva}
        podnozje={
          <>
            <Button
              varijanta="outline"
              onClick={() => setIzmenaKorisnik(null)}
              onemoguceno={cuva}
            >
              Odustani
            </Button>
            <Button ucitava={cuva} onClick={sacuvajIzmenu}>
              Sačuvaj
            </Button>
          </>
        }
      >
        <Input
          naziv="izmenaImePrezime"
          labela="Ime i prezime"
          vrednost={izmena.ImePrezime}
          onChange={(e) => setIzmena({ ...izmena, ImePrezime: e.target.value })}
          greska={greskeForme.ImePrezime}
          obavezno
        />

        <Input
          naziv="izmenaEmail"
          labela="Email"
          tip="email"
          vrednost={izmena.email}
          onChange={(e) => setIzmena({ ...izmena, email: e.target.value })}
          greska={greskeForme.email}
          obavezno
        />

        <Input
          naziv="izmenaBiografija"
          labela="Biografija"
          tip="textarea"
          vrednost={izmena.Biografija}
          onChange={(e) => setIzmena({ ...izmena, Biografija: e.target.value })}
          greska={greskeForme.Biografija}
        />

        <Input
          naziv="izmenaPassword"
          labela="Nova lozinka (ostavite prazno da ostane ista)"
          tip="password"
          vrednost={izmena.password}
          onChange={(e) => setIzmena({ ...izmena, password: e.target.value })}
          greska={greskeForme.password}
        />

        {izmena.password && (
          <Input
            naziv="izmenaPasswordPotvrda"
            labela="Potvrda nove lozinke"
            tip="password"
            vrednost={izmena.password_confirmation}
            onChange={(e) => setIzmena({ ...izmena, password_confirmation: e.target.value })}
          />
        )}

        <Poruka vrsta="info" dodatneKlase="mb-0">
          Uloge se menjaju posebno, dugmetom „Uloge".
        </Poruka>
      </Modal>

      <Modal
        naslov={`Uloge — ${ulogeKorisnik?.imePrezime ?? ''}`}
        otvoren={Boolean(ulogeKorisnik)}
        naZatvaranje={() => setUlogeKorisnik(null)}
        podnozje={
          <>
            <Button varijanta="outline" onClick={() => setUlogeKorisnik(null)}>
              Odustani
            </Button>
            <Button
              ucitava={cuva}
              onemoguceno={izabraneUloge.length === 0}
              onClick={sacuvajUloge}
            >
              Sačuvaj uloge
            </Button>
          </>
        }
      >
        <p className="text-secondary small">
          Korisnik mora imati bar jednu ulogu. Uloga se bira pri prijavi i tada određuje šta sme da
          radi u toj sesiji.
        </p>

        {SVE_ULOGE.map((uloga) => (
          <div className="form-check" key={uloga.id}>
            <input
              className="form-check-input"
              type="checkbox"
              id={`uloga-${uloga.id}`}
              checked={izabraneUloge.includes(uloga.id)}
              onChange={() => prebaciUlogu(uloga.id)}
            />
            <label className="form-check-label" htmlFor={`uloga-${uloga.id}`}>
              {uloga.naziv}
            </label>
          </div>
        ))}
      </Modal>

      <PotvrdaBrisanja
        otvoren={Boolean(zaBrisanje)}
        naslov="Brisanje korisnika"
        poruka="Nalog će biti trajno uklonjen iz sistema."
        stavka={zaBrisanje?.imePrezime}
        ucitava={brise}
        naPotvrdu={obrisiKorisnika}
        naOtkaz={() => setZaBrisanje(null)}
      />
    </div>
  )
}
