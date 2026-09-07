import { useState } from 'react'
import api from '../api/axios'
import { AuthContext, NAZIV_ULOGE } from './authKontekst'

function ucitajIzMemorije(kljuc) {
  const vrednost = localStorage.getItem(kljuc)
  if (!vrednost) return null
  try {
    return JSON.parse(vrednost)
  } catch {
    return vrednost
  }
}

export function AuthProvider({ children }) {
  const [korisnik, setKorisnik] = useState(() => ucitajIzMemorije('korisnik'))
  const [ulogaId, setUlogaId] = useState(() => {
    const sacuvana = localStorage.getItem('ulogaId')
    return sacuvana ? Number(sacuvana) : null
  })

  const prijaviSe = async (email, lozinka, izabranaUloga) => {
    const { data } = await api.post('/prijava', {
      email,
      password: lozinka,
      uloga_id: izabranaUloga,
    })

    localStorage.setItem('token', data.token)
    localStorage.setItem('korisnik', JSON.stringify(data.korisnik))
    localStorage.setItem('ulogaId', String(data.trenutna_uloga))

    setKorisnik(data.korisnik)
    setUlogaId(Number(data.trenutna_uloga))

    return Number(data.trenutna_uloga)
  }

  const odjaviSe = async () => {
    try {
      await api.post('/odjava')
    } catch {
      void 0
    }
    localStorage.removeItem('token')
    localStorage.removeItem('korisnik')
    localStorage.removeItem('ulogaId')
    setKorisnik(null)
    setUlogaId(null)
  }

  const vrednost = {
    korisnik,
    ulogaId,
    ulogaNaziv: ulogaId ? NAZIV_ULOGE[ulogaId] : null,
    jeUlogovan: Boolean(korisnik),
    prijaviSe,
    odjaviSe,
  }

  return <AuthContext.Provider value={vrednost}>{children}</AuthContext.Provider>
}
