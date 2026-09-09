import { useEffect, useId } from 'react'

const KLASE_PO_VELICINI = {
  malo: 'modal-sm',
  srednje: '',
  veliko: 'modal-lg',
}

export default function Modal({
  naslov = '',
  otvoren = false,
  naZatvaranje,
  podnozje = null,
  velicina = 'srednje',
  zatvaranjeKlikomVan = true,
  children,
}) {
  const idNaslova = useId()

  useEffect(() => {
    if (!otvoren) {
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
  }, [otvoren, naZatvaranje])

  if (!otvoren) {
    return null
  }

  const klaseDijaloga = ['modal-dialog', 'modal-dialog-centered', 'modal-dialog-scrollable',
    KLASE_PO_VELICINI[velicina] ?? KLASE_PO_VELICINI.srednje]
    .filter(Boolean)
    .join(' ')

  return (
    <>
      <div
        className="modal fade show d-block"
        tabIndex="-1"
        role="dialog"
        aria-modal="true"
        aria-labelledby={idNaslova}
        onMouseDown={(dogadjaj) => {
          if (zatvaranjeKlikomVan && dogadjaj.target === dogadjaj.currentTarget) {
            naZatvaranje?.()
          }
        }}
      >
        <div className={klaseDijaloga}>
          <div className="modal-content">
            <div className="modal-header">
              <h5 className="modal-title" id={idNaslova}>{naslov}</h5>
              <button
                type="button"
                className="btn-close"
                aria-label="Zatvori"
                onClick={() => naZatvaranje?.()}
              ></button>
            </div>

            <div className="modal-body">{children}</div>

            {podnozje && <div className="modal-footer">{podnozje}</div>}
          </div>
        </div>
      </div>

      <div className="modal-backdrop fade show"></div>
    </>
  )
}
