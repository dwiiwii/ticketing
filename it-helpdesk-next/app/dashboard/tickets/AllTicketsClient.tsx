'use client'
import { useState } from 'react'

const SLA_OPTIONS = ['15 Menit','30 Menit','1 Jam','2 Jam','4 Jam','8 Jam','1 Hari','3 Hari']
const statusColor: Record<string,string> = { Buka:'#16a34a', Proses:'#d97706', Selesai:'#2563eb' }
const statusBg:    Record<string,string> = { Buka:'#dcfce7', Proses:'#fef3c7', Selesai:'#eff6ff' }

export default function AllTicketsClient({ tickets: initialTickets }: { tickets: any[] }) {
  const [tickets, setTickets] = useState(initialTickets)
  const [search, setSearch] = useState('')
  const [filterStatus, setFilterStatus] = useState('')
  const [filterDiv, setFilterDiv] = useState('')

  async function updateStatus(id: number, status: string) {
    await fetch(`/api/tickets/${id}`, { method:'PATCH', headers:{'Content-Type':'application/json'}, body: JSON.stringify({ status }) })
    setTickets(prev => prev.map(t => t.id === id ? { ...t, status } : t))
  }
  async function updateSla(id: number, sla: string) {
    await fetch(`/api/tickets/${id}`, { method:'PATCH', headers:{'Content-Type':'application/json'}, body: JSON.stringify({ sla }) })
    setTickets(prev => prev.map(t => t.id === id ? { ...t, sla } : t))
  }
  async function deleteTicket(id: number) {
    if (!confirm('Hapus tiket ini?')) return
    await fetch(`/api/tickets/${id}`, { method:'DELETE' })
    setTickets(prev => prev.filter(t => t.id !== id))
  }

  const divisions = [...new Set(tickets.map((t:any) => t.user?.name).filter(Boolean))]

  const filtered = tickets.filter((t:any) => {
    const q = search.toLowerCase()
    const matchSearch = !q || t.ticketNumber.toLowerCase().includes(q) || t.requesterName.toLowerCase().includes(q) || t.subject.toLowerCase().includes(q)
    const matchStatus = !filterStatus || t.status === filterStatus
    const matchDiv = !filterDiv || t.user?.name === filterDiv
    return matchSearch && matchStatus && matchDiv
  })

  return (
    <div style={{ padding:32 }}>
      <div style={{ marginBottom:24 }}>
        <h1 style={{ fontSize:22, fontWeight:700, color:'#0f172a', margin:0 }}>Semua Tiket</h1>
        <p style={{ color:'#64748b', marginTop:4, fontSize:14 }}>Kelola seluruh tiket dari semua divisi</p>
      </div>

      {/* Filter bar */}
      <div style={{ display:'flex', gap:12, marginBottom:20, flexWrap:'wrap' }}>
        <input
          type="text" placeholder="Cari tiket, nama, atau subjek..."
          value={search} onChange={e => setSearch(e.target.value)}
          style={{ flex:1, minWidth:220, padding:'9px 14px', borderRadius:9, border:'1px solid #e2e8f0', fontSize:13, outline:'none', background:'#fff' }}
        />
        <select value={filterStatus} onChange={e => setFilterStatus(e.target.value)}
          style={{ padding:'9px 12px', borderRadius:9, border:'1px solid #e2e8f0', fontSize:13, background:'#fff', cursor:'pointer' }}>
          <option value="">Semua Status</option>
          <option value="Buka">Buka</option>
          <option value="Proses">Proses</option>
          <option value="Selesai">Selesai</option>
        </select>
        <select value={filterDiv} onChange={e => setFilterDiv(e.target.value)}
          style={{ padding:'9px 12px', borderRadius:9, border:'1px solid #e2e8f0', fontSize:13, background:'#fff', cursor:'pointer' }}>
          <option value="">Semua Divisi</option>
          {divisions.map((d:any) => <option key={d} value={d}>{d}</option>)}
        </select>
      </div>

      <div style={{ background:'#fff', borderRadius:16, boxShadow:'0 1px 8px rgba(0,0,0,0.06)', padding:24, overflow:'hidden' }}>
        <div style={{ display:'flex', justifyContent:'space-between', alignItems:'center', marginBottom:16 }}>
          <span style={{ fontSize:13, color:'#64748b' }}>Menampilkan <strong>{filtered.length}</strong> dari {tickets.length} tiket</span>
        </div>
        <div style={{ overflowX:'auto' }}>
          <table style={{ width:'100%', borderCollapse:'collapse', fontSize:13 }}>
            <thead>
              <tr style={{ borderBottom:'1px solid #f1f5f9' }}>
                {['No.','No. Ticket','Nama','Divisi','Kategori','Subjek','Status','SLA','Diperbarui','Aksi'].map(h=>(
                  <th key={h} style={{ padding:'10px 12px', textAlign:'left', color:'#64748b', fontWeight:600, whiteSpace:'nowrap' }}>{h}</th>
                ))}
              </tr>
            </thead>
            <tbody>
              {filtered.map((t:any, i:number) => (
                <tr key={t.id} style={{ borderBottom:'1px solid #f8fafc' }}>
                  <td style={{ padding:'12px 12px', color:'#94a3b8' }}>{i+1}</td>
                  <td style={{ padding:'12px 12px', fontWeight:600, color:'#0ea5e9' }}>{t.ticketNumber}</td>
                  <td style={{ padding:'12px 12px', minWidth:120 }}>{t.requesterName}</td>
                  <td style={{ padding:'12px 12px', minWidth:100 }}>{t.user?.name || '-'}</td>
                  <td style={{ padding:'12px 12px', minWidth:100 }}>{t.category}</td>
                  <td style={{ padding:'12px 12px', minWidth:180 }}>{t.subject}</td>
                  <td style={{ padding:'12px 12px', minWidth:120 }}>
                    <select value={t.status} onChange={e=>updateStatus(t.id,e.target.value)}
                      style={{ padding:'4px 8px', borderRadius:6, border:'none', cursor:'pointer', fontSize:12, fontWeight:600, color:statusColor[t.status]||'#334155', background:statusBg[t.status]||'#f1f5f9' }}>
                      <option value="Buka">Buka</option>
                      <option value="Proses">Proses</option>
                      <option value="Selesai">Selesai</option>
                    </select>
                  </td>
                  <td style={{ padding:'12px 12px', minWidth:120 }}>
                    <select value={t.sla||''} onChange={e=>updateSla(t.id,e.target.value)}
                      style={{ padding:'4px 8px', borderRadius:6, border:'1px solid #e2e8f0', fontSize:12, background:'#f8fafc', cursor:'pointer' }}>
                      <option value="">-</option>
                      {SLA_OPTIONS.map(s=><option key={s} value={s}>{s}</option>)}
                    </select>
                  </td>
                  <td style={{ padding:'12px 12px', color:'#94a3b8', minWidth:160, fontSize:12 }}>
                    {new Date(t.updatedAt).toLocaleDateString('id-ID',{day:'2-digit',month:'short',year:'numeric',hour:'2-digit',minute:'2-digit'})}
                  </td>
                  <td style={{ padding:'12px 12px' }}>
                    <button onClick={()=>deleteTicket(t.id)} style={{ background:'#fee2e2', color:'#ef4444', border:'none', borderRadius:6, padding:'5px 10px', cursor:'pointer', fontSize:12, fontWeight:600 }}>Hapus</button>
                  </td>
                </tr>
              ))}
              {filtered.length === 0 && (
                <tr><td colSpan={10} style={{ padding:32, textAlign:'center', color:'#94a3b8' }}>Tidak ada tiket ditemukan.</td></tr>
              )}
            </tbody>
          </table>
        </div>
      </div>
    </div>
  )
}
