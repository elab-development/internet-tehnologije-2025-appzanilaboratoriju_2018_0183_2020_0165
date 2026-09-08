export default function Input({
  naziv,
  labela,
  tip = 'text',
  vrednost,
  onChange,
  greska = null,
  obavezno = false,
  placeholder = '',
  redova = 4,
  onemoguceno = false,
  dodatneKlase = '',
}) {
  const klase = ['form-control', greska ? 'is-invalid' : '', dodatneKlase].filter(Boolean).join(' ')

  const zajednickiPropovi = {
    id: naziv,
    name: naziv,
    className: klase,
    value: vrednost,
    onChange,
    required: obavezno,
    placeholder,
    disabled: onemoguceno,
  }

  return (
    <div className="mb-3">
      {labela && (
        <label className="form-label" htmlFor={naziv}>
          {labela}
          {obavezno && <span className="text-danger ms-1">*</span>}
        </label>
      )}

      {tip === 'textarea' ? (
        <textarea {...zajednickiPropovi} rows={redova} />
      ) : (
        <input {...zajednickiPropovi} type={tip} />
      )}

      {greska && <div className="invalid-feedback">{greska}</div>}
    </div>
  )
}
