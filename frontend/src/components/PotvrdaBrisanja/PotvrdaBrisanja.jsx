import Button from '../Button/Button'
import Modal from '../Modal/Modal'

export default function PotvrdaBrisanja({
  otvoren = false,
  naslov = 'Potvrda brisanja',
  poruka = 'Da li ste sigurni da želite da obrišete ovu stavku?',
  stavka = null,
  tekstPotvrde = 'Obriši',
  ucitava = false,
  naPotvrdu,
  naOtkaz,
}) {
  return (
    <Modal
      naslov={naslov}
      otvoren={otvoren}
      naZatvaranje={ucitava ? undefined : naOtkaz}
      velicina="malo"
      zatvaranjeKlikomVan={!ucitava}
      podnozje={
        <>
          <Button varijanta="outline" onClick={naOtkaz} onemoguceno={ucitava}>
            Odustani
          </Button>
          <Button varijanta="danger" onClick={naPotvrdu} ucitava={ucitava}>
            {tekstPotvrde}
          </Button>
        </>
      }
    >
      <p className="mb-2">{poruka}</p>

      {stavka && <p className="fw-semibold mb-2">{stavka}</p>}

      <p className="text-danger small mb-0">Ova radnja je trajna i ne može se poništiti.</p>
    </Modal>
  )
}
