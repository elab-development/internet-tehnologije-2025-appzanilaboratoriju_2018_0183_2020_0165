import { Link } from 'react-router-dom'

export default function Breadcrumbs({ putanja = [] }) {
  if (putanja.length === 0) {
    return null
  }

  return (
    <nav aria-label="Putanja navigacije">
      <ol className="breadcrumb">
        {putanja.map((stavka, indeks) => {
          const poslednja = indeks === putanja.length - 1

          return (
            <li
              key={`${stavka.naziv}-${indeks}`}
              className={`breadcrumb-item ${poslednja ? 'active' : ''}`}
              aria-current={poslednja ? 'page' : undefined}
            >
              {stavka.ka && !poslednja ? <Link to={stavka.ka}>{stavka.naziv}</Link> : stavka.naziv}
            </li>
          )
        })}
      </ol>
    </nav>
  )
}
