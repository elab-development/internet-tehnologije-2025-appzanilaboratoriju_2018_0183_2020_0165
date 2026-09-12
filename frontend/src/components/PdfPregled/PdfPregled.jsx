import { useEffect, useRef, useState } from 'react'
import Button from '../Button/Button'
import Poruka from '../Poruka/Poruka'
import Ucitavanje from '../Ucitavanje/Ucitavanje'
import { ucitajFajlRada, sacuvajNaDisk } from '../../utils/fajlRada'

function opisiGresku(error) {
  if (!error.response) {
    return 'Server nije dostupan. Proverite da li je pokrenut i probajte ponovo.'
  }

  if (error.response.status === 404) {
    return 'Za ovaj rad nije priložen PDF.'
  }

  if (error.response.status === 403) {
    return 'Fajl ovog rada mogu da otvore samo autori i dodeljeni recenzent.'
  }

  return 'Fajl nije moguće otvoriti.'
}

export default function PdfPregled({ rad, naZatvaranje }) {
  const [adresaFajla, setAdresaFajla] = useState(null)
  const [ucitava, setUcitava] = useState(false)
  const [greska, setGreska] = useState(null)
  const drugaKartica = useRef(false)

  useEffect(() => {
    if (!rad?.id) {
      return
    }

    let otkazano = false
    let adresa = null
    drugaKartica.current = false

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
          setGreska(opisiGresku(error))
        }
      } finally {
        if (!otkazano) setUcitava(false)
      }
    }

    ucitaj()

    return () => {
      otkazano = true
      if (adresa && !drugaKartica.current) {
        window.URL.revokeObjectURL(adresa)
      }
    }
  }, [rad?.id])

  useEffect(() => {
    if (!rad?.id) {
      return
    }

    function naTaster(dogadjaj) {
      if (dogadjaj.key === 'Escape') {
        naZatvaranje?.()
      }
    }

    const prethodniOverflow = document.body.style.overflow
    document.body.style.overflow = 'hidden'
    document.addEventListener('keydown', naTaster)

    return () => {
      document.body.style.overflow = prethodniOverflow
      document.removeEventListener('keydown', naTaster)
    }
  }, [rad?.id, naZatvaranje])

  if (!rad) {
    return null
  }

  const otvoriUNovojKartici = () => {
    drugaKartica.current = true
    window.open(adresaFajla, '_blank', 'noopener')
  }

  return (
    <div
      className="position-fixed top-0 start-0 w-100 h-100"
      style={{ zIndex: 1080, backgroundColor: '#1f2123' }}
      role="dialog"
      aria-modal="true"
      aria-label={`PDF rada: ${rad.naslov ?? ''}`}
    >
      {ucitava && (
        <div className="d-flex align-items-center justify-content-center h-100 text-white">
          <Ucitavanje tekst="Učitavanje PDF-a..." visina="auto" />
        </div>
      )}

      {greska && (
        <div className="d-flex align-items-center justify-content-center h-100 p-4">
          <div style={{ maxWidth: '480px' }}>
            <Poruka vrsta="greska">{greska}</Poruka>
            <Button varijanta="outline" onClick={naZatvaranje}>
              Zatvori
            </Button>
          </div>
        </div>
      )}

      {adresaFajla && (
        <iframe
          src={adresaFajla}
          title={`PDF rada: ${rad.naslov ?? ''}`}
          className="d-block border-0"
          style={{ width: '100%', height: '100%' }}
        />
      )}

      {adresaFajla && (
        <div
          className="position-absolute d-flex flex-wrap gap-2 p-2 rounded-3 shadow"
          style={{ bottom: '1rem', right: '1rem', backgroundColor: 'rgba(33, 37, 41, 0.85)' }}
        >
          <Button varijanta="outlineSvetla" velicina="mala" onClick={otvoriUNovojKartici}>
            Nova kartica
          </Button>
          <Button
            varijanta="outlineSvetla"
            velicina="mala"
            onClick={() => sacuvajNaDisk(adresaFajla, rad.imeFajla)}
          >
            Preuzmi
          </Button>
          <Button varijanta="danger" velicina="mala" onClick={naZatvaranje}>
            Zatvori
          </Button>
        </div>
      )}
    </div>
  )
}
