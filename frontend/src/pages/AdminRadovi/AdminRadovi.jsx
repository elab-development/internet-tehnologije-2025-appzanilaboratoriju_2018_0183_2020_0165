import { useEffect, useState } from 'react'
import { Link } from 'react-router-dom'
import api from '../../api/axios'
import usePaginatedFetch from '../../hooks/usePaginatedFetch'
import Input from '../../components/Input/Input'
import Select from '../../components/Select/Select'
import Modal from '../../components/Modal/Modal'
import Button from '../../components/Button/Button'
import StatusBedz from '../../components/StatusBedz/StatusBedz'
import Pagination from '../../components/Pagination/Pagination'
import Poruka from '../../components/Poruka/Poruka'
import Ucitavanje from '../../components/Ucitavanje/Ucitavanje'
import PotvrdaBrisanja from '../../components/PotvrdaBrisanja/PotvrdaBrisanja'
import PdfPregled from '../../components/PdfPregled/PdfPregled'
import AutoriRada from '../../components/AutoriRada/AutoriRada'

const PO_STRANI = 8
const OBJAVLJEN = 'Objavljen'

export default function AdminRadovi() {
  const [pretraga, setPretraga] = useState('')
  const [odlozenaPretraga, setOdlozenaPretraga] = useState('')
  const [obavestenje, setObavestenje] = useState(null)
  const [radZaBrisanje, setRadZaBrisanje] = useState(null)
  const [recenzijaZaBrisanje, setRecenzijaZaBrisanje] = useState(null)
  const [brise, setBrise] = useState(false)
  const [pdfRad, setPdfRad] = useState(null)
  const [recenzenti, setRecenzenti] = useState([])
  const [radZaDodelu, setRadZaDodelu] = useState(null)
  const [izabraniRecenzent, setIzabraniRecenzent] = useState('')
  const [dodeljuje, setDodeljuje] = useState(false)
  const [greskaDodele, setGreskaDodele] = useState(null)

  useEffect(() => {
    const tajmer = setTimeout(() => setOdlozenaPretraga(pretraga), 400)
    return () => clearTimeout(tajmer)
  }, [pretraga])

  useEffect(() => {
    api
      .get('/admin/korisnici')
      .then(({ data }) => {
        const svi = data?.data ?? data?.podaci ?? []
        setRecenzenti(svi.filter((k) => k.uloge?.some((u) => u.naziv === 'Recenzent')))
      })
      .catch(() => setRecenzenti([]))
  }, [])

  const { podaci, ucitava, greska, strana, ukupnoStrana, ukupnoStavki, promeniStranu, osvezi } =
    usePaginatedFetch('/radovi', odlozenaPretraga ? { pretraga: odlozenaPretraga } : {}, PO_STRANI)

  const obrisiRad = async () => {
    setBrise(true)

    try {
      await api.delete(`/radovi/${radZaBrisanje.id}`)
      setObavestenje({ vrsta: 'uspeh', tekst: 'Rad je trajno obrisan.' })
      setRadZaBrisanje(null)
      osvezi()
    } catch (error) {
      setObavestenje({
        vrsta: 'greska',
        tekst: error.response?.data?.message ?? 'Brisanje rada nije uspelo.',
      })
    } finally {
      setBrise(false)
    }
  }

  const otvoriDodelu = (rad) => {
    setRadZaDodelu(rad)
    setIzabraniRecenzent('')
    setGreskaDodele(null)
  }

  const dodeliRecenzenta = async () => {
    setDodeljuje(true)
    setGreskaDodele(null)

    try {
      await api.post(`/radovi/${radZaDodelu.id}/recenzent`, {
        recenzentId: Number(izabraniRecenzent),
      })
      setObavestenje({ vrsta: 'uspeh', tekst: 'Recenzent je dodeljen radu.' })
      setRadZaDodelu(null)
      osvezi()
    } catch (error) {
      setGreskaDodele(error.response?.data?.message ?? 'Dodela recenzenta nije uspela.')
    } finally {
      setDodeljuje(false)
    }
  }

  const obrisiRecenziju = async () => {
    setBrise(true)

    try {
      await api.delete(`/recenzije/${recenzijaZaBrisanje.recenzijaId}`)
      setObavestenje({ vrsta: 'uspeh', tekst: 'Dodela recenzije je obrisana.' })
      setRecenzijaZaBrisanje(null)
      osvezi()
    } catch (error) {
      setObavestenje({
        vrsta: 'greska',
        tekst: error.response?.data?.message ?? 'Brisanje recenzije nije uspelo.',
      })
    } finally {
      setBrise(false)
    }
  }

  return (
    <div className="container py-4">
      <div className="mb-4">
        <h2 className="mb-1">Svi naučni radovi</h2>
        <p className="text-secondary mb-0">
          {ukupnoStavki === 0 ? 'Nema radova.' : `Ukupno radova: ${ukupnoStavki}`}
        </p>
      </div>

      {obavestenje && <Poruka vrsta={obavestenje.vrsta}>{obavestenje.tekst}</Poruka>}

      <div className="row g-3 mb-4">
        <div className="col-12 col-md-6">
          <Input
            naziv="pretraga"
            labela="Pretraga"
            vrednost={pretraga}
            onChange={(e) => setPretraga(e.target.value)}
            placeholder="Naslov, ključne reči, oblast ili autor"
          />
        </div>
      </div>

      {ucitava && <Ucitavanje tekst="Učitavanje radova..." />}

      {greska && <Poruka vrsta="greska">{greska}</Poruka>}

      {!ucitava && !greska && podaci.length === 0 && (
        <Poruka vrsta="prazno">Nijedan rad ne odgovara pretrazi.</Poruka>
      )}

      {!ucitava && !greska && podaci.length > 0 && (
        <>
          <div className="table-responsive">
            <table className="table align-middle">
              <thead>
                <tr>
                  <th>Naslov</th>
                  <th>Autori</th>
                  <th>Godina</th>
                  <th>Status</th>
                  <th>Recenzent</th>
                  <th className="text-end">Radnje</th>
                </tr>
              </thead>
              <tbody>
                {podaci.map((rad) => (
                  <tr key={rad.id}>
                    <td>
                      {rad.status === OBJAVLJEN ? (
                        <Link to={`/radovi/${rad.id}`} className="text-decoration-none">
                          {rad.naslov}
                        </Link>
                      ) : (
                        rad.naslov
                      )}
                      {rad.verzija > 1 && (
                        <span className="badge bg-light text-dark border ms-2">
                          verzija {rad.verzija}
                        </span>
                      )}
                    </td>
                    <td className="small">
                      <AutoriRada rad={rad} />
                    </td>
                    <td className="small">{rad.godina}</td>
                    <td>
                      <StatusBedz status={rad.status} />
                    </td>
                    <td className="small">
                      {rad.recenzenti?.length > 0
                        ? rad.recenzenti.map((recenzent) => recenzent.imePrezime).join(', ')
                        : 'nije dodeljen'}
                    </td>
                    <td className="text-end">
                      <div className="d-flex justify-content-end flex-wrap gap-2">
                        {rad.imaFajl && (
                          <Button
                            varijanta="outline"
                            velicina="mala"
                            onClick={() => setPdfRad(rad)}
                          >
                            PDF
                          </Button>
                        )}

                        {rad.status !== OBJAVLJEN && !(rad.recenzenti?.length > 0) && (
                          <Button velicina="mala" onClick={() => otvoriDodelu(rad)}>
                            Dodeli recenzenta
                          </Button>
                        )}

                        {rad.recenzenti?.length > 0 && (
                          <Button
                            varijanta="outline"
                            velicina="mala"
                            onClick={() =>
                              setRecenzijaZaBrisanje({
                                recenzijaId: rad.recenzenti[0].recenzijaId,
                                naslov: rad.naslov,
                                recenzent: rad.recenzenti[0].imePrezime,
                              })
                            }
                          >
                            Skini recenzenta
                          </Button>
                        )}

                        <Button
                          varijanta="danger"
                          velicina="mala"
                          onClick={() => setRadZaBrisanje(rad)}
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
              trenutnaStrana={strana}
              ukupnoStrana={ukupnoStrana}
              naPromenu={promeniStranu}
            />
          </div>
        </>
      )}

      <PotvrdaBrisanja
        otvoren={radZaBrisanje !== null}
        naslov="Brisanje naučnog rada"
        poruka="Brisanjem rada trajno se uklanjaju i njegove recenzije, autorstva, oblasti i priloženi PDF."
        stavka={radZaBrisanje?.naslov}
        tekstPotvrde="Obriši rad"
        ucitava={brise}
        naPotvrdu={obrisiRad}
        naOtkaz={() => setRadZaBrisanje(null)}
      />

      <PotvrdaBrisanja
        otvoren={recenzijaZaBrisanje !== null}
        naslov="Brisanje dodele recenzije"
        poruka="Uklanja se dodela rada recenzentu, zajedno sa svim njegovim ocenama. Status rada ostaje nepromenjen, pa radu koji čeka recenziju treba dodeliti novog recenzenta."
        stavka={
          recenzijaZaBrisanje
            ? `${recenzijaZaBrisanje.recenzent}: ${recenzijaZaBrisanje.naslov}`
            : null
        }
        tekstPotvrde="Obriši dodelu"
        ucitava={brise}
        naPotvrdu={obrisiRecenziju}
        naOtkaz={() => setRecenzijaZaBrisanje(null)}
      />

      <Modal
        naslov={`Dodela recenzenta — ${radZaDodelu?.naslov ?? ''}`}
        otvoren={radZaDodelu !== null}
        naZatvaranje={() => (dodeljuje ? null : setRadZaDodelu(null))}
        zatvaranjeKlikomVan={!dodeljuje}
        podnozje={
          <>
            <Button
              varijanta="outline"
              onClick={() => setRadZaDodelu(null)}
              onemoguceno={dodeljuje}
            >
              Odustani
            </Button>
            <Button
              ucitava={dodeljuje}
              onemoguceno={!izabraniRecenzent}
              onClick={dodeliRecenzenta}
            >
              Dodeli
            </Button>
          </>
        }
      >
        {greskaDodele && <Poruka vrsta="greska">{greskaDodele}</Poruka>}

        <Select
          naziv="recenzent"
          labela="Recenzent"
          vrednost={izabraniRecenzent}
          onChange={(e) => setIzabraniRecenzent(e.target.value)}
          prazanTekst="Izaberite recenzenta"
          prazanKaoPlaceholder
          opcije={recenzenti.map((r) => ({ vrednost: r.id, tekst: r.imePrezime }))}
          obavezno
        />

        <Poruka vrsta="info" dodatneKlase="mb-0">
          Autor rada ne može biti njegov recenzent. Dodela ne menja status rada.
        </Poruka>
      </Modal>

      <PdfPregled rad={pdfRad} naZatvaranje={() => setPdfRad(null)} />
    </div>
  )
}
