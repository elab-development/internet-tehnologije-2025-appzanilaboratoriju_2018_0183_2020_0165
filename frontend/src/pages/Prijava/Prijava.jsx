import { useState } from 'react'
import { useNavigate } from 'react-router-dom'
import useAuth from '../../hooks/useAuth'
import { ULOGE } from '../../context/authKontekst'

const POCETNA_STRANICA_PO_ULOZI = {
  [ULOGE.ADMINISTRATOR]: '/admin/korisnici',
  [ULOGE.RECENZENT]: '/recenzije',
  [ULOGE.ISTRAZIVAC]: '/moji-radovi',
}

export default function Prijava() {
  const [email, setEmail] = useState('')
  const [lozinka, setLozinka] = useState('')
  const [ulogaId, setUlogaId] = useState(ULOGE.ISTRAZIVAC)
  const [greska, setGreska] = useState('')
  const [ucitava, setUcitava] = useState(false)

  const { prijaviSe } = useAuth()
  const navigate = useNavigate()

  const posaljiFormu = async (dogadjaj) => {
    dogadjaj.preventDefault()
    setGreska('')
    setUcitava(true)

    try {
      const uloga = await prijaviSe(email, lozinka, Number(ulogaId))
      navigate(POCETNA_STRANICA_PO_ULOZI[uloga] ?? '/radovi')
    } catch (error) {
      const status = error.response?.status
      if (status === 429) {
        setGreska('Previše pokušaja prijave. Sačekajte minut pa pokušajte ponovo.')
      } else {
        setGreska(error.response?.data?.message ?? 'Prijava nije uspela.')
      }
    } finally {
      setUcitava(false)
    }
  }

  return (
    <div className="container py-5">
      <div className="row justify-content-center">
        <div className="col-md-6 col-lg-5">
          <div className="card shadow-sm">
            <div className="card-body p-4">
              <h3 className="card-title mb-4 text-center">Prijava na sistem</h3>

              {greska && <div className="alert alert-danger py-2">{greska}</div>}

              <form onSubmit={posaljiFormu}>
                <div className="mb-3">
                  <label className="form-label" htmlFor="email">
                    Email
                  </label>
                  <input
                    id="email"
                    type="email"
                    className="form-control"
                    value={email}
                    onChange={(e) => setEmail(e.target.value)}
                    required
                  />
                </div>

                <div className="mb-3">
                  <label className="form-label" htmlFor="lozinka">
                    Lozinka
                  </label>
                  <input
                    id="lozinka"
                    type="password"
                    className="form-control"
                    value={lozinka}
                    onChange={(e) => setLozinka(e.target.value)}
                    required
                  />
                </div>

                <div className="mb-4">
                  <label className="form-label" htmlFor="uloga">
                    Prijavi se kao
                  </label>
                  <select
                    id="uloga"
                    className="form-select"
                    value={ulogaId}
                    onChange={(e) => setUlogaId(e.target.value)}
                  >
                    <option value={ULOGE.ISTRAZIVAC}>Istraživač</option>
                    <option value={ULOGE.RECENZENT}>Recenzent</option>
                    <option value={ULOGE.ADMINISTRATOR}>Administrator</option>
                  </select>
                </div>

                <button type="submit" className="btn btn-primary w-100" disabled={ucitava}>
                  {ucitava ? 'Prijavljivanje...' : 'Prijavi se'}
                </button>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  )
}
