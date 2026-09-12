import { useEffect, useState } from 'react'
import Modal from '../Modal/Modal'
import Button from '../Button/Button'
import Poruka from '../Poruka/Poruka'
import Ucitavanje from '../Ucitavanje/Ucitavanje'
import { ucitajFajlRada, sacuvajNaDisk } from '../../utils/fajlRada'

export default function PdfPregled({ rad, naZatvaranje }) {
  const [adresaFajla, setAdresaFajla] = useState(null)
  const [ucitava, setUcitava] = useState(false)
  const [greska, setGreska] = useState(null)

  useEffect(() => {
    if (!rad?.id) {
      return
    }

    let otkazano = false
    let adresa = null

    async function ucitaj() {
      setUcitava(true)
      setGreska(null)
      setAdresaFajla(null)

      try {
        adresa = await ucitajFajlRada(rad.id)

        if (otkazano) {
          window.URL.revokeObjectURL(adresa)
          return
        }

        setAdresaFajla(adresa)
      } catch (error) {
        if (!otkazano) {
          setGreska(
            error.response?.status === 404
              ? 'Za ovaj rad nije priložen PDF.'
              : 'Fajl nije moguće otvoriti. Možda nemate pravo pristupa ovom radu.'
          )
        }
      } finally {
        if (!otkazano) setUcitava(false)
      }
    }

    ucitaj()

    return () => {
      otkazano = true
      if (adresa) window.URL.revokeObjectURL(adresa)
    }
  }, [rad?.id])

  const podnozje = (
    <>
      <Button varijanta="outline" onClick={naZatvaranje}>
        Zatvori
      </Button>
      <Button
        onemoguceno={!adresaFajla}
        onClick={() => sacuvajNaDisk(adresaFajla, rad?.imeFajla)}
      >
        Preuzmi PDF
      </Button>
    </>
  )

  return (
    <Modal
      naslov={rad?.naslov ?? 'Rad'}
      otvoren={rad !== null}
      naZatvaranje={naZatvaranje}
      velicina="veliko"
      podnozje={podnozje}
    >
      {ucitava && <Ucitavanje tekst="Učitavanje PDF-a..." visina="480px" />}

      {greska && <Poruka vrsta="greska">{greska}</Poruka>}

      {adresaFajla && (
        <iframe
          src={adresaFajla}
          title={`PDF rada: ${rad?.naslov ?? ''}`}
          className="w-100 border rounded"
          style={{ height: '70vh', minHeight: '420px' }}
        />
      )}
    </Modal>
  )
}
