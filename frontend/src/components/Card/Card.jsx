import { Link } from 'react-router-dom'
import StatusBedz from '../StatusBedz/StatusBedz'
import Button from '../Button/Button'
import AutoriRada from '../AutoriRada/AutoriRada'
import './Card.css'

export default function Card({ rad, onDetalji }) {
  if (!rad) {
    return null
  }

  return (
    <div className="card h-100 shadow-sm">
      <div className="card-body d-flex flex-column">
        <div className="d-flex justify-content-between align-items-start gap-2 mb-2">
          <h5 className="card-title mb-0">
            <Link to={`/radovi/${rad.id}`} className="text-decoration-none">
              {rad.naslov}
            </Link>
          </h5>
          <StatusBedz status={rad.status} />
        </div>

        <p className="text-secondary small mb-2">
          <AutoriRada rad={rad} />
          {rad.godina && <span className="ms-2">· {rad.godina}</span>}
        </p>

        {rad.abstrakt && <p className="card-text small text-truncate-3">{rad.abstrakt}</p>}

        {Array.isArray(rad.oblasti) && rad.oblasti.length > 0 && (
          <div className="d-flex flex-wrap gap-1 mb-3">
            {rad.oblasti.map((oblast) => (
              <span key={oblast} className="badge bg-light text-dark border">
                {oblast}
              </span>
            ))}
          </div>
        )}

        <div className="mt-auto">
          <Button varijanta="outline" velicina="mala" onClick={() => onDetalji?.(rad.id)}>
            Detalji
          </Button>
        </div>
      </div>
    </div>
  )
}
