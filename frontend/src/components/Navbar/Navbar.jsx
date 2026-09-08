import { Link, NavLink, useNavigate } from 'react-router-dom'
import useAuth from '../../hooks/useAuth'
import { ULOGE } from '../../context/authKontekst'
import './Navbar.css'

export default function Navbar() {
  const { korisnik, ulogaId, ulogaNaziv, jeUlogovan, odjaviSe } = useAuth()
  const navigate = useNavigate()

  const naOdjavu = async () => {
    await odjaviSe()
    navigate('/prijava')
  }

  return (
    <nav className="navbar navbar-expand-lg navbar-dark bg-dark">
      <div className="container">
        <Link className="navbar-brand fw-bold" to="/radovi">
          NIL<span className="text-info">App</span>
        </Link>

        <button
          className="navbar-toggler"
          type="button"
          data-bs-toggle="collapse"
          data-bs-target="#glavniMeni"
          aria-controls="glavniMeni"
          aria-expanded="false"
          aria-label="Otvori meni"
        >
          <span className="navbar-toggler-icon"></span>
        </button>

        <div className="collapse navbar-collapse" id="glavniMeni">
          <ul className="navbar-nav me-auto">
            <li className="nav-item">
              <NavLink className="nav-link" to="/radovi">
                Objavljeni radovi
              </NavLink>
            </li>

            {ulogaId === ULOGE.ISTRAZIVAC && (
              <li className="nav-item">
                <NavLink className="nav-link" to="/moji-radovi">
                  Moji radovi
                </NavLink>
              </li>
            )}

            {ulogaId === ULOGE.RECENZENT && (
              <li className="nav-item">
                <NavLink className="nav-link" to="/recenzije">
                  Radovi za recenziju
                </NavLink>
              </li>
            )}

            {ulogaId === ULOGE.ADMINISTRATOR && (
              <>
                <li className="nav-item">
                  <NavLink className="nav-link" to="/admin/korisnici">
                    Korisnici
                  </NavLink>
                </li>
                <li className="nav-item">
                  <NavLink className="nav-link" to="/admin/statistika">
                    Statistika
                  </NavLink>
                </li>
              </>
            )}
          </ul>

          <div className="d-flex align-items-center gap-3">
            {jeUlogovan ? (
              <>
                <span className="navbar-text small">
                  {korisnik?.ImePrezime}
                  <span className="badge bg-info text-dark ms-2">{ulogaNaziv}</span>
                </span>
                <button className="btn btn-outline-light btn-sm" onClick={naOdjavu}>
                  Odjavi se
                </button>
              </>
            ) : (
              <NavLink className="btn btn-info btn-sm" to="/prijava">
                Prijavi se
              </NavLink>
            )}
          </div>
        </div>
      </div>
    </nav>
  )
}
