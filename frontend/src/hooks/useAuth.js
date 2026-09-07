import { useContext } from 'react'
import { AuthContext } from '../context/authKontekst'

export default function useAuth() {
  const kontekst = useContext(AuthContext)

  if (!kontekst) {
    throw new Error('useAuth se mora koristiti unutar AuthProvider komponente')
  }

  return kontekst
}
