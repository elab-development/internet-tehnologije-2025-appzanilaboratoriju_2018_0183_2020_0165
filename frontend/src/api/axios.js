import axios from 'axios'

const api = axios.create({
  baseURL: 'http://localhost:8000/api',
  headers: {
    Accept: 'application/json',
  },
})

api.interceptors.request.use((config) => {
  const token = localStorage.getItem('token')
  if (token) {
    config.headers.Authorization = `Bearer ${token}`
  }
  return config
})

function odjaviIPreusmeri() {
  localStorage.removeItem('token')
  localStorage.removeItem('korisnik')
  localStorage.removeItem('ulogaId')
  if (window.location.pathname !== '/prijava') {
    window.location.href = '/prijava'
  }
}

api.interceptors.response.use(
  (response) => response,
  (error) => {
    const status = error.response?.status

    if (status === 401) {
      odjaviIPreusmeri()
    }

    if (status === 403 && error.response?.data?.kod === 'pogresna_uloga_u_tokenu') {
      odjaviIPreusmeri()
    }

    return Promise.reject(error)
  }
)

export default api
