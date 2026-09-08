import { BrowserRouter, Routes, Route, Navigate } from 'react-router-dom'
import { AuthProvider } from './context/AuthProvider'
import { ULOGE } from './context/authKontekst'
import Navbar from './components/Navbar/Navbar'
import ZasticenaRuta from './components/ZasticenaRuta'
import Prijava from './pages/Prijava/Prijava'
import Radovi from './pages/Radovi/Radovi'
import RadDetalji from './pages/RadDetalji/RadDetalji'
import MojiRadovi from './pages/MojiRadovi/MojiRadovi'
import Recenzije from './pages/Recenzije/Recenzije'
import AdminKorisnici from './pages/AdminKorisnici/AdminKorisnici'
import AdminStatistika from './pages/AdminStatistika/AdminStatistika'

export default function App() {
  return (
    <AuthProvider>
      <BrowserRouter>
        <Navbar />

        <main>
          <Routes>
            <Route path="/" element={<Navigate to="/radovi" replace />} />
            <Route path="/prijava" element={<Prijava />} />
            <Route path="/radovi" element={<Radovi />} />
            <Route path="/radovi/:id" element={<RadDetalji />} />

            <Route
              path="/moji-radovi"
              element={
                <ZasticenaRuta dozvoljenaUloga={ULOGE.ISTRAZIVAC}>
                  <MojiRadovi />
                </ZasticenaRuta>
              }
            />

            <Route
              path="/recenzije"
              element={
                <ZasticenaRuta dozvoljenaUloga={ULOGE.RECENZENT}>
                  <Recenzije />
                </ZasticenaRuta>
              }
            />

            <Route
              path="/admin/korisnici"
              element={
                <ZasticenaRuta dozvoljenaUloga={ULOGE.ADMINISTRATOR}>
                  <AdminKorisnici />
                </ZasticenaRuta>
              }
            />

            <Route
              path="/admin/statistika"
              element={
                <ZasticenaRuta dozvoljenaUloga={ULOGE.ADMINISTRATOR}>
                  <AdminStatistika />
                </ZasticenaRuta>
              }
            />

            <Route
              path="*"
              element={
                <div className="container py-5">
                  <div className="alert alert-secondary">
                    <h5 className="alert-heading">Stranica nije pronađena</h5>
                    <p className="mb-0">Adresa koju ste otvorili ne postoji u aplikaciji.</p>
                  </div>
                </div>
              }
            />
          </Routes>
        </main>
      </BrowserRouter>
    </AuthProvider>
  )
}
