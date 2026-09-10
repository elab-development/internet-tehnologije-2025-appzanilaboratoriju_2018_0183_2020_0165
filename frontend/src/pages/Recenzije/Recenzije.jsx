import { useState } from 'react'
import api from '../../api/axios'
import usePaginatedFetch from '../../hooks/usePaginatedFetch'
import Modal from '../../components/Modal/Modal'
import Input from '../../components/Input/Input'
import Select from '../../components/Select/Select'
import Button from '../../components/Button/Button'
import StatusBedz from '../../components/StatusBedz/StatusBedz'
import Pagination from '../../components/Pagination/Pagination'
import Poruka from '../../components/Poruka/Poruka'
import Ucitavanje from '../../components/Ucitavanje/Ucitavanje'

const PO_STRANI = 5

const ISHODI_RECENZIJE = [
  { vrednost: '3', tekst: 'Objavljen' },
  { vrednost: '4', tekst: 'Odbijen' },
  { vrednost: '1', tekst: 'Vraćen na doradu' },
]

const PRAZNA_OCENA = { StatusID: '', Komentar: '' }

function prikaziAutore(rad) {
  if (rad?.spoljniAutori) {
    return rad.spoljniAutori
  }

  if (Array.isArray(rad?.autori) && rad.autori.length > 0) {
    return rad.autori.join(', ')
  }

  return 'Autori nisu navedeni'
}

function formatirajDatum(vrednost) {
  if (!vrednost) {
    return ''
  }

  const datum = new Date(vrednost)

  return Number.isNaN(datum.getTime()) ? vrednost : datum.toLocaleDateString('sr-RS')
}

export default function Recenzije() {
  const [recenzija, setRecenzija] = useState(null)
  const [ocena, setOcena] = useState(PRAZNA_OCENA)
  const [greskeForme, setGreskeForme] = useState({})
  const [cuva, setCuva] = useState(false)
  const [obavestenje, setObavestenje] = useState(null)

  const { podaci, ucitava, greska, strana, ukupnoStrana, ukupnoStavki, promeniStranu, osvezi } =
    usePaginatedFetch('/recenzije/moje', {}, PO_STRANI)

  const otvoriOcenjivanje = (stavka) => {
    setRecenzija(stavka)
    setOcena(PRAZNA_OCENA)
    setGreskeForme({})
  }

  const zatvoriOcenjivanje = () => {
    if (!cuva) {
      setRecenzija(null)
    }
  }

  const sacuvajOcenu = async (dogadjaj) => {
    dogadjaj.preventDefault()
    setCuva(true)
    setGreskeForme({})

    try {
      await api.post(`/recenzije/${recenzija.id}/stavke`, {
        StatusID: Number(ocena.StatusID),
        Komentar: ocena.Komentar,
      })

      setRecenzija(null)
      setOcena(PRAZNA_OCENA)
      setObavestenje({
        vrsta: 'uspeh',
        tekst: 'Ocena je sačuvana i status rada je ažuriran.',
      })
      osvezi()
    } catch (error) {
      const greske = error.response?.data?.errors

      if (greske) {
        setGreskeForme(Object.fromEntries(Object.entries(greske).map(([k, v]) => [k, v[0]])))
      } else {
        setObavestenje({
          vrsta: 'greska',
          tekst: error.response?.data?.message ?? 'Čuvanje ocene nije uspelo.',
        })
      }
    } finally {
      setCuva(false)
    }
  }

  if (ucitava) {
    return (
      <div className="container py-4">
        <h2 className="mb-3">Radovi dodeljeni na recenziju</h2>
        <Ucitavanje tekst="Učitavanje dodeljenih radova..." />
      </div>
    )
  }

  return (
    <div className="container py-4">
      <h2 className="mb-1">Radovi dodeljeni na recenziju</h2>
      <p className="text-secondary">
        {ukupnoStavki === 0 ? 'Nemate dodeljenih radova.' : `Ukupno dodeljeno: ${ukupnoStavki}`}
      </p>

      {obavestenje && (
        <Poruka vrsta={obavestenje.vrsta} dodatneKlase="mb-3">
          {obavestenje.tekst}
        </Poruka>
      )}

      {greska && <Poruka vrsta="greska">{greska}</Poruka>}

      {!greska && ukupnoStavki === 0 && (
        <Poruka vrsta="prazno">
          Kada vam administrator ili sistem dodeli rad na recenziju, pojaviće se ovde.
        </Poruka>
      )}

      <div className="d-flex flex-column gap-3">
        {podaci.map((stavka) => {
          const rad = stavka.naucniRad
          const istorija = stavka.stavke ?? []

          return (
            <div className="card shadow-sm" key={stavka.id}>
              <div className="card-body">
                <div className="d-flex justify-content-between align-items-start gap-2 mb-2">
                  <h5 className="card-title mb-0">{rad?.naslov ?? 'Rad nije dostupan'}</h5>
                  <StatusBedz status={rad?.status} />
                </div>

                <p className="text-secondary small mb-2">
                  {prikaziAutore(rad)}
                  {rad?.godina && <span className="ms-2">· {rad.godina}</span>}
                  <span className="ms-2">· dodeljeno {formatirajDatum(stavka.datumDodele)}</span>
                </p>

                {rad?.abstrakt && <p className="card-text small">{rad.abstrakt}</p>}

                {Array.isArray(rad?.oblasti) && rad.oblasti.length > 0 && (
                  <div className="d-flex flex-wrap gap-1 mb-3">
                    {rad.oblasti.map((oblast) => (
                      <span key={oblast} className="badge bg-light text-dark border">
                        {oblast}
                      </span>
                    ))}
                  </div>
                )}

                {istorija.length > 0 && (
                  <div className="border-top pt-3 mt-2">
                    <h6 className="text-secondary small text-uppercase mb-2">
                      Istorija ocena ({istorija.length})
                    </h6>

                    <ul className="list-unstyled mb-0">
                      {istorija.map((ocenaIzIstorije) => (
                        <li key={ocenaIzIstorije.id} className="mb-2">
                          <div className="d-flex align-items-center gap-2 mb-1">
                            <StatusBedz status={ocenaIzIstorije.status} />
                            <span className="text-secondary small">
                              {formatirajDatum(ocenaIzIstorije.datum)}
                            </span>
                          </div>
                          <p className="small mb-0">{ocenaIzIstorije.komentar}</p>
                        </li>
                      ))}
                    </ul>
                  </div>
                )}

                <div className="mt-3">
                  <Button onClick={() => otvoriOcenjivanje(stavka)}>Oceni rad</Button>
                </div>
              </div>
            </div>
          )
        })}
      </div>

      <div className="mt-4">
        <Pagination
          trenutnaStrana={strana}
          ukupnoStrana={ukupnoStrana}
          naPromenu={promeniStranu}
        />
      </div>

      <Modal
        naslov="Ocena rada"
        otvoren={recenzija !== null}
        naZatvaranje={zatvoriOcenjivanje}
        zatvaranjeKlikomVan={!cuva}
        velicina="veliko"
        podnozje={
          <>
            <Button varijanta="outline" onClick={zatvoriOcenjivanje} onemoguceno={cuva}>
              Odustani
            </Button>
            <Button tip="submit" ucitava={cuva} onClick={sacuvajOcenu}>
              Sačuvaj ocenu
            </Button>
          </>
        }
      >
        <p className="fw-semibold mb-3">{recenzija?.naucniRad?.naslov}</p>

        <form onSubmit={sacuvajOcenu}>
          <Select
            naziv="StatusID"
            labela="Novi status rada"
            vrednost={ocena.StatusID}
            onChange={(dogadjaj) => setOcena({ ...ocena, StatusID: dogadjaj.target.value })}
            opcije={ISHODI_RECENZIJE}
            prazanTekst="Izaberite ishod recenzije"
            greska={greskeForme.StatusID}
            obavezno
          />

          <Input
            naziv="Komentar"
            labela="Komentar recenzenta"
            tip="textarea"
            vrednost={ocena.Komentar}
            onChange={(dogadjaj) => setOcena({ ...ocena, Komentar: dogadjaj.target.value })}
            greska={greskeForme.Komentar}
            placeholder="Obrazloženje ocene, najmanje 10 karaktera"
            obavezno
          />
        </form>

        <Poruka vrsta="info" dodatneKlase="mb-0">
          Svaka ocena se čuva kao nova stavka recenzije. Ranije ocene se ne menjaju ni brišu.
        </Poruka>
      </Modal>
    </div>
  )
}
