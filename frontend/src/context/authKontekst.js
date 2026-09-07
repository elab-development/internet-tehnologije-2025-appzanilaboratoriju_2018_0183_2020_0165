import { createContext } from 'react'

export const AuthContext = createContext(null)

export const ULOGE = {
  ADMINISTRATOR: 1,
  RECENZENT: 2,
  ISTRAZIVAC: 3,
}

export const NAZIV_ULOGE = {
  1: 'Administrator',
  2: 'Recenzent',
  3: 'Istraživač',
}
