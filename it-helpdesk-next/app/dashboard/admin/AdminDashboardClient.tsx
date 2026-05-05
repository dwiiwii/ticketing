'use client'
import { useState, useEffect, useRef } from 'react'
import {
  Chart as ChartJS, CategoryScale, LinearScale, BarElement,
  Title, Tooltip, Legend
} from 'chart.js'
import { Bar } from 'react-chartjs-2'

ChartJS.register(CategoryScale, LinearScale, BarElement, Title, Tooltip, Legend)

const MONTH_NAMES = ['Januari','Februari','Maret','April','Mei','Juni',
                     'Juli','Agustus','September','Oktober','November','Desember']
const SLA_OPTIONS = ['15 Menit','30 Menit','1 Jam','2 Jam','4 Jam','8 Jam','1 Hari','3 Hari']

export default function AdminDashboardClient({ tickets: initialTickets, stats }: any) {
  const [tickets, setTickets] = useState(initialTickets)
  const [reportData, setReportData] = useState<any>(null)
  const [month, setMonth] = useState(new Date().getMonth() + 1)
  const [year, setYear] = useState(new Date().getFullYear())
  const [tab, setTab] = useState('daily')

  useEffect(() => {
    fetchReport()
  }, [month, year])

  async function fetchReport() {
    const res = await fetch(`/api/report?month=${month}&year=${year}`)
    const data = await res.json()
    setReportData(data)
  }

  async function updateStatus(id: number, status: string) {
    await fetch(`/api/tickets/${id}`, {
      method: 'PATCH',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ status }),
    })
    setTickets((prev: any[]) => prev.map(t => t.id === id ? { ...t, status } : t))
  }

  async function updateSla(id: number, sla: string) {
    await fetch(`/api/tickets/${id}`, {
      method: 'PATCH',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ sla }),
    })
    setTickets((prev: any[]) => prev.map(t => t.id === id ? { ...t, sla } : t))
  }

  async function deleteTicket(id: number) {
    if (!confirm('Hapus tiket ini?')) return
    await fetch(`/api/tickets/${id}`, { method: 'DELETE' })
    setTickets((prev: any[]) => prev.filter(t => t.id !== id))
  }

  function exportReport() {
    window.location.href = `/api/export?month=${month}&year=${year}`
  }

  const statusColor: Record<string, string> = {
    Buka: '#16a34a', Proses: '#d97706', Selesai: '#2563eb'
  }
  const statusBg: Record<string, string> = {
    Buka: '#dcfce7', Proses: '#fef3c7', Selesai: '#eff6ff'
  }

  const chartLabels = tab === 'daily' ? reportData?.daily?.labels : reportData?.monthly?.labels
  const chartData   = tab === 'daily' ? reportData?.daily?.data   : reportData?.monthly?.data

  return (
    <div style={{ padding: 32 }}>
      {/* Header */}
      <div style={{ marginBottom: 28 }}>
        <h1 style={{ fontSize: 22, fontWeight: 700, color: '#0f172a', margin: 0 }}>Ringkasan</h1>
        <p style={{ color: '#64748b', marginTop: 4, fontSize: 14 }}>
          {new Date().toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' })}
        </p>
      </div>

      {/* Stats Cards */}
      <div style={{ display: 'grid', gridTemplateColumns: 'repeat(4,1fr)', gap: 16, marginBottom: 28 }}>
        {[
          { label: 'Total Tiket', value: stats.totalTickets, color: '#0ea5e9', bg: '#f0f9ff' },
          { label: 'Tiket Terbuka', value: stats.bukaCount, color: '#16a34a', bg: '#f0fdf4' },
          { label: 'Sedang Diproses', value: stats.prosesCount, color: '#d97706', bg: '#fffbeb' },
          { label: 'Selesai', value: stats.selesaiCount, color: '#7c3aed', bg: '#faf5ff' },
        ].map(s => (
          <div key={s.label} style={{
            background: s.bg, borderRadius: 14, padding: '20px 24px',
            border: `1px solid ${s.color}22`
          }}>
            <div style={{ fontSize: 12, fontWeight: 600, color: s.color, textTransform: 'uppercase', letterSpacing: 0.5 }}>{s.label}</div>
            <div style={{ fontSize: 32, fontWeight: 800, color: s.color, marginTop: 4 }}>{s.value}</div>
          </div>
        ))}
      </div>

      {/* Ticket Table */}
      <div style={{ background: '#fff', borderRadius: 16, boxShadow: '0 1px 8px rgba(0,0,0,0.06)', padding: 24, marginBottom: 28, overflow: 'hidden' }}>
        <h2 style={{ fontSize: 16, fontWeight: 700, color: '#0f172a', marginBottom: 16 }}>Semua Tiket</h2>
        <div style={{ overflowX: 'auto' }}>
          <table style={{ width: '100%', borderCollapse: 'collapse', fontSize: 13 }}>
            <thead>
              <tr style={{ borderBottom: '1px solid #f1f5f9' }}>
                {['No.','No. Ticket','Nama','Divisi','Kategori','Subjek','Status','SLA','Diperbarui','Aksi'].map(h => (
                  <th key={h} style={{ padding: '10px 12px', textAlign: 'left', color: '#64748b', fontWeight: 600, whiteSpace: 'nowrap' }}>{h}</th>
                ))}
              </tr>
            </thead>
            <tbody>
              {tickets.map((t: any, i: number) => (
                <tr key={t.id} style={{ borderBottom: '1px solid #f8fafc' }}>
                  <td style={{ padding: '12px 12px', color: '#94a3b8' }}>{i + 1}</td>
                  <td style={{ padding: '12px 12px', fontWeight: 600, color: '#0ea5e9' }}>{t.ticketNumber}</td>
                  <td style={{ padding: '12px 12px', minWidth: 120 }}>{t.requesterName}</td>
                  <td style={{ padding: '12px 12px', minWidth: 100 }}>{t.user?.name || '-'}</td>
                  <td style={{ padding: '12px 12px', minWidth: 100 }}>{t.category}</td>
                  <td style={{ padding: '12px 12px', minWidth: 180 }}>{t.subject}</td>
                  <td style={{ padding: '12px 12px', minWidth: 120 }}>
                    <select
                      value={t.status}
                      onChange={e => updateStatus(t.id, e.target.value)}
                      style={{
                        padding: '4px 8px', borderRadius: 6, border: 'none', cursor: 'pointer',
                        fontSize: 12, fontWeight: 600,
                        color: statusColor[t.status] || '#334155',
                        background: statusBg[t.status] || '#f1f5f9',
                      }}
                    >
                      <option value="Buka">Buka</option>
                      <option value="Proses">Proses</option>
                      <option value="Selesai">Selesai</option>
                    </select>
                  </td>
                  <td style={{ padding: '12px 12px', minWidth: 120 }}>
                    <select
                      value={t.sla || ''}
                      onChange={e => updateSla(t.id, e.target.value)}
                      style={{ padding: '4px 8px', borderRadius: 6, border: '1px solid #e2e8f0', fontSize: 12, background: '#f8fafc', cursor: 'pointer' }}
                    >
                      <option value="">-</option>
                      {SLA_OPTIONS.map(s => <option key={s} value={s}>{s}</option>)}
                    </select>
                  </td>
                  <td style={{ padding: '12px 12px', color: '#94a3b8', minWidth: 160, fontSize: 12 }}>
                    {new Date(t.updatedAt).toLocaleDateString('id-ID', { day:'2-digit', month:'short', year:'numeric', hour:'2-digit', minute:'2-digit' })}
                  </td>
                  <td style={{ padding: '12px 12px' }}>
                    <button onClick={() => deleteTicket(t.id)} style={{
                      background: '#fee2e2', color: '#ef4444', border: 'none',
                      borderRadius: 6, padding: '5px 10px', cursor: 'pointer', fontSize: 12, fontWeight: 600
                    }}>Hapus</button>
                  </td>
                </tr>
              ))}
              {tickets.length === 0 && (
                <tr><td colSpan={10} style={{ padding: 32, textAlign: 'center', color: '#94a3b8' }}>Belum ada tiket.</td></tr>
              )}
            </tbody>
          </table>
        </div>
      </div>

      {/* Report Chart */}
      <div style={{ background: '#fff', borderRadius: 16, boxShadow: '0 1px 8px rgba(0,0,0,0.06)', padding: 24 }}>
        <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', flexWrap: 'wrap', gap: 12, marginBottom: 20 }}>
          <div>
            <h2 style={{ fontSize: 16, fontWeight: 700, color: '#0f172a', margin: 0 }}>Laporan Tiket Selesai</h2>
            <p style={{ color: '#64748b', fontSize: 13, marginTop: 2 }}>Grafik tiket yang sudah diselesaikan</p>
          </div>
          <div style={{ display: 'flex', gap: 10, flexWrap: 'wrap', alignItems: 'center' }}>
            <select value={month} onChange={e => setMonth(Number(e.target.value))}
              style={{ padding: '7px 12px', borderRadius: 8, border: '1px solid #e2e8f0', fontSize: 13, background: '#f8fafc' }}>
              {MONTH_NAMES.map((m, i) => <option key={m} value={i+1}>{m}</option>)}
            </select>
            <select value={year} onChange={e => setYear(Number(e.target.value))}
              style={{ padding: '7px 12px', borderRadius: 8, border: '1px solid #e2e8f0', fontSize: 13, background: '#f8fafc' }}>
              {[0,1,2,3].map(d => <option key={d} value={new Date().getFullYear()-d}>{new Date().getFullYear()-d}</option>)}
            </select>
            <button onClick={exportReport}
              style={{ display: 'flex', alignItems: 'center', gap: 6, padding: '7px 14px', borderRadius: 8, border: 'none', background: '#16a34a', color: '#fff', fontSize: 13, fontWeight: 600, cursor: 'pointer' }}>
              📊 Export Excel
            </button>
          </div>
        </div>

        {/* Stat cards */}
        {reportData && (
          <div style={{ display: 'flex', gap: 14, marginBottom: 20, flexWrap: 'wrap' }}>
            {[
              { label: 'Total Selesai', value: reportData.daily.data.reduce((a:number,b:number)=>a+b,0), color:'#16a34a', bg:'#f0fdf4' },
              { label: 'Divisi Terbanyak', value: reportData.division[0]?.divisi || '-', color:'#2563eb', bg:'#eff6ff' },
              { label: 'Rata-rata/Hari', value: (reportData.daily.data.reduce((a:number,b:number)=>a+b,0)/reportData.daysInMonth).toFixed(1), color:'#7c3aed', bg:'#faf5ff' },
            ].map(s => (
              <div key={s.label} style={{ flex:1, minWidth:140, background:s.bg, borderRadius:10, padding:'12px 18px', border:`1px solid ${s.color}33` }}>
                <div style={{ fontSize:11, color:s.color, fontWeight:700, textTransform:'uppercase', letterSpacing:0.5 }}>{s.label}</div>
                <div style={{ fontSize:22, fontWeight:800, color:s.color, marginTop:4 }}>{s.value}</div>
              </div>
            ))}
          </div>
        )}

        {/* Tabs */}
        <div style={{ display:'flex', marginBottom:16 }}>
          {['daily','monthly'].map(t => (
            <button key={t} onClick={() => setTab(t)}
              style={{
                padding:'6px 18px', border:'1px solid #e2e8f0', borderRadius: t==='daily' ? '6px 0 0 6px' : '0 6px 6px 0',
                borderLeft: t==='monthly' ? 'none' : undefined, cursor:'pointer', fontSize:12, fontWeight:600,
                background: tab===t ? '#1e293b':'#f8fafc', color: tab===t ? '#fff':'#64748b'
              }}>
              {t === 'daily' ? 'Harian' : 'Bulanan'}
            </button>
          ))}
        </div>

        {/* Chart */}
        <div style={{ height: 280 }}>
          {reportData && chartLabels && (
            <Bar
              data={{
                labels: chartLabels,
                datasets: [{
                  label: 'Tiket Selesai',
                  data: chartData,
                  backgroundColor: 'rgba(22,163,74,0.3)',
                  borderColor: '#16a34a',
                  borderWidth: 2,
                  borderRadius: 6,
                }]
              }}
              options={{
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                  y: { beginAtZero: true, ticks: { stepSize: 1, color: '#94a3b8' }, grid: { color: '#f1f5f9' } },
                  x: { ticks: { color: '#94a3b8', maxRotation: 0 }, grid: { display: false } }
                }
              }}
            />
          )}
        </div>

        {/* Division breakdown */}
        {reportData?.division?.length > 0 && (
          <div style={{ marginTop: 20 }}>
            <div style={{ fontSize: 13, fontWeight: 600, color: '#334155', marginBottom: 10 }}>Rincian per Divisi</div>
            {reportData.division.map((d: any, i: number) => {
              const colors = ['#16a34a','#2563eb','#7c3aed','#ea580c','#0891b2']
              const maxVal = reportData.division[0].total
              const pct = Math.round((d.total / maxVal) * 100)
              return (
                <div key={d.divisi} style={{ display:'flex', alignItems:'center', gap:10, marginBottom:8 }}>
                  <div style={{ minWidth:120, fontSize:13, fontWeight:500, color:'#334155' }}>{d.divisi}</div>
                  <div style={{ flex:1, background:'#f1f5f9', borderRadius:99, height:8, overflow:'hidden' }}>
                    <div style={{ width:`${pct}%`, background:colors[i%colors.length], height:'100%', borderRadius:99 }} />
                  </div>
                  <div style={{ minWidth:30, fontSize:13, fontWeight:700, color:colors[i%colors.length] }}>{d.total}</div>
                </div>
              )
            })}
          </div>
        )}
      </div>
    </div>
  )
}
