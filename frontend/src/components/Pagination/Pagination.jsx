import './Pagination.css'

function rasponStrana(trenutna, ukupno) {
  const maksimum = 5

  if (ukupno <= maksimum) {
    return Array.from({ length: ukupno }, (_, i) => i + 1)
  }

  let pocetak = Math.max(1, trenutna - 2)
  const kraj = Math.min(ukupno, pocetak + maksimum - 1)

  pocetak = Math.max(1, kraj - maksimum + 1)

  return Array.from({ length: kraj - pocetak + 1 }, (_, i) => pocetak + i)
}

export default function Pagination({ trenutnaStrana, ukupnoStrana, naPromenu }) {
  if (!ukupnoStrana || ukupnoStrana < 2) {
    return null
  }

  const strane = rasponStrana(trenutnaStrana, ukupnoStrana)

  return (
    <nav aria-label="Navigacija kroz strane">
      <ul className="pagination justify-content-center mb-0">
        <li className={`page-item ${trenutnaStrana === 1 ? 'disabled' : ''}`}>
          <button className="page-link" onClick={() => naPromenu(trenutnaStrana - 1)}>
            Prethodna
          </button>
        </li>

        {strane[0] > 1 && (
          <li className="page-item disabled">
            <span className="page-link">...</span>
          </li>
        )}

        {strane.map((broj) => (
          <li key={broj} className={`page-item ${broj === trenutnaStrana ? 'active' : ''}`}>
            <button className="page-link" onClick={() => naPromenu(broj)}>
              {broj}
            </button>
          </li>
        ))}

        {strane[strane.length - 1] < ukupnoStrana && (
          <li className="page-item disabled">
            <span className="page-link">...</span>
          </li>
        )}

        <li className={`page-item ${trenutnaStrana === ukupnoStrana ? 'disabled' : ''}`}>
          <button className="page-link" onClick={() => naPromenu(trenutnaStrana + 1)}>
            Sledeća
          </button>
        </li>
      </ul>
    </nav>
  )
}
