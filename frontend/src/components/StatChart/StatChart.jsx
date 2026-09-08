import {
  Bar,
  BarChart,
  Cell,
  Legend,
  Pie,
  PieChart,
  ResponsiveContainer,
  Tooltip,
  XAxis,
  YAxis,
} from 'recharts'

const BOJE = ['#0d6efd', '#198754', '#ffc107', '#dc3545', '#6f42c1', '#20c997']

function natpisUKrisci({ cx, cy, midAngle, innerRadius, outerRadius, value }) {
  const ugao = -midAngle * (Math.PI / 180)
  const rastojanje = innerRadius + (outerRadius - innerRadius) * 0.6

  return (
    <text
      x={cx + rastojanje * Math.cos(ugao)}
      y={cy + rastojanje * Math.sin(ugao)}
      fill="#fff"
      fontSize={13}
      fontWeight="600"
      textAnchor="middle"
      dominantBaseline="central"
    >
      {value}
    </text>
  )
}

export default function StatChart({ podaci = [], tip = 'bar', naslov = '', visina = 320 }) {
  const imaPodataka = podaci.length > 0 && podaci.some((stavka) => stavka.vrednost > 0)

  if (!imaPodataka) {
    return (
      <div className="card h-100">
        {naslov && <div className="card-header">{naslov}</div>}
        <div className="card-body">
          <div className="alert alert-secondary mb-0">Nema podataka za prikaz.</div>
        </div>
      </div>
    )
  }

  return (
    <div className="card h-100">
      {naslov && <div className="card-header">{naslov}</div>}
      <div className="card-body">
        <ResponsiveContainer width="100%" height={visina}>
          {tip === 'pie' ? (
            <PieChart>
              <Pie
                data={podaci}
                dataKey="vrednost"
                nameKey="oznaka"
                outerRadius="80%"
                isAnimationActive={false}
                labelLine={false}
                label={natpisUKrisci}
              >
                {podaci.map((stavka, indeks) => (
                  <Cell key={stavka.oznaka} fill={BOJE[indeks % BOJE.length]} />
                ))}
              </Pie>
              <Tooltip />
              <Legend />
            </PieChart>
          ) : (
            <BarChart data={podaci} margin={{ top: 8, right: 8, left: -20, bottom: 8 }}>
              <XAxis dataKey="oznaka" tick={{ fontSize: 12 }} />
              <YAxis allowDecimals={false} tick={{ fontSize: 12 }} />
              <Tooltip />
              <Bar dataKey="vrednost" name="Broj radova" radius={[4, 4, 0, 0]} isAnimationActive={false}>
                {podaci.map((stavka, indeks) => (
                  <Cell key={stavka.oznaka} fill={BOJE[indeks % BOJE.length]} />
                ))}
              </Bar>
            </BarChart>
          )}
        </ResponsiveContainer>
      </div>
    </div>
  )
}
