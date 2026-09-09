import { useCallback, useEffect, useMemo, useState } from 'react'
import api from '../api/axios'

function izvuciListu(odgovor) {
  if (Array.isArray(odgovor)) return odgovor
  if (Array.isArray(odgovor?.data)) return odgovor.data
  if (Array.isArray(odgovor?.podaci)) return odgovor.podaci
  return []
}

export default function usePaginatedFetch(putanja, parametri = {}, poStrani = 6) {
  const [sviPodaci, setSviPodaci] = useState([])
  const [ucitava, setUcitava] = useState(true)
  const [greska, setGreska] = useState(null)
  const [strana, setStrana] = useState(1)

  const kljucParametara = JSON.stringify(parametri)

  const dohvati = useCallback(async () => {
    setUcitava(true)
    setGreska(null)

    try {
      const { data } = await api.get(putanja, { params: JSON.parse(kljucParametara) })
      setSviPodaci(izvuciListu(data))
    } catch (error) {
      setGreska(error.response?.data?.message ?? 'Podaci trenutno nisu dostupni.')
      setSviPodaci([])
    } finally {
      setUcitava(false)
    }
  }, [putanja, kljucParametara])

  useEffect(() => {
    dohvati()
  }, [dohvati])

  useEffect(() => {
    setStrana(1)
  }, [kljucParametara])

  const ukupnoStrana = Math.max(1, Math.ceil(sviPodaci.length / poStrani))

  const trenutnaStrana = Math.min(strana, ukupnoStrana)

  const podaci = useMemo(() => {
    const pocetak = (trenutnaStrana - 1) * poStrani
    return sviPodaci.slice(pocetak, pocetak + poStrani)
  }, [sviPodaci, trenutnaStrana, poStrani])

  return {
    podaci,
    sviPodaci,
    ucitava,
    greska,
    strana: trenutnaStrana,
    ukupnoStrana,
    ukupnoStavki: sviPodaci.length,
    promeniStranu: setStrana,
    osvezi: dohvati,
  }
}
