import { useEffect, useMemo, useState } from 'react'
import { useNavigate } from 'react-router-dom'
import api from '../../api/axios'
import usePaginatedFetch from '../../hooks/usePaginatedFetch'
import Card from '../../components/Card/Card'
import Select from '../../components/Select/Select'
import Input from '../../components/Input/Input'
import Button from '../../components/Button/Button'
import Pagination from '../../components/Pagination/Pagination'
import Poruka from '../../components/Poruka/Poruka'
import Ucitavanje from '../../components/Ucitavanje/Ucitavanje'

const SORTIRANJA = [
  { vrednost: 'godina-opadajuce', tekst: 'Najnoviji prvo' },
  { vrednost: 'godina-rastuce', tekst: 'Najstariji prvo' },
  { vrednost: 'naslov-rastuce', tekst: 'Naslov A-Š' },
  { vrednost: 'naslov-opadajuce', tekst: 'Naslov Š-A' },
]

const PO_STRANI = 6

export default function Radovi() {
  const navigate = useNavigate()

  const [unetiPojam, setUnetiPojam] = useState('')
  const [pojam, setPojam] = useState('')
  const [oblastId, setOblastId] = useState('')
  const [sortiranje, setSortiranje] = useState('godina-opadajuce')
  const [oblasti, setOblasti] = useState([])

  useEffect(() => {
    const odlozeno = setTimeout(() => setPojam(unetiPojam.trim()), 400)
    return () => clearTimeout(odlozeno)
  }, [unetiPojam])

  useEffect(() => {
    api
      .get('/oblasti')
      .then(({ data }) => setOblasti(Array.isArray(data) ? data : (data?.podaci ?? [])))
      .catch(() => setOblasti([]))
  }, [])

  const parametri = useMemo(() => {
    const p = {}
    if (pojam) p.keyword = pojam
    if (oblastId) p.oblast_id = oblastId
    return p
  }, [pojam, oblastId])

  const { sviPodaci, ucitava, greska, strana, ukupnoStrana, ukupnoStavki, promeniStranu } =
    usePaginatedFetch('/radovi/objavljeni', parametri, PO_STRANI)

  const sortirani = useMemo(() => {
    const kopija = [...sviPodaci]

    kopija.sort((a, b) => {
      switch (sortiranje) {
        case 'godina-rastuce':
          return (a.godina ?? 0) - (b.godina ?? 0)
        case 'naslov-rastuce':
          return (a.naslov ?? '').localeCompare(b.naslov ?? '', 'sr')
        case 'naslov-opadajuce':
          return (b.naslov ?? '').localeCompare(a.naslov ?? '', 'sr')
        default:
          return (b.godina ?? 0) - (a.godina ?? 0)
      }
    })

    return kopija
  }, [sviPodaci, sortiranje])

  const zaPrikaz = useMemo(() => {
    const pocetak = (strana - 1) * PO_STRANI
    return sortirani.slice(pocetak, pocetak + PO_STRANI)
  }, [sortirani, strana])

  const ponistiFiltere = () => {
    setUnetiPojam('')
    setOblastId('')
    setSortiranje('godina-opadajuce')
  }

  const imaFiltera = Boolean(pojam || oblastId)

  return (
    <div className="container py-4">
      <div className="mb-4">
        <h2 className="mb-1">Objavljeni naučni radovi</h2>
        <p className="text-secondary mb-0">
          Pregled radova objavljenih u okviru laboratorije i uvezenih iz spoljnih izvora.
        </p>
      </div>

      <div className="card shadow-sm mb-4">
        <div className="card-body">
          <div className="row g-3 align-items-end">
            <div className="col-12 col-lg-5">
              <Input
                naziv="pretraga"
                labela="Pretraga po naslovu i ključnim rečima"
                vrednost={unetiPojam}
                onChange={(e) => setUnetiPojam(e.target.value)}
                placeholder="npr. neuronske mreže"
              />
            </div>

            <div className="col-12 col-sm-6 col-lg-3">
              <Select
                naziv="oblast"
                labela="Oblast"
                vrednost={oblastId}
                onChange={(e) => setOblastId(e.target.value)}
                prazanTekst="Sve oblasti"
                opcije={oblasti.map((oblast) => ({
                  vrednost: oblast.oblastId,
                  tekst: oblast.naziv,
                }))}
              />
            </div>

            <div className="col-12 col-sm-6 col-lg-3">
              <Select
                naziv="sortiranje"
                labela="Sortiraj"
                vrednost={sortiranje}
                onChange={(e) => setSortiranje(e.target.value)}
                opcije={SORTIRANJA}
              />
            </div>

            <div className="col-12 col-lg-1 mb-3">
              <Button
                varijanta="outline"
                onemoguceno={!imaFiltera}
                onClick={ponistiFiltere}
                dodatneKlase="w-100"
              >
                Poništi
              </Button>
            </div>
          </div>
        </div>
      </div>

      {ucitava && <Ucitavanje tekst="Učitavanje radova..." />}

      {!ucitava && greska && <Poruka vrsta="greska">{greska}</Poruka>}

      {!ucitava && !greska && sortirani.length === 0 && (
        <Poruka vrsta="prazno" naslov="Nema rezultata">
          {imaFiltera
            ? 'Nijedan objavljen rad ne odgovara zadatim kriterijumima. Pokušajte sa drugom oblašću ili pojmom.'
            : 'Trenutno nema objavljenih radova.'}
        </Poruka>
      )}

      {!ucitava && !greska && sortirani.length > 0 && (
        <>
          <p className="text-secondary small">
            Prikazano {zaPrikaz.length} od ukupno {ukupnoStavki} radova
          </p>

          <div className="row g-3">
            {zaPrikaz.map((rad) => (
              <div className="col-12 col-md-6 col-lg-4" key={rad.id}>
                <Card rad={rad} onDetalji={(id) => navigate(`/radovi/${id}`)} />
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
    </div>
  )
}
