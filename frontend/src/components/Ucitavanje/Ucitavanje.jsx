export default function Ucitavanje({ tekst = 'Učitavanje...', visina = '200px' }) {
  return (
    <div
      className="d-flex flex-column align-items-center justify-content-center text-secondary"
      style={{ minHeight: visina }}
    >
      <div className="spinner-border mb-2" role="status">
        <span className="visually-hidden">{tekst}</span>
      </div>
      <span className="small">{tekst}</span>
    </div>
  )
}
