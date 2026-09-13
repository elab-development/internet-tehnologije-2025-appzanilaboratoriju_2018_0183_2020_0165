import { useEffect, useMemo, useState } from 'react'
import { Link } from 'react-router-dom'
import api from '../../api/axios'
import usePaginatedFetch from '../../hooks/usePaginatedFetch'
import useAuth from '../../hooks/useAuth'
import Modal from '../../components/Modal/Modal'
import Input from '../../components/Input/Input'
import Select from '../../components/Select/Select'
import Button from '../../components/Button/Button'
import StatusBedz from '../../components/StatusBedz/StatusBedz'
import Pagination from '../../components/Pagination/Pagination'
import Poruka from '../../components/Poruka/Poruka'
import Ucitavanje from '../../components/Ucitavanje/Ucitavanje'
import PdfPregled from '../../components/PdfPregled/PdfPregled'
import AutoriRada from '../../components/AutoriRada/AutoriRada'

const PO_STRANI = 5

const PRAZNA_FORMA = {
  naslov: '',
  abstrakt: '',
  kljucneReci: '',
  godina: String(new Date().getFullYear()),
  oblasti: [],
  autori: [],
  reference: [],
  fajl: null,
}

const NACRT = 'Nacrt'
const OBJAVLJEN = 'Objavljen'
const ODBIJEN = 'Odbijen'
const CEKA_RECENZIJU = 'Čeka recenziju'
const CEKA_RECENZIJU_ID = 2

export default function MojiRadovi() {
  const { korisnik } = useAuth()
  const [oblasti, setOblasti] = useState([])
  const [istrazivaci, setIstrazivaci] = useState([])
  const [objavljeniRadovi, setObjavljeniRadovi] = useState([])
  const [obavestenje, setObavestenje] = useState(null)

  const [forma, setForma] = useState(PRAZNA_FORMA)
  const [formaOtvorena, setFormaOtvorena] = useState(false)
  const [radKojiSeMenja, setRadKojiSeMenja] = useState(null)
  const [radZaNovuVerziju, setRadZaNovuVerziju] = useState(null)
  const [greskeForme, setGreskeForme] = useState({})
  const [cuva, setCuva] = useState(false)

  const [verzijeRada, setVerzijeRada] = useState(null)
  const [pdfRad, setPdfRad] = useState(null)
  const [verzije, setVerzije] = useState([])

  const { podaci, ucitava, greska, strana, ukupnoStrana, ukupnoStavki, promeniStranu, osvezi } =
    usePaginatedFetch('/radovi/moji', {}, PO_STRANI)

  useEffect(() => {
    api
      .get('/oblasti')
      .then(({ data }) => setOblasti(Array.isArray(data) ? data : (data?.podaci ?? [])))
      .catch(() => setOblasti([]))

    api
      .get('/istrazivaci')
      .then(({ data }) => setIstrazivaci(data?.podaci ?? []))
      .catch(() => setIstrazivaci([]))

    api
      .get('/radovi/objavljeni')
      .then(({ data }) => setObjavljeniRadovi(data?.data ?? data?.podaci ?? []))
      .catch(() => setObjavljeniRadovi([]))
  }, [])

  const naslovForme = useMemo(() => {
    if (radKojiSeMenja) {
      return radKojiSeMenja.status === ODBIJEN
        ? `Ispravka odbijenog rada — ${radKojiSeMenja.naslov}`
        : `Izmena nacrta — ${radKojiSeMenja.naslov}`
    }
    if (radZaNovuVerziju) return `Nova verzija — ${radZaNovuVerziju.naslov}`
    return 'Novi naučni rad'
  }, [radKojiSeMenja, radZaNovuVerziju])

  const zatvoriFormu = () => {
    if (cuva) return
    setFormaOtvorena(false)
    setRadKojiSeMenja(null)
    setRadZaNovuVerziju(null)
    setForma(PRAZNA_FORMA)
    setGreskeForme({})
  }

  const otvoriNoviRad = () => {
    setForma(PRAZNA_FORMA)
    setRadKojiSeMenja(null)
    setRadZaNovuVerziju(null)
    setGreskeForme({})
    setFormaOtvorena(true)
  }

  const otvoriIzmenu = (rad) => {
    setForma({
      naslov: rad.naslov ?? '',
      abstrakt: rad.abstrakt ?? '',
      kljucneReci: rad.kljucneReci ?? '',
      godina: String(rad.godina ?? new Date().getFullYear()),
      oblasti: [],
      autori: [],
      reference: (rad.reference ?? []).map((citirani) => citirani.id),
      fajl: null,
    })
    setRadKojiSeMenja(rad)
    setRadZaNovuVerziju(null)
    setGreskeForme({})
    setFormaOtvorena(true)
  }

  const otvoriNovuVerziju = (rad) => {
    setForma({
      naslov: rad.naslov ?? '',
      abstrakt: rad.abstrakt ?? '',
      kljucneReci: rad.kljucneReci ?? '',
      godina: String(new Date().getFullYear()),
      oblasti: [],
      autori: [],
      reference: [],
      fajl: null,
    })
    setRadZaNovuVerziju(rad)
    setRadKojiSeMenja(null)
    setGreskeForme({})
    setFormaOtvorena(true)
  }

  const moguciKoautori = useMemo(
    () =>
      istrazivaci.filter((i) => i.id !== korisnik?.ZapID && !forma.autori.includes(i.id)),
    [istrazivaci, korisnik?.ZapID, forma.autori]
  )

  const moguceReference = useMemo(
    () =>
      objavljeniRadovi.filter(
        (r) => r.id !== radKojiSeMenja?.id && !forma.reference.includes(r.id)
      ),
    [objavljeniRadovi, radKojiSeMenja?.id, forma.reference]
  )

  const prebaciOblast = (idOblasti) => {
    setForma((prethodna) => ({
      ...prethodna,
      oblasti: prethodna.oblasti.includes(idOblasti)
        ? prethodna.oblasti.filter((x) => x !== idOblasti)
        : [...prethodna.oblasti, idOblasti],
    }))
  }

  const prebaciReferencu = (idRada) => {
    setForma((prethodna) => ({
      ...prethodna,
      reference: prethodna.reference.includes(idRada)
        ? prethodna.reference.filter((x) => x !== idRada)
        : [...prethodna.reference, idRada],
    }))
  }

  const prebaciKoautora = (idKoautora) => {
    setForma((prethodna) => {
      if (prethodna.autori.includes(idKoautora)) {
        return { ...prethodna, autori: prethodna.autori.filter((x) => x !== idKoautora) }
      }

      if (prethodna.autori.length >= 2) {
        return prethodna
      }

      return { ...prethodna, autori: [...prethodna.autori, idKoautora] }
    })
  }

  const napraviTeloZahteva = () => {
    const telo = new FormData()
    telo.append('naslov', forma.naslov)
    telo.append('abstrakt', forma.abstrakt)
    telo.append('kljucneReci', forma.kljucneReci)
    telo.append('godina', forma.godina)

    forma.oblasti.forEach((id) => telo.append('oblasti[]', id))
    forma.autori.forEach((id) => telo.append('autori[]', id))
    forma.reference.forEach((id) => telo.append('reference[]', id))

    if (forma.fajl) {
      telo.append('fajl', forma.fajl)
    }

    return telo
  }

  const sacuvaj = async (dogadjaj) => {
    dogadjaj.preventDefault()
    setCuva(true)
    setGreskeForme({})

    try {
      if (radKojiSeMenja) {
        const ponovnaPredaja = radKojiSeMenja.status === ODBIJEN
        const telo = napraviTeloZahteva()
        telo.append('_method', 'PUT')

        if (ponovnaPredaja) {
          telo.append('StatusID', CEKA_RECENZIJU_ID)
        }

        await api.post(`/radovi/${radKojiSeMenja.id}`, telo)
        setObavestenje({
          vrsta: 'uspeh',
          tekst: ponovnaPredaja
            ? 'Rad je ispravljen i ponovo poslat na recenziju.'
            : 'Nacrt je izmenjen.',
        })
      } else if (radZaNovuVerziju) {
        await api.post(`/radovi/${radZaNovuVerziju.id}/verzija`, napraviTeloZahteva())
        setObavestenje({
          vrsta: 'uspeh',
          tekst: 'Nova verzija je kreirana i poslata na recenziju.',
        })
      } else {
        await api.post('/radovi', napraviTeloZahteva())
        setObavestenje({ vrsta: 'uspeh', tekst: 'Rad je predat i dodeljen recenzentu.' })
      }

      zatvoriFormu()
      osvezi()
    } catch (error) {
      const greske = error.response?.data?.errors

      if (greske) {
        setGreskeForme(
          Object.fromEntries(Object.entries(greske).map(([polje, tekst]) => [polje, tekst[0]]))
        )
      } else {
        setObavestenje({
          vrsta: 'greska',
          tekst:
            error.response?.data?.message ??
            error.response?.data?.error ??
            'Čuvanje rada nije uspelo.',
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


  if (ucitava) {
    return (
      <div className="container py-4">
        <h2 className="mb-3">Moji radovi</h2>
        <Ucitavanje tekst="Učitavanje radova..." />
      </div>
    )
  }

  return (
    <div className="container py-4">
      <div className="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
        <div>
          <h2 className="mb-1">Moji radovi</h2>
          <p className="text-secondary mb-0">
            {ukupnoStavki === 0
              ? 'Još nemate predatih radova.'
              : `Ukupno radova: ${ukupnoStavki}`}
          </p>
        </div>

        <Button onClick={otvoriNoviRad}>Predaj novi rad</Button>
      </div>

      {obavestenje && (
        <Poruka vrsta={obavestenje.vrsta} dodatneKlase="mb-3">
          {obavestenje.tekst}
        </Poruka>
      )}

      {greska && <Poruka vrsta="greska">{greska}</Poruka>}

      {!greska && ukupnoStavki === 0 && (
        <Poruka vrsta="prazno" naslov="Nema radova">
          Predajte prvi rad preko dugmeta iznad. Sistem će mu automatski dodeliti recenzenta.
        </Poruka>
      )}

      {!greska && podaci.length > 0 && (
        <>
          <div className="row g-3">
            {podaci.map((rad) => (
              <div className="col-12" key={rad.id}>
                <div className="card shadow-sm">
                  <div className="card-body">
                    <div className="d-flex justify-content-between align-items-start gap-3 mb-2">
                      <h5 className="mb-0">
                        {rad.status === OBJAVLJEN ? (
                          <Link to={`/radovi/${rad.id}`} className="text-decoration-none">
                            {rad.naslov}
                          </Link>
                        ) : (
                          rad.naslov
                        )}
                      </h5>
                      <div className="d-flex align-items-center gap-2">
                        {rad.verzija > 1 && (
                          <span className="badge bg-light text-dark border">
                            verzija {rad.verzija}
                          </span>
                        )}
                        <StatusBedz status={rad.status} />
                      </div>
                    </div>

                    <p className="text-secondary small mb-2">
                      <AutoriRada rad={rad} />
                      {rad.godina && <span className="ms-2">· {rad.godina}</span>}
                    </p>

                    {rad.status === CEKA_RECENZIJU && (
                      <p className="text-secondary small mb-2">
                        {rad.recenzenti?.length > 0 ? (
                          <>
                            Recenzent:{' '}
                            {rad.recenzenti.map((recenzent, indeks) => (
                              <span key={recenzent.id}>
                                {indeks > 0 && ', '}
                                {recenzent.imePrezime}
                              </span>
                            ))}
                          </>
                        ) : (
                          'Recenzent još nije dodeljen.'
                        )}
                      </p>
                    )}

                    <p className="small mb-3">{rad.abstrakt}</p>

                    {Array.isArray(rad.oblasti) && rad.oblasti.length > 0 && (
                      <div className="d-flex flex-wrap gap-1 mb-3">
                        {rad.oblasti.map((oblast) => (
                          <span key={oblast} className="badge bg-light text-dark border">
                            {oblast}
                          </span>
                        ))}
                      </div>
                    )}

                    <div className="d-flex flex-wrap gap-2">
                      {rad.status === NACRT && (
                        <Button onClick={() => otvoriIzmenu(rad)}>Izmeni nacrt</Button>
                      )}

                      {rad.status === ODBIJEN && (
                        <Button onClick={() => otvoriIzmenu(rad)}>Ispravi i predaj ponovo</Button>
                      )}

                      {rad.status === OBJAVLJEN && (
                        <Button onClick={() => otvoriNovuVerziju(rad)}>Nova verzija</Button>
                      )}

                      <Button varijanta="outline" onClick={() => prikaziVerzije(rad)}>
                        Sve verzije
                      </Button>

                      {rad.imaFajl && (
                        <Button
                          onClick={() => setPdfRad(rad)}
                        >
                          Pročitaj rad (PDF)
                        </Button>
                      )}
                    </div>
                  </div>
                </div>
              </div>
            ))}
          </div>

          <div className="mt-4">
            <Pagination
              trenutnaStrana={strana}
              ukupnoStrana={ukupnoStrana}
              naPromenu={promeniStranu}
            />
          </div>
        </>
      )}

      <Modal
        naslov={naslovForme}
        otvoren={formaOtvorena}
        naZatvaranje={zatvoriFormu}
        velicina="veliko"
        podnozje={
          <>
            <Button varijanta="outline" onClick={zatvoriFormu}>
              Odustani
            </Button>
            <Button ucitava={cuva} onClick={sacuvaj}>
              {radKojiSeMenja
                ? radKojiSeMenja.status === ODBIJEN
                  ? 'Sačuvaj i predaj ponovo'
                  : 'Sačuvaj izmene'
                : 'Predaj rad'}
            </Button>
          </>
        }
      >
        <form onSubmit={sacuvaj}>
          <Input
            naziv="naslov"
            labela="Naslov"
            vrednost={forma.naslov}
            onChange={(e) => setForma({ ...forma, naslov: e.target.value })}
            greska={greskeForme.naslov}
            obavezno
          />

          <Input
            naziv="abstrakt"
            labela="Apstrakt"
            tip="textarea"
            redova={4}
            vrednost={forma.abstrakt}
            onChange={(e) => setForma({ ...forma, abstrakt: e.target.value })}
            greska={greskeForme.abstrakt}
            obavezno
          />

          <Input
            naziv="kljucneReci"
            labela="Ključne reči"
            vrednost={forma.kljucneReci}
            onChange={(e) => setForma({ ...forma, kljucneReci: e.target.value })}
            greska={greskeForme.kljucneReci}
            placeholder="npr. neuronske mreže, klasifikacija"
            obavezno
          />

          <Input
            naziv="godina"
            labela="Godina"
            tip="number"
            vrednost={forma.godina}
            onChange={(e) => setForma({ ...forma, godina: e.target.value })}
            greska={greskeForme.godina}
            obavezno
          />

          <div className="mb-3">
            <label className="form-label" htmlFor="fajl">
              Fajl rada (PDF, najviše 10 MB)
            </label>
            <input
              id="fajl"
              type="file"
              accept="application/pdf"
              className={`form-control ${greskeForme.fajl ? 'is-invalid' : ''}`}
              onChange={(e) => setForma({ ...forma, fajl: e.target.files?.[0] ?? null })}
            />
            {greskeForme.fajl && <div className="invalid-feedback">{greskeForme.fajl}</div>}
            {radKojiSeMenja && (
              <div className="form-text">
                {radKojiSeMenja.imaFajl
                  ? `Trenutni fajl: ${radKojiSeMenja.imeFajla}. Ostavite prazno da ostane isti, ili izaberite novi da ga zamenite.`
                  : 'Ovaj rad još nema priložen PDF.'}
              </div>
            )}
          </div>

          <div className="mb-3">
            <span className="form-label d-block">
              Oblasti {!radKojiSeMenja && <span className="text-danger">*</span>}
            </span>
            {greskeForme.oblasti && (
              <div className="text-danger small mb-1">{greskeForme.oblasti}</div>
            )}
            <div className="d-flex flex-wrap gap-3">
              {oblasti.map((oblast) => (
                <div className="form-check" key={oblast.oblastId}>
                  <input
                    className="form-check-input"
                    type="checkbox"
                    id={`oblast-${oblast.oblastId}`}
                    checked={forma.oblasti.includes(oblast.oblastId)}
                    onChange={() => prebaciOblast(oblast.oblastId)}
                  />
                  <label className="form-check-label" htmlFor={`oblast-${oblast.oblastId}`}>
                    {oblast.naziv}
                  </label>
                </div>
              ))}
            </div>
            {radKojiSeMenja && (
              <div className="form-text">
                Ostavite neizabrano da bi oblasti ostale nepromenjene.
              </div>
            )}
          </div>

          {!radKojiSeMenja && !radZaNovuVerziju && (
            <div className="mb-3">
              <span className="form-label d-block">Koautori (najviše dva)</span>
              {greskeForme.autori && (
                <div className="text-danger small mb-1">{greskeForme.autori}</div>
              )}
              <Select
                naziv="koautor"
                vrednost=""
                onChange={(e) => e.target.value && prebaciKoautora(Number(e.target.value))}
                prazanTekst="Dodaj koautora..."
                prazanKaoPlaceholder
                opcije={moguciKoautori.map((i) => ({ vrednost: i.id, tekst: i.imePrezime }))}
                onemoguceno={forma.autori.length >= 2}
              />
              <div className="d-flex flex-wrap gap-2">
                {forma.autori.map((idKoautora) => {
                  const koautor = istrazivaci.find((i) => i.id === idKoautora)
                  return (
                    <span key={idKoautora} className="badge bg-secondary">
                      {koautor?.imePrezime ?? idKoautora}
                      <button
                        type="button"
                        className="btn-close btn-close-white ms-2"
                        aria-label="Ukloni koautora"
                        onClick={() => prebaciKoautora(idKoautora)}
                      ></button>
                    </span>
                  )
                })}
              </div>
            </div>
          )}

          {!radZaNovuVerziju && (
            <div className="mb-3">
              <span className="form-label d-block">Radovi koje ovaj rad citira</span>
              {greskeForme.reference && (
                <div className="text-danger small mb-1">{greskeForme.reference}</div>
              )}
              <Select
                naziv="referenca"
                vrednost=""
                onChange={(e) => e.target.value && prebaciReferencu(Number(e.target.value))}
                prazanTekst="Dodaj citirani rad..."
                prazanKaoPlaceholder
                opcije={moguceReference.map((r) => ({
                  vrednost: r.id,
                  tekst: `${r.naslov} (${r.godina})`,
                }))}
              />
              <div className="d-flex flex-wrap gap-2">
                {forma.reference.map((idCitiranog) => {
                  const citirani = objavljeniRadovi.find((r) => r.id === idCitiranog)
                  return (
                    <span key={idCitiranog} className="badge bg-secondary">
                      {citirani?.naslov ?? idCitiranog}
                      <button
                        type="button"
                        className="btn-close btn-close-white ms-2"
                        aria-label="Ukloni citirani rad"
                        onClick={() => prebaciReferencu(idCitiranog)}
                      ></button>
                    </span>
                  )
                })}
              </div>
              <div className="form-text">
                Citirati se mogu samo objavljeni radovi.
                {radZaNovuVerziju ? '' : ' Nova verzija nasleđuje reference stare.'}
              </div>
            </div>
          )}
        </form>
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
          <Poruka vrsta="prazno">Ovaj rad ima samo jednu verziju.</Poruka>
        ) : (
          <ul className="list-group list-group-flush">
            {verzije.map((verzija) => (
              <li
                key={verzija.id}
                className="list-group-item px-0 d-flex justify-content-between align-items-center gap-3"
              >
                <div>
                  <div className="fw-semibold">
                    Verzija {verzija.verzija}
                    {verzija.id === verzijeRada?.id && (
                      <span className="text-secondary small ms-2">(trenutno prikazana)</span>
                    )}
                  </div>
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
