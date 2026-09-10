import { useEffect, useState } from 'react'
import { Link, useNavigate, useParams } from 'react-router-dom'
import api from '../../api/axios'
import Breadcrumbs from '../../components/Breadcrumbs/Breadcrumbs'
import StatusBedz from '../../components/StatusBedz/StatusBedz'
import Poruka from '../../components/Poruka/Poruka'
import Ucitavanje from '../../components/Ucitavanje/Ucitavanje'
import Button from '../../components/Button/Button'

function prikaziAutore(rad) {
  if (rad?.spoljniAutori) return rad.spoljniAutori
  if (Array.isArray(rad?.autori) && rad.autori.length > 0) return rad.autori.join(', ')
  return 'Autori nisu navedeni'
}

export default function RadDetalji() {
  const { id } = useParams()
  const navigate = useNavigate()

  const [rad, setRad] = useState(null)
  const [ucitava, setUcitava] = useState(true)
  const [greska, setGreska] = useState(null)

  const [citati, setCitati] = useState(null)
  const [spoljni, setSpoljni] = useState(null)
  const [spoljniPoruka, setSpoljniPoruka] = useState(null)
  const [srodni, setSrodni] = useState(null)
  const [istorija, setIstorija] = useState([])

  useEffect(() => {
    let otkazano = false

    async function ucitaj() {
      setUcitava(true)
      setGreska(null)

      try {
        const { data } = await api.get(`/radovi/${id}`)
        if (!otkazano) setRad(data?.data ?? data)
      } catch (error) {
        if (!otkazano) {
          setGreska(
            error.response?.status === 404
              ? 'Objavljen rad sa ovim brojem ne postoji.'
              : 'Podaci o radu trenutno nisu dostupni.'
          )
        }
      } finally {
        if (!otkazano) setUcitava(false)
      }
    }

    ucitaj()
    return () => {
      otkazano = true
    }
  }, [id])

  useEffect(() => {
    let otkazano = false

    api
      .get(`/radovi/${id}/citati`)
      .then(({ data }) => !otkazano && setCitati(data))
      .catch(() => !otkazano && setCitati(null))

    api
      .get(`/radovi/${id}/spoljni-citati`)
      .then(({ data }) => !otkazano && setSpoljni(data?.crossRef ?? null))
      .catch((error) => {
        if (otkazano) return
        setSpoljni(null)
        setSpoljniPoruka(
          error.response?.status === 422
            ? 'Ovaj rad još nema dodeljen DOI, pa se citiranost ne može proveriti kod spoljnih servisa.'
            : 'Podaci o citiranosti trenutno nisu dostupni.'
        )
      })

    api
      .get(`/radovi/${id}/srodni-radovi`)
      .then(({ data }) => !otkazano && setSrodni(data?.srodni ?? null))
      .catch(() => !otkazano && setSrodni(null))

    api
      .get(`/radovi/${id}/istorija-citiranosti`)
      .then(({ data }) => !otkazano && setIstorija(data?.merenja ?? []))
      .catch(() => !otkazano && setIstorija([]))

    return () => {
      otkazano = true
    }
  }, [id])

  if (ucitava) {
    return (
      <div className="container py-4">
        <Ucitavanje tekst="Učitavanje rada..." />
      </div>
    )
  }

  if (greska) {
    return (
      <div className="container py-4">
        <Breadcrumbs
          putanja={[
            { naziv: 'Početna', ka: '/radovi' },
            { naziv: 'Rad nije pronađen' },
          ]}
        />
        <Poruka vrsta="greska">{greska}</Poruka>
        <Button varijanta="outline" onClick={() => navigate('/radovi')}>
          Nazad na listu radova
        </Button>
      </div>
    )
  }

  return (
    <div className="container py-4">
      <Breadcrumbs
        putanja={[
          { naziv: 'Početna', ka: '/radovi' },
          { naziv: 'Objavljeni radovi', ka: '/radovi' },
          { naziv: rad?.naslov ?? 'Detalji rada' },
        ]}
      />

      <div className="row g-4">
        <div className="col-12 col-lg-8">
          <div className="card shadow-sm mb-4">
            <div className="card-body">
              <div className="d-flex justify-content-between align-items-start gap-3 mb-2">
                <h3 className="mb-0">{rad?.naslov}</h3>
                <StatusBedz status={rad?.status} />
              </div>

              <p className="text-secondary mb-3">
                {prikaziAutore(rad)}
                {rad?.godina && <span className="ms-2">· {rad.godina}</span>}
              </p>

              {Array.isArray(rad?.oblasti) && rad.oblasti.length > 0 && (
                <div className="d-flex flex-wrap gap-1 mb-3">
                  {rad.oblasti.map((oblast) => (
                    <span key={oblast} className="badge bg-light text-dark border">
                      {oblast}
                    </span>
                  ))}
                </div>
              )}

              <h6 className="text-uppercase text-secondary small">Apstrakt</h6>
              <p>{rad?.abstrakt}</p>

              {rad?.kljucneReci && (
                <>
                  <h6 className="text-uppercase text-secondary small mt-4">Ključne reči</h6>
                  <p className="mb-0">{rad.kljucneReci}</p>
                </>
              )}

              {rad?.doi && (
                <>
                  <h6 className="text-uppercase text-secondary small mt-4">DOI</h6>
                  <a
                    href={`https://doi.org/${rad.doi}`}
                    target="_blank"
                    rel="noreferrer"
                    className="mb-0"
                  >
                    {rad.doi}
                  </a>
                </>
              )}
            </div>
          </div>

          <div className="card shadow-sm">
            <div className="card-body">
              <h5 className="card-title mb-3">Srodni radovi u svetu</h5>
              <p className="text-secondary small">
                Preuzeto sa servisa OpenAlex, na osnovu ključnih reči ovog rada.
              </p>

              {srodni?.dostupno && srodni.radovi?.length > 0 ? (
                <ul className="list-group list-group-flush">
                  {srodni.radovi.map((srodan, indeks) => (
                    <li key={srodan.doi ?? indeks} className="list-group-item px-0">
                      <div className="fw-semibold">{srodan.naslov}</div>
                      <div className="text-secondary small">
                        {srodan.autori || 'Autori nisu navedeni'}
                        {srodan.godina && <span className="ms-2">· {srodan.godina}</span>}
                        {typeof srodan.brojCitata === 'number' && (
                          <span className="ms-2">· {srodan.brojCitata} citata</span>
                        )}
                      </div>
                      {srodan.doi && (
                        <a
                          href={`https://doi.org/${srodan.doi}`}
                          target="_blank"
                          rel="noreferrer"
                          className="small"
                        >
                          {srodan.doi}
                        </a>
                      )}
                    </li>
                  ))}
                </ul>
              ) : (
                <Poruka vrsta="prazno">
                  Za ključne reči ovog rada nisu pronađeni srodni radovi.
                </Poruka>
              )}
            </div>
          </div>
        </div>

        <div className="col-12 col-lg-4">
          <div className="card shadow-sm mb-4">
            <div className="card-body">
              <h5 className="card-title mb-3">Citiranost u svetu</h5>

              {spoljni?.dostupno ? (
                <>
                  <div className="display-6 mb-1">{spoljni.brojCitata}</div>
                  <p className="text-secondary small mb-3">
                    puta citiran prema servisu CrossRef
                  </p>
                  <dl className="row small mb-0">
                    {spoljni.casopis && (
                      <>
                        <dt className="col-5 text-secondary">Časopis</dt>
                        <dd className="col-7">{spoljni.casopis}</dd>
                      </>
                    )}
                    {spoljni.izdavac && (
                      <>
                        <dt className="col-5 text-secondary">Izdavač</dt>
                        <dd className="col-7">{spoljni.izdavac}</dd>
                      </>
                    )}
                  </dl>

                  {istorija.length > 0 && (
                    <div className="mt-3 pt-3 border-top">
                      <h6 className="text-uppercase text-secondary small">Istorija merenja</h6>
                      <ul className="list-unstyled small mb-0">
                        {istorija.map((merenje) => (
                          <li
                            key={merenje.datum}
                            className="d-flex justify-content-between border-bottom py-1"
                          >
                            <span className="text-secondary">{merenje.datum}</span>
                            <span className="fw-semibold">{merenje.brojCitata}</span>
                          </li>
                        ))}
                      </ul>
                      {istorija.length === 1 && (
                        <p className="text-secondary small mt-2 mb-0">
                          Sistem beleži citiranost pri svakom osvežavanju podataka sa CrossRef-a, pa
                          se niz merenja popunjava vremenom.
                        </p>
                      )}
                    </div>
                  )}
                </>
              ) : (
                <Poruka vrsta="prazno">
                  {spoljniPoruka ?? 'Podaci o citiranosti trenutno nisu dostupni.'}
                </Poruka>
              )}
            </div>
          </div>

          <div className="card shadow-sm mb-4">
            <div className="card-body">
              <h5 className="card-title mb-1">Citiran u laboratoriji</h5>
              <p className="text-secondary small">
                {citati?.brojCitata ?? 0} radova iz sistema citira ovaj rad
              </p>

              {citati?.citiranOdStrane?.length > 0 ? (
                <ul className="list-unstyled mb-0">
                  {citati.citiranOdStrane.map((drugi) => (
                    <li key={drugi.id} className="mb-2">
                      <Link to={`/radovi/${drugi.id}`}>{drugi.naslov}</Link>
                      <span className="text-secondary small ms-1">({drugi.godina})</span>
                    </li>
                  ))}
                </ul>
              ) : (
                <p className="text-secondary small mb-0">Nijedan rad iz sistema ga još ne citira.</p>
              )}
            </div>
          </div>

          <div className="card shadow-sm">
            <div className="card-body">
              <h5 className="card-title mb-1">Reference ovog rada</h5>
              <p className="text-secondary small">
                Ovaj rad citira {citati?.brojReferenci ?? 0} radova iz sistema
              </p>

              {citati?.citira?.length > 0 ? (
                <ul className="list-unstyled mb-0">
                  {citati.citira.map((drugi) => (
                    <li key={drugi.id} className="mb-2">
                      <Link to={`/radovi/${drugi.id}`}>{drugi.naslov}</Link>
                      <span className="text-secondary small ms-1">({drugi.godina})</span>
                    </li>
                  ))}
                </ul>
              ) : (
                <p className="text-secondary small mb-0">Nema referenci ka radovima iz sistema.</p>
              )}
            </div>
          </div>
        </div>
      </div>
    </div>
  )
}
