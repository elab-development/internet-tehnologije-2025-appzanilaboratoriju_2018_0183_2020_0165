import { useMemo, useState } from 'react'
import { useNavigate } from 'react-router-dom'
import usePaginatedFetch from '../../hooks/usePaginatedFetch'
import Input from '../../components/Input/Input'
import Button from '../../components/Button/Button'
import Pagination from '../../components/Pagination/Pagination'
import Poruka from '../../components/Poruka/Poruka'
import Ucitavanje from '../../components/Ucitavanje/Ucitavanje'
import './Istrazivaci.css'

const PO_STRANI = 6

export default function Istrazivaci() {
  const navigate = useNavigate()
  const [pojam, setPojam] = useState('')

  const { sviPodaci, ucitava, greska, strana, promeniStranu } = usePaginatedFetch(
    '/istrazivaci',
    {},
    PO_STRANI
  )

  const filtrirani = useMemo(() => {
    const trazeno = pojam.trim().toLowerCase()
    if (!trazeno) return sviPodaci

    return sviPodaci.filter((istrazivac) =>
      (istrazivac.imePrezime ?? '').toLowerCase().includes(trazeno)
    )
  }, [sviPodaci, pojam])

  const zaPrikaz = useMemo(() => {
    const pocetak = (strana - 1) * PO_STRANI
    return filtrirani.slice(pocetak, pocetak + PO_STRANI)
  }, [filtrirani, strana])

  const stranaUkupno = Math.max(1, Math.ceil(filtrirani.length / PO_STRANI))

  return (
    <div className="container py-4">
      <div className="mb-4">
        <h2 className="mb-1">Istraživači</h2>
        <p className="text-secondary mb-0">
          Članovi laboratorije, njihove biografije i objavljeni radovi.
        </p>
      </div>

      <div className="row mb-3">
        <div className="col-12 col-md-5">
          <Input
            naziv="pretragaIstrazivaca"
            labela="Pretraga po imenu"
            vrednost={pojam}
            onChange={(e) => setPojam(e.target.value)}
            placeholder="npr. Petar"
          />
        </div>
      </div>

      {ucitava && <Ucitavanje tekst="Učitavanje istraživača..." />}

      {!ucitava && greska && <Poruka vrsta="greska">{greska}</Poruka>}

      {!ucitava && !greska && filtrirani.length === 0 && (
        <Poruka vrsta="prazno" naslov="Nema rezultata">
          {pojam ? 'Nijedan istraživač ne odgovara zadatom imenu.' : 'Nema unetih istraživača.'}
        </Poruka>
      )}

      {!ucitava && !greska && filtrirani.length > 0 && (
        <>
          <div className="row g-3">
            {zaPrikaz.map((istrazivac) => (
              <div className="col-12 col-md-6 col-lg-4" key={istrazivac.id}>
                <div className="card h-100 shadow-sm">
                  <div className="card-body d-flex flex-column">
                    <h5 className="card-title mb-1">{istrazivac.imePrezime}</h5>
                    <p className="text-secondary small mb-2">
                      {istrazivac.brojObjavljenihRadova === 1
                        ? '1 objavljen rad'
                        : `${istrazivac.brojObjavljenihRadova ?? 0} objavljenih radova`}
                    </p>

                    <p className="card-text small text-truncate-3">
                      {istrazivac.biografija || 'Biografija nije uneta.'}
                    </p>

                    <div className="mt-auto">
                      <Button
                        varijanta="outline"
                        velicina="mala"
                        onClick={() => navigate(`/istrazivaci/${istrazivac.id}`)}
                      >
                        Profil
                      </Button>
                    </div>
                  </div>
                </div>
              </div>
            ))}
          </div>

          <div className="mt-4">
            <Pagination
              trenutnaStrana={strana}
              ukupnoStrana={stranaUkupno}
              naPromenu={promeniStranu}
            />
          </div>
        </>
      )}
    </div>
  )
}
