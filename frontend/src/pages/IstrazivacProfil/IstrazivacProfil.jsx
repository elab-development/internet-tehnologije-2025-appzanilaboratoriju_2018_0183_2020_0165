import { useEffect, useState } from 'react'
import { Link, useNavigate, useParams } from 'react-router-dom'
import api from '../../api/axios'
import Breadcrumbs from '../../components/Breadcrumbs/Breadcrumbs'
import Poruka from '../../components/Poruka/Poruka'
import Ucitavanje from '../../components/Ucitavanje/Ucitavanje'
import Button from '../../components/Button/Button'

export default function IstrazivacProfil() {
  const { id } = useParams()
  const navigate = useNavigate()

  const [istrazivac, setIstrazivac] = useState(null)
  const [ucitava, setUcitava] = useState(true)
  const [greska, setGreska] = useState(null)

  useEffect(() => {
    let otkazano = false

    async function ucitaj() {
      setUcitava(true)
      setGreska(null)

      try {
        const { data } = await api.get(`/istrazivaci/${id}`)
        if (!otkazano) setIstrazivac(data)
      } catch (error) {
        if (!otkazano) {
          setGreska(
            error.response?.status === 404
              ? 'Istraživač sa ovim brojem ne postoji.'
              : 'Podaci o istraživaču trenutno nisu dostupni.'
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

  if (ucitava) {
    return (
      <div className="container py-4">
        <Ucitavanje tekst="Učitavanje profila..." />
      </div>
    )
  }

  if (greska) {
    return (
      <div className="container py-4">
        <Breadcrumbs
          putanja={[
            { naziv: 'Istraživači', ka: '/istrazivaci' },
            { naziv: 'Profil nije pronađen' },
          ]}
        />
        <Poruka vrsta="greska">{greska}</Poruka>
        <Button varijanta="outline" onClick={() => navigate('/istrazivaci')}>
          Nazad na listu istraživača
        </Button>
      </div>
    )
  }

  const radovi = istrazivac?.radovi ?? []

  return (
    <div className="container py-4">
      <Breadcrumbs
        putanja={[
          { naziv: 'Početna', ka: '/radovi' },
          { naziv: 'Istraživači', ka: '/istrazivaci' },
          { naziv: istrazivac?.imePrezime ?? 'Profil' },
        ]}
      />

      <div className="row g-4">
        <div className="col-12 col-lg-4">
          <div className="card shadow-sm">
            <div className="card-body">
              <h4 className="card-title mb-1">{istrazivac?.imePrezime}</h4>
              <p className="text-secondary small mb-3">Istraživač</p>

              <h6 className="text-uppercase text-secondary small">Biografija</h6>
              <p className="mb-0">{istrazivac?.biografija || 'Biografija nije uneta.'}</p>
            </div>
          </div>
        </div>

        <div className="col-12 col-lg-8">
          <div className="card shadow-sm">
            <div className="card-body">
              <h5 className="card-title mb-1">Objavljeni radovi</h5>
              <p className="text-secondary small">
                {radovi.length === 1 ? '1 objavljen rad' : `${radovi.length} objavljenih radova`}
              </p>

              {radovi.length === 0 ? (
                <Poruka vrsta="prazno">
                  Ovaj istraživač još nema objavljenih radova u sistemu. Radovi u statusu nacrta i
                  na recenziji nisu javno vidljivi.
                </Poruka>
              ) : (
                <ul className="list-group list-group-flush">
                  {radovi.map((rad) => (
                    <li key={rad.id} className="list-group-item px-0">
                      <Link to={`/radovi/${rad.id}`} className="fw-semibold">
                        {rad.naslov}
                      </Link>
                      <div className="text-secondary small">
                        {rad.spoljniAutori || 'Autori iz laboratorije'}
                        {rad.godina && <span className="ms-2">· {rad.godina}</span>}
                      </div>
                      {rad.doi && (
                        <a
                          href={`https://doi.org/${rad.doi}`}
                          target="_blank"
                          rel="noreferrer"
                          className="small"
                        >
                          {rad.doi}
                        </a>
                      )}
                    </li>
                  ))}
                </ul>
              )}
            </div>
          </div>
        </div>
      </div>
    </div>
  )
}
