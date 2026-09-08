import { useState } from 'react'
import { useNavigate } from 'react-router-dom'
import useAuth from '../../hooks/useAuth'
import { ULOGE } from '../../context/authKontekst'
import Button from '../../components/Button/Button'
import Input from '../../components/Input/Input'

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
                <Input
                  naziv="email"
                  labela="Email"
                  tip="email"
                  vrednost={email}
                  onChange={(e) => setEmail(e.target.value)}
                  obavezno
                />

                <Input
                  naziv="lozinka"
                  labela="Lozinka"
                  tip="password"
                  vrednost={lozinka}
                  onChange={(e) => setLozinka(e.target.value)}
                  obavezno
                />

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

                <Button tip="submit" ucitava={ucitava} dodatneKlase="w-100">
                  {ucitava ? 'Prijavljivanje...' : 'Prijavi se'}
                </Button>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  )
}
