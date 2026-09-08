import { useEffect, useState } from 'react'
import api from '../../api/axios'
import StatChart from '../../components/StatChart/StatChart'

export default function AdminStatistika() {
  const [poStatusu, setPoStatusu] = useState(null)
  const [poGodini, setPoGodini] = useState(null)
  const [ucitava, setUcitava] = useState(true)
  const [greska, setGreska] = useState(null)

  useEffect(() => {
    let otkazano = false

    async function ucitaj() {
      try {
        const [statusi, godine] = await Promise.all([
          api.get('/statistika/radovi-po-statusu'),
          api.get('/statistika/radovi-po-godini'),
        ])

        if (!otkazano) {
          setPoStatusu(statusi.data)
          setPoGodini(godine.data)
        }
      } catch (err) {
        if (!otkazano) {
          setGreska(
            err.response?.status === 403
              ? 'Statistika je dostupna samo administratoru.'
              : 'Podaci o statistici trenutno nisu dostupni.'
          )
        }
      } finally {
        if (!otkazano) {
          setUcitava(false)
        }
      }
    }

    ucitaj()

    return () => {
      otkazano = true
    }
  }, [])

  if (ucitava) {
    return (
      <div className="container py-4">
        <h2 className="mb-3">Statistika radova</h2>
        <div className="d-flex align-items-center gap-2 text-secondary">
          <span className="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
          Učitavanje podataka...
        </div>
      </div>
    )
  }

  if (greska) {
    return (
      <div className="container py-4">
        <h2 className="mb-3">Statistika radova</h2>
        <div className="alert alert-danger">{greska}</div>
      </div>
    )
  }

  return (
    <div className="container py-4">
      <h2 className="mb-1">Statistika radova</h2>
      <p className="text-secondary">Ukupno radova u sistemu: {poStatusu?.ukupno ?? 0}</p>

      <div className="row g-4">
        <div className="col-12 col-lg-5">
          <StatChart
            podaci={poStatusu?.podaci ?? []}
            tip="pie"
            naslov={poStatusu?.naslov ?? 'Radovi po statusu'}
          />
        </div>

        <div className="col-12 col-lg-7">
          <StatChart
            podaci={poGodini?.podaci ?? []}
            tip="bar"
            naslov={poGodini?.naslov ?? 'Radovi po godini'}
          />
        </div>
      </div>
    </div>
  )
}
