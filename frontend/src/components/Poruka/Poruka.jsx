const KLASE_PO_VRSTI = {
  greska: 'alert-danger',
  uspeh: 'alert-success',
  info: 'alert-info',
  prazno: 'alert-secondary',
}

export default function Poruka({ vrsta = 'info', naslov = null, children, dodatneKlase = '' }) {
  if (!children && !naslov) {
    return null
  }

  const klase = ['alert', KLASE_PO_VRSTI[vrsta] ?? KLASE_PO_VRSTI.info, dodatneKlase]
    .filter(Boolean)
    .join(' ')

  return (
    <div className={klase} role="alert">
      {naslov && <h6 className="alert-heading mb-1">{naslov}</h6>}
      {children && <div className="mb-0">{children}</div>}
    </div>
  )
}
