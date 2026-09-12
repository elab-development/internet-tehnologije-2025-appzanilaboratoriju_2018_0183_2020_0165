import api from '../api/axios'

export async function ucitajFajlRada(idRada) {
  const odgovor = await api.get(`/radovi/${idRada}/fajl`, { responseType: 'blob' })

  return window.URL.createObjectURL(new Blob([odgovor.data], { type: 'application/pdf' }))
}

export function sacuvajNaDisk(adresaFajla, imeFajla) {
  const veza = document.createElement('a')
  veza.href = adresaFajla
  veza.download = imeFajla || 'rad.pdf'
  document.body.appendChild(veza)
  veza.click()
  veza.remove()
}

export async function preuzmiFajlRada(idRada, imeFajla) {
  const adresa = await ucitajFajlRada(idRada)

  sacuvajNaDisk(adresa, imeFajla)
  window.URL.revokeObjectURL(adresa)
}
