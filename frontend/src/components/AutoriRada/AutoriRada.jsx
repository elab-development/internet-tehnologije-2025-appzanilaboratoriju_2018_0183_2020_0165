import { Link } from 'react-router-dom'

export default function AutoriRada({ rad, linkovi = false, prazanTekst = 'Autori nisu navedeni' }) {
  if (rad?.spoljniAutori) {
    return <span>{rad.spoljniAutori}</span>
  }

  if (!Array.isArray(rad?.autori) || rad.autori.length === 0) {
    return <span>{prazanTekst}</span>
  }

  if (!linkovi) {
    return <span>{rad.autori.map((autor) => autor.imePrezime).join(', ')}</span>
  }

  return (
    <span>
      {rad.autori.map((autor, indeks) => (
        <span key={autor.id ?? indeks}>
          {indeks > 0 && ', '}
          {autor.id ? (
            <Link to={`/istrazivaci/${autor.id}`}>{autor.imePrezime}</Link>
          ) : (
            autor.imePrezime
          )}
        </span>
      ))}
    </span>
  )
}
