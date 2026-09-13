import { useMemo, useState } from 'react'
import { Link } from 'react-router-dom'
import api from '../../api/axios'
import usePaginatedFetch from '../../hooks/usePaginatedFetch'
import Modal from '../../components/Modal/Modal'
import Input from '../../components/Input/Input'
import Select from '../../components/Select/Select'
import Button from '../../components/Button/Button'
import StatusBedz from '../../components/StatusBedz/StatusBedz'
import Poruka from '../../components/Poruka/Poruka'
import Ucitavanje from '../../components/Ucitavanje/Ucitavanje'
import AutoriRada from '../../components/AutoriRada/AutoriRada'
import PdfPregled from '../../components/PdfPregled/PdfPregled'

const OBJAVLJEN = 'Objavljen'
const CEKA_RECENZIJU = 'Čeka recenziju'
const NACRT = 'Nacrt'

const PO_STRANI = 5

const ISHODI_RECENZIJE = [
  { vrednost: '3', tekst: 'Objavljen' },
  { vrednost: '4', tekst: 'Odbijen' },
  { vrednost: '1', tekst: 'Vraćen na doradu' },
]

const PRAZNA_OCENA = { StatusID: '', Komentar: '' }

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
  const [pdfRad, setPdfRad] = useState(null)
  const [verzijeRada, setVerzijeRada] = useState(null)
  const [verzije, setVerzije] = useState([])

  const { sviPodaci, ucitava, greska, ukupnoStavki, osvezi } =
    usePaginatedFetch('/recenzije/moje', {}, PO_STRANI)

  const zaOcenu = useMemo(
    () => sviPodaci.filter((stavka) => stavka.naucniRad?.status === CEKA_RECENZIJU),
    [sviPodaci]
  )

  const zavrsene = useMemo(
    () => sviPodaci.filter((stavka) => stavka.naucniRad?.status !== CEKA_RECENZIJU),
    [sviPodaci]
  )

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

  const prikaziVerzije = async (rad) => {
    setVerzijeRada(rad)
    setVerzije([])

    try {
      const { data } = await api.get(`/radovi/${rad.id}/verzije`)
      setVerzije(data?.verzije ?? [])
    } catch {
      setVerzije([])
    }
  }

  const porukaZaZavrsenu = (status) => {
    if (status === OBJAVLJEN) {
      return 'Rad je objavljen. Recenzija je završena.'
    }

    if (status === NACRT) {
      return 'Vraćen autoru na doradu. Kada ga ponovo preda, vratiće se u spisak za ocenu.'
    }

    return 'Rad je odbijen. Ako ga autor ispravi i ponovo preda, vratiće se u spisak za ocenu.'
  }

  const karticaRecenzije = (stavka) => {
    const rad = stavka.naucniRad
    const istorija = stavka.stavke ?? []
    const cekaOcenu = rad?.status === CEKA_RECENZIJU

    return (
      <div className="card shadow-sm" key={stavka.id}>
        <div className="card-body">
          <div className="d-flex justify-content-between align-items-start gap-2 mb-2">
            <h5 className="card-title mb-0">
              {rad?.status === OBJAVLJEN ? (
                <Link to={`/radovi/${rad.id}`} className="text-decoration-none">
                  {rad.naslov}
                </Link>
              ) : (
                (rad?.naslov ?? 'Rad nije dostupan')
              )}
            </h5>
            <StatusBedz status={rad?.status} />
          </div>

          <p className="text-secondary small mb-2">
            <AutoriRada rad={rad} />
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

          <div className="mt-3 d-flex flex-wrap gap-2">
            {cekaOcenu ? (
              <Button onClick={() => otvoriOcenjivanje(stavka)}>Oceni rad</Button>
            ) : (
              <span className="text-secondary small align-self-center">
                {porukaZaZavrsenu(rad?.status)}
              </span>
            )}

            {rad?.imaFajl ? (
              <Button varijanta="outline" onClick={() => setPdfRad(rad)}>
                Pročitaj rad (PDF)
              </Button>
            ) : (
              <span className="text-secondary small align-self-center">
                Autor nije priložio PDF
              </span>
            )}

            {rad && (
              <Button varijanta="outline" onClick={() => prikaziVerzije(rad)}>
                Sve verzije
              </Button>
            )}
          </div>
        </div>
      </div>
    )
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
        {ukupnoStavki === 0
          ? 'Nemate dodeljenih radova.'
          : `Čeka vašu ocenu: ${zaOcenu.length} · završeno: ${zavrsene.length}`}
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

      {zaOcenu.length > 0 && (
        <>
          <h5 className="mt-4 mb-3">Čeka vašu ocenu ({zaOcenu.length})</h5>
          <div className="d-flex flex-column gap-3">{zaOcenu.map(karticaRecenzije)}</div>
        </>
      )}

      {zavrsene.length > 0 && (
        <>
          <h5 className="mt-5 mb-1">Završene recenzije ({zavrsene.length})</h5>
          <p className="text-secondary small">
            Ovi radovi ne čekaju vašu odluku. Rad vraćen na doradu vratiće se gore kada ga autor
            ponovo preda.
          </p>
          <div className="d-flex flex-column gap-3">{zavrsene.map(karticaRecenzije)}</div>
        </>
      )}

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
            prazanKaoPlaceholder
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

      <Modal
        naslov={`Verzije rada — ${verzijeRada?.naslov ?? ''}`}
        otvoren={Boolean(verzijeRada)}
        naZatvaranje={() => setVerzijeRada(null)}
        podnozje={
          <Button varijanta="outline" onClick={() => setVerzijeRada(null)}>
            Zatvori
          </Button>
        }
      >
        {verzije.length === 0 ? (
          <Poruka vrsta="prazno" dodatneKlase="mb-0">
            Ovaj rad ima samo jednu verziju.
          </Poruka>
        ) : (
          <ul className="list-unstyled mb-0">
            {verzije.map((verzija) => (
              <li
                key={verzija.id}
                className="d-flex justify-content-between align-items-center border-bottom py-2"
              >
                <div>
                  <span className="fw-semibold">verzija {verzija.verzija}</span>
                  <div className="text-secondary small">
                    {verzija.naslov} · {verzija.godina}
                  </div>
                </div>
                <StatusBedz status={verzija.status} />
              </li>
            ))}
          </ul>
        )}
      </Modal>

      <PdfPregled rad={pdfRad} naZatvaranje={() => setPdfRad(null)} />
    </div>
  )
}
