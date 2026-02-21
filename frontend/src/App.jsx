
import { useEffect, useState } from 'react'
import reactLogo from './assets/react.svg'
import viteLogo from '/vite.svg'
import './App.css'
import axios from 'axios'

function App() {
  const [radovi, setRadovi] = useState([])

  useEffect(() => {
    // Menjaj URL ako ti Laravel ne radi na portu 8000
    axios.get('http://localhost:8000/api/moji-radovi') 
      .then(res => {
        console.log("Podaci stigli:", res.data.data)
        setRadovi(res.data.data)
      })
      .catch(err => console.error("Greška:", err))
  }, [])

  return (
    <div style={{ padding: '20px' }}>
      <h1>Test povezivanja Laravela i Reacta</h1>
      <ul>
        {radovi.map(rad => (
          <li key={rad.id || rad.recenzija_id}>{rad.naslov || "Nema naslova"}</li>
        ))}
      </ul>
    </div>
  )
}

export default App