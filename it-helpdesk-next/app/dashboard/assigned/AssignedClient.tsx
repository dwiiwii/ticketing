'use client'
import { useState } from 'react'

const SLA_OPTIONS = ['15 Menit','30 Menit','1 Jam','2 Jam','4 Jam','8 Jam','1 Hari','3 Hari']

export default function AssignedClient({ tickets: initialTickets }: { tickets: any[] }) {
  const [tickets, setTickets] = useState(initialTickets)

  async function updateStatus(id: number, status: string) {
    await fetch(`/api/tickets/${id}`, { method:'PATCH', headers:{'Content-Type':'application/json'}, body: JSON.stringify({ status }) })
    setTickets(prev => status === 'Selesai'
      ? prev.filter(t => t.id !== id)
      : prev.map(t => t.id === id ? { ...t, status } : t)
    )
  }

  return (
    <div style={{ padding:32 }}>
      <div style={{ marginBottom:28 }}>
        <h1 style={{ fontSize:22, fontWeight:700, color:'#0f172a', margin:0 }}>Tugas Saya</h1>
        <p style={{ color:'#64748b', marginTop:4, fontSize:14 }}>Tiket yang sedang dalam proses penanganan</p>
      </div>

      {tickets.length === 0 ? (
        <div style={{ background:'#fff', borderRadius:16, boxShadow:'0 1px 8px rgba(0,0,0,0.06)', padding:60, textAlign:'center' }}>
          <div style={{ fontSize:48, marginBottom:12 }}>✅</div>
          <h2 style={{ fontSize:18, fontWeight:700, color:'#0f172a', marginBottom:8 }}>Semua Beres!</h2>
          <p style={{ color:'#64748b', fontSize:14 }}>Tidak ada tiket yang sedang diproses saat ini.</p>
        </div>
      ) : (
        <div style={{ display:'grid', gap:14 }}>
          {tickets.map((t: any) => (
            <div key={t.id} style={{ background:'#fff', borderRadius:14, boxShadow:'0 1px 8px rgba(0,0,0,0.06)', padding:22, display:'flex', gap:16, alignItems:'flex-start' }}>
              <div style={{ width:44, height:44, borderRadius:10, background:'#fef3c7', display:'flex', alignItems:'center', justifyContent:'center', fontSize:22, flexShrink:0 }}>⚙️</div>
              <div style={{ flex:1 }}>
                <div style={{ display:'flex', alignItems:'center', gap:10, marginBottom:4 }}>
                  <span style={{ fontWeight:700, fontSize:14, color:'#0f172a' }}>{t.subject}</span>
                  <span style={{ fontSize:12, fontWeight:600, color:'#0ea5e9' }}>{t.ticketNumber}</span>
                </div>
                <div style={{ fontSize:13, color:'#64748b' }}>{t.requesterName} · {t.user?.name || '-'} · {t.category}</div>
                {t.description && <div style={{ fontSize:13, color:'#94a3b8', marginTop:6, lineHeight:1.5 }}>{t.description}</div>}
              </div>
              <div style={{ display:'flex', gap:8 }}>
                <button onClick={()=>updateStatus(t.id,'Selesai')}
                  style={{ padding:'8px 16px', borderRadius:8, border:'none', background:'#dcfce7', color:'#16a34a', fontSize:12, fontWeight:700, cursor:'pointer' }}>
                  ✓ Selesai
                </button>
                <button onClick={()=>updateStatus(t.id,'Buka')}
                  style={{ padding:'8px 12px', borderRadius:8, border:'1px solid #e2e8f0', background:'#f8fafc', color:'#475569', fontSize:12, fontWeight:600, cursor:'pointer' }}>
                  Kembalikan
                </button>
              </div>
            </div>
          ))}
        </div>
      )}
    </div>
  )
}
