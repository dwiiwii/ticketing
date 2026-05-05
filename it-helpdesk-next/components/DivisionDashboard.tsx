'use client'
import { useState } from 'react'
import Link from 'next/link'

const statusColor: Record<string, string> = { Buka:'#16a34a', Proses:'#d97706', Selesai:'#2563eb' }
const statusBg:    Record<string, string> = { Buka:'#dcfce7', Proses:'#fef3c7', Selesai:'#eff6ff' }

export default function DivisionDashboard({ title, tickets: initialTickets }: { title: string, tickets: any[] }) {
  const [tickets, setTickets] = useState(initialTickets)

  return (
    <div style={{ padding: 32 }}>
      {/* Header */}
      <div style={{ display:'flex', justifyContent:'space-between', alignItems:'center', marginBottom: 28 }}>
        <h1 style={{ fontSize: 22, fontWeight: 700, color: '#0f172a', margin: 0 }}>{title}</h1>
        <Link href="/dashboard/add-ticket"
          style={{ display:'flex', alignItems:'center', gap:6, padding:'10px 18px', background:'#0f172a', color:'#fff',
            borderRadius:10, textDecoration:'none', fontSize:14, fontWeight:600 }}>
          + Buat Tiket
        </Link>
      </div>

      {/* Table */}
      <div style={{ background:'#fff', borderRadius:16, boxShadow:'0 1px 8px rgba(0,0,0,0.06)', padding:24, overflow:'hidden' }}>
        <div style={{ overflowX: 'auto' }}>
          <table style={{ width:'100%', borderCollapse:'collapse', fontSize:13 }}>
            <thead>
              <tr style={{ borderBottom:'1px solid #f1f5f9' }}>
                {['No.','No. Ticket','Nama','Kategori','Subjek','Status','SLA','Diperbarui'].map(h => (
                  <th key={h} style={{ padding:'10px 12px', textAlign:'left', color:'#64748b', fontWeight:600, whiteSpace:'nowrap' }}>{h}</th>
                ))}
              </tr>
            </thead>
            <tbody>
              {tickets.map((t: any, i: number) => (
                <tr key={t.id} style={{ borderBottom:'1px solid #f8fafc' }}>
                  <td style={{ padding:'12px 12px', color:'#94a3b8' }}>{i+1}</td>
                  <td style={{ padding:'12px 12px', fontWeight:600, color:'#0ea5e9' }}>{t.ticketNumber}</td>
                  <td style={{ padding:'12px 12px', minWidth:140 }}>{t.requesterName}</td>
                  <td style={{ padding:'12px 12px', minWidth:120 }}>{t.category}</td>
                  <td style={{ padding:'12px 12px', minWidth:200 }}>{t.subject}</td>
                  <td style={{ padding:'12px 12px', minWidth:100 }}>
                    <span style={{
                      padding:'4px 10px', borderRadius:6, fontSize:12, fontWeight:600,
                      color: statusColor[t.status] || '#334155',
                      background: statusBg[t.status] || '#f1f5f9',
                    }}>{t.status}</span>
                  </td>
                  <td style={{ padding:'12px 12px', minWidth:100, color: t.sla ? '#334155' : '#94a3b8' }}>
                    {t.sla || '-'}
                  </td>
                  <td style={{ padding:'12px 12px', color:'#94a3b8', minWidth:160, fontSize:12 }}>
                    {new Date(t.updatedAt).toLocaleDateString('id-ID', { day:'2-digit', month:'short', year:'numeric', hour:'2-digit', minute:'2-digit' })}
                  </td>
                </tr>
              ))}
              {tickets.length === 0 && (
                <tr><td colSpan={8} style={{ padding:32, textAlign:'center', color:'#94a3b8' }}>Belum ada tiket.</td></tr>
              )}
            </tbody>
          </table>
        </div>
      </div>
    </div>
  )
}
