const KLASE_PO_VARIJANTI = {
  primary: 'btn-primary',
  danger: 'btn-danger',
  outline: 'btn-outline-secondary',
}

export default function Button({
  varijanta = 'primary',
  tip = 'button',
  ucitava = false,
  onemoguceno = false,
  dodatneKlase = '',
  onClick,
  children,
}) {
  const klase = ['btn', KLASE_PO_VARIJANTI[varijanta] ?? KLASE_PO_VARIJANTI.primary, dodatneKlase]
    .filter(Boolean)
    .join(' ')

  return (
    <button type={tip} className={klase} onClick={onClick} disabled={ucitava || onemoguceno}>
      {ucitava && <span className="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>}
      {children}
    </button>
  )
}
