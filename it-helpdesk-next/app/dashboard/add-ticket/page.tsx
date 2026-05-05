'use client'
import { useState } from 'react'
import { useRouter } from 'next/navigation'
import { useSession } from 'next-auth/react'

const CATEGORIES = ['Hardware','Software','Jaringan','Akun & Akses','Printer','Lainnya']

export default function AddTicketPage() {
  const { data: session } = useSession()
  const router = useRouter()
  const [form, setForm] = useState({ subject:'', category:'', requesterName:'', description:'' })
  const [file, setFile] = useState<File|null>(null)
  const [loading, setLoading] = useState(false)
  const [error, setError] = useState('')

  function handleChange(e: React.ChangeEvent<HTMLInputElement|HTMLSelectElement|HTMLTextAreaElement>) {
    setForm(prev => ({ ...prev, [e.target.name]: e.target.value }))
  }

  async function handleSubmit(e: React.FormEvent) {
    e.preventDefault()
    setLoading(true)
    setError('')

    try {
      let attachmentUrl = null

      // Upload file to Cloudinary if selected
      if (file) {
        const fd = new FormData()
        fd.append('file', file)
        const upRes = await fetch('/api/upload', { method:'POST', body: fd })
        const upData = await upRes.json()
        attachmentUrl = upData.url
      }

      const res = await fetch('/api/tickets', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          subject: form.subject,
          category: form.category,
          requesterName: form.requesterName || session?.user?.name,
          description: form.description,
          attachment: attachmentUrl,
        }),
      })

      if (!res.ok) throw new Error('Gagal membuat tiket')
      router.push('/')
      router.refresh()
    } catch (err: any) {
      setError(err.message || 'Terjadi kesalahan')
      setLoading(false)
    }
  }

  const inputStyle: React.CSSProperties = {
    width:'100%', padding:'11px 14px', borderRadius:10, border:'1.5px solid #e2e8f0',
    fontSize:14, outline:'none', background:'#f8fafc', color:'#0f172a', transition:'border 0.2s',
    fontFamily: "'Inter', sans-serif",
  }
  const labelStyle: React.CSSProperties = { fontSize:13, fontWeight:600, color:'#334155', display:'block', marginBottom:7 }

  return (
    <div style={{ padding:32, maxWidth:700, margin:'0 auto' }}>
      <div style={{ marginBottom:28 }}>
        <h1 style={{ fontSize:22, fontWeight:700, color:'#0f172a', margin:0 }}>Buat Tiket Baru</h1>
        <p style={{ color:'#64748b', marginTop:4, fontSize:14 }}>Isi formulir berikut untuk membuat permintaan IT</p>
      </div>

      <div style={{ background:'#fff', borderRadius:16, boxShadow:'0 1px 8px rgba(0,0,0,0.06)', padding:32 }}>
        <form onSubmit={handleSubmit}>
          <div style={{ display:'grid', gap:20 }}>

            {/* Requester Name */}
            <div>
              <label style={labelStyle}>Nama Pengaju</label>
              <input
                name="requesterName" type="text"
                value={form.requesterName || session?.user?.name || ''}
                onChange={handleChange}
                placeholder="Nama lengkap"
                style={inputStyle}
              />
            </div>

            {/* Category */}
            <div>
              <label style={labelStyle}>Kategori <span style={{color:'#ef4444'}}>*</span></label>
              <select name="category" value={form.category} onChange={handleChange} required style={inputStyle}>
                <option value="">Pilih kategori...</option>
                {CATEGORIES.map(c => <option key={c} value={c}>{c}</option>)}
              </select>
            </div>

            {/* Subject */}
            <div>
              <label style={labelStyle}>Subjek / Judul <span style={{color:'#ef4444'}}>*</span></label>
              <input
                name="subject" type="text"
                value={form.subject} onChange={handleChange}
                placeholder="Ringkasan singkat masalah Anda"
                required style={inputStyle}
              />
            </div>

            {/* Description */}
            <div>
              <label style={labelStyle}>Deskripsi Masalah</label>
              <textarea
                name="description" value={form.description} onChange={handleChange}
                placeholder="Jelaskan masalah secara detail..."
                rows={5}
                style={{ ...inputStyle, resize:'vertical' }}
              />
            </div>

            {/* File Attachment */}
            <div>
              <label style={labelStyle}>Lampiran (Opsional)</label>
              <div style={{ border:'2px dashed #e2e8f0', borderRadius:10, padding:'20px', textAlign:'center', background:'#f8fafc', cursor:'pointer' }}
                onClick={() => document.getElementById('fileInput')?.click()}>
                {file ? (
                  <div>
                    <div style={{ fontSize:24 }}>📎</div>
                    <div style={{ fontSize:13, color:'#334155', marginTop:6 }}>{file.name}</div>
                    <div style={{ fontSize:12, color:'#94a3b8' }}>{(file.size/1024).toFixed(1)} KB</div>
                  </div>
                ) : (
                  <div>
                    <div style={{ fontSize:32 }}>📁</div>
                    <div style={{ fontSize:13, color:'#64748b', marginTop:8 }}>Klik untuk pilih file atau seret ke sini</div>
                    <div style={{ fontSize:12, color:'#94a3b8', marginTop:4 }}>PNG, JPG, PDF, DOCX (maks. 10MB)</div>
                  </div>
                )}
                <input id="fileInput" type="file" style={{ display:'none' }}
                  accept="image/*,.pdf,.doc,.docx,.xls,.xlsx"
                  onChange={e => setFile(e.target.files?.[0] || null)} />
              </div>
            </div>

            {error && (
              <div style={{ background:'#fee2e2', color:'#991b1b', padding:'12px 16px', borderRadius:9, fontSize:13 }}>{error}</div>
            )}

            <div style={{ display:'flex', gap:12, justifyContent:'flex-end', marginTop:8 }}>
              <button type="button" onClick={() => router.back()}
                style={{ padding:'11px 24px', borderRadius:10, border:'1.5px solid #e2e8f0', background:'#fff', fontSize:14, fontWeight:600, color:'#475569', cursor:'pointer' }}>
                Batal
              </button>
              <button type="submit" disabled={loading}
                style={{ padding:'11px 28px', borderRadius:10, border:'none', background: loading ? '#64748b':'#0f172a', color:'#fff', fontSize:14, fontWeight:700, cursor: loading ? 'not-allowed':'pointer' }}>
                {loading ? 'Mengirim...' : 'Kirim Tiket'}
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>
  )
}
