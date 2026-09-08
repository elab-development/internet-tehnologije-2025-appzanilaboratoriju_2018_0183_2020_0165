import { Navigate } from 'react-router-dom'
import useAuth from '../hooks/useAuth'

export default function ZasticenaRuta({ dozvoljenaUloga, children }) {
  const { jeUlogovan, ulogaId } = useAuth()

  if (!jeUlogovan) {
    return <Navigate to="/prijava" replace />
  }

  if (dozvoljenaUloga && ulogaId !== dozvoljenaUloga) {
    return (
      <div className="container py-5">
        <div className="alert alert-warning">
          <h5 className="alert-heading">Zabranjen pristup</h5>
          <p className="mb-0">Ova stranica nije dostupna za vašu trenutnu ulogu.</p>
        </div>
      </div>
    )
  }

  return children
}
