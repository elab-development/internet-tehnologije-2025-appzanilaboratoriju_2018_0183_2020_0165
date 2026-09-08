const KLASE_PO_STATUSU = {
  'Nacrt': 'bg-secondary',
  'Čeka recenziju': 'bg-warning text-dark',
  'Objavljen': 'bg-success',
  'Odbijen': 'bg-danger',
}

export default function StatusBedz({ status, dodatneKlase = '' }) {
  if (!status) {
    return null
  }

  const klase = ['badge', KLASE_PO_STATUSU[status] ?? 'bg-light text-dark', dodatneKlase]
    .filter(Boolean)
    .join(' ')

  return <span className={klase}>{status}</span>
}
