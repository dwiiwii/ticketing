'use client'
import { useState } from 'react'

export default function TrackTicketPage() {
  const [query, setQuery] = useState('')
  const [ticket, setTicket] = useState<any>(null)
  const [error, setError] = useState('')
  const [loading, setLoading] = useState(false)

  async function handleSearch(e: React.FormEvent) {
    e.preventDefault()
    if (!query.trim()) return
    setLoading(true)
    setError('')
    setTicket(null)

    const res = await fetch(`/api/tickets?ticketNumber=${encodeURIComponent(query.trim())}`)
    const data = await res.json()
    const found = Array.isArray(data) ? data.find((t:any) => t.ticketNumber === query.trim()) : null

    if (found) setTicket(found)
    else setError(`Tiket dengan nomor "${query}" tidak ditemukan.`)
    setLoading(false)
  }

  const statusLower = ticket?.status?.toLowerCase() || ''
  const isCreated  = true
  const isProgress = ['proses','selesai'].includes(statusLower)
  const isDone     = statusLower === 'selesai'

  function StepCircle({ done, active, icon }: { done:boolean, active:boolean, icon:string }) {
    const bg = done ? '#10b981' : active ? '#0ea5e9' : '#e2e8f0'
    const color = (done || active) ? '#fff' : '#94a3b8'
    return (
      <div style={{ width:42, height:42, borderRadius:'50%', background:bg, display:'flex', alignItems:'center', justifyContent:'center', fontSize:18, color, flexShrink:0, boxShadow: active ? `0 0 0 4px ${bg}33` : 'none' }}>
        {icon}
      </div>
    )
  }

  return (
    <div style={{ padding:32 }}>
      <div style={{ maxWidth:680, margin:'0 auto' }}>
        <div style={{ marginBottom:28 }}>
          <h1 style={{ fontSize:22, fontWeight:700, color:'#0f172a', margin:0 }}>Cek Status Tiket</h1>
        </div>

        <div style={{ background:'#fff', borderRadius:16, boxShadow:'0 1px 8px rgba(0,0,0,0.06)', padding:32 }}>
          {/* Search */}
          <form onSubmit={handleSearch} style={{ display:'flex', gap:10, marginBottom:24 }}>
            <input
              type="text" value={query} onChange={e => setQuery(e.target.value)}
              placeholder="Masukkan Nomor Tiket (Contoh: FR-1)"
              style={{ flex:1, padding:'13px 16px', borderRadius:10, border:'1.5px solid #e2e8f0', fontSize:15, outline:'none', background:'#f8fafc' }}
            />
            <button type="submit" disabled={loading}
              style={{ padding:'0 22px', borderRadius:10, border:'none', background:'#0f172a', color:'#fff', fontSize:14, fontWeight:700, cursor:'pointer' }}>
              {loading ? '⌛' : '🔍 Lacak'}
            </button>
          </form>

          {/* Error */}
          {error && (
            <div style={{ background:'#fee2e2', color:'#991b1b', padding:'13px 16px', borderRadius:10, fontSize:13 }}>⚠️ {error}</div>
          )}

          {/* Result */}
          {ticket && (
            <>
              {/* Detail Box */}
              <div style={{ display:'grid', gridTemplateColumns:'1fr 1fr', gap:14, padding:20, background:'rgba(0,0,0,0.02)', borderRadius:12, border:'1px dashed #e2e8f0', marginBottom:28 }}>
                {[
                  { label:'Nomor Tiket', value: ticket.ticketNumber, blue: true },
                  { label:'Nama Pengaju', value: ticket.requesterName },
                  { label:'Subjek', value: ticket.subject },
                  { label:'Kategori', value: ticket.category },
                ].map(item => (
                  <div key={item.label}>
                    <div style={{ fontSize:11, color:'#94a3b8', textTransform:'uppercase', letterSpacing:0.5, fontWeight:600 }}>{item.label}</div>
                    <div style={{ fontSize:14, fontWeight:500, marginTop:4, color: item.blue ? '#0ea5e9':'#0f172a' }}>{item.value}</div>
                  </div>
                ))}
              </div>

              {/* Timeline */}
              <h3 style={{ fontSize:15, fontWeight:600, color:'#0f172a', marginBottom:16 }}>Linimasa Penanganan</h3>
              <div style={{ position:'relative', display:'flex', flexDirection:'column', gap:20 }}>
                <div style={{ position:'absolute', left:20, top:20, bottom:20, width:2, background:'#e2e8f0', zIndex:0 }} />

                {[
                  { label:'Tiket Dibuat', desc:`Tiket telah diterima oleh sistem pada ${new Date(ticket.createdAt).toLocaleDateString('id-ID',{day:'2-digit',month:'short',year:'numeric',hour:'2-digit',minute:'2-digit'})}.`, done: isProgress, active: !isProgress, icon: isProgress ? '✓':'📄' },
                  { label:'Sedang Diproses', desc:'Tim IT sedang memeriksa dan memperbaiki masalah.', done: isDone, active: isProgress && !isDone, icon: isDone ? '✓':'⚙️' },
                  { label:'Selesai Diperbaiki', desc: isDone ? 'Masalah telah berhasil diselesaikan oleh Tim IT.':'Menunggu perbaikan selesai.', done: false, active: isDone, icon:'🏁' },
                ].map(step => (
                  <div key={step.label} style={{ display:'flex', gap:16, position:'relative', zIndex:1 }}>
                    <StepCircle done={step.done} active={step.active} icon={step.icon} />
                    <div style={{ background:'#f8fafc', border:'1px solid #f1f5f9', borderRadius:12, padding:'14px 18px', flex:1 }}>
                      <div style={{ fontWeight:600, fontSize:14, color:'#0f172a' }}>{step.label}</div>
                      <div style={{ fontSize:13, color:'#64748b', marginTop:4 }}>{step.desc}</div>
                    </div>
                  </div>
                ))}
              </div>
            </>
          )}
        </div>
      </div>
    </div>
  )
}
