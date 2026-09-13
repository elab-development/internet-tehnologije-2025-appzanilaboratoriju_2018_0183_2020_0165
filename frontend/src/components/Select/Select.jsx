export default function Select({
  naziv,
  labela,
  vrednost,
  onChange,
  opcije = [],
  prazanTekst = null,
  prazanKaoPlaceholder = false,
  greska = null,
  obavezno = false,
  onemoguceno = false,
  dodatneKlase = '',
}) {
  const klase = ['form-select', greska ? 'is-invalid' : '', dodatneKlase].filter(Boolean).join(' ')

  return (
    <div className="mb-3">
      {labela && (
        <label className="form-label" htmlFor={naziv}>
          {labela}
          {obavezno && <span className="text-danger ms-1">*</span>}
        </label>
      )}

      <select
        id={naziv}
        name={naziv}
        className={klase}
        value={vrednost}
        onChange={onChange}
        required={obavezno}
        disabled={onemoguceno}
      >
        {prazanTekst && (
          <option value="" disabled={prazanKaoPlaceholder} hidden={prazanKaoPlaceholder}>
            {prazanTekst}
          </option>
        )}
        {opcije.map((opcija) => (
          <option key={opcija.vrednost} value={opcija.vrednost}>
            {opcija.tekst}
          </option>
        ))}
      </select>

      {greska && <div className="invalid-feedback">{greska}</div>}
    </div>
  )
}
