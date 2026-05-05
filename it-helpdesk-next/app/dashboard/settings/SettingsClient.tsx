'use client'
import { useState } from 'react'

const ROLES = ['admin','master','agent','user']
const inputStyle: React.CSSProperties = { width:'100%', padding:'9px 12px', borderRadius:8, border:'1.5px solid #e2e8f0', fontSize:13, outline:'none', background:'#f8fafc' }

export default function SettingsClient({ users: initialUsers }: { users: any[] }) {
  const [users, setUsers] = useState(initialUsers)
  const [showAdd, setShowAdd] = useState(false)
  const [form, setForm] = useState({ name:'', email:'', password:'', role:'user' })
  const [editId, setEditId] = useState<number|null>(null)
  const [editPin, setEditPin] = useState('')

  async function addUser() {
    const res = await fetch('/api/users', { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify(form) })
    const user = await res.json()
    setUsers(prev => [...prev, user])
    setForm({ name:'', email:'', password:'', role:'user' })
    setShowAdd(false)
  }

  async function updatePin(id: number) {
    await fetch(`/api/users/${id}`, { method:'PATCH', headers:{'Content-Type':'application/json'}, body: JSON.stringify({ password: editPin }) })
    setUsers(prev => prev.map(u => u.id === id ? { ...u, password: editPin } : u))
    setEditId(null)
    setEditPin('')
  }

  async function deleteUser(id: number) {
    if (!confirm('Hapus user ini?')) return
    await fetch(`/api/users/${id}`, { method:'DELETE' })
    setUsers(prev => prev.filter(u => u.id !== id))
  }

  const roleColor: Record<string,string> = { admin:'#7c3aed', master:'#0ea5e9', agent:'#16a34a', user:'#64748b' }
  const roleBg:    Record<string,string> = { admin:'#faf5ff', master:'#f0f9ff', agent:'#f0fdf4', user:'#f8fafc' }

  return (
    <div style={{ padding:32 }}>
      <div style={{ display:'flex', justifyContent:'space-between', alignItems:'center', marginBottom:28 }}>
        <div>
          <h1 style={{ fontSize:22, fontWeight:700, color:'#0f172a', margin:0 }}>Pengaturan Pengguna</h1>
          <p style={{ color:'#64748b', marginTop:4, fontSize:14 }}>Kelola akun dan PIN pengguna</p>
        </div>
        <button onClick={() => setShowAdd(!showAdd)}
          style={{ padding:'10px 20px', borderRadius:10, border:'none', background:'#0f172a', color:'#fff', fontSize:14, fontWeight:700, cursor:'pointer' }}>
          + Tambah Pengguna
        </button>
      </div>

      {/* Add User Form */}
      {showAdd && (
        <div style={{ background:'#fff', borderRadius:14, boxShadow:'0 1px 8px rgba(0,0,0,0.06)', padding:24, marginBottom:24 }}>
          <h3 style={{ fontSize:15, fontWeight:700, color:'#0f172a', marginBottom:16 }}>Tambah Pengguna Baru</h3>
          <div style={{ display:'grid', gridTemplateColumns:'1fr 1fr', gap:14 }}>
            <div>
              <label style={{ fontSize:12, fontWeight:600, color:'#64748b', display:'block', marginBottom:6 }}>Nama</label>
              <input value={form.name} onChange={e=>setForm(p=>({...p,name:e.target.value}))} placeholder="Nama lengkap" style={inputStyle} />
            </div>
            <div>
              <label style={{ fontSize:12, fontWeight:600, color:'#64748b', display:'block', marginBottom:6 }}>Email</label>
              <input type="email" value={form.email} onChange={e=>setForm(p=>({...p,email:e.target.value}))} placeholder="email@fasremit.com" style={inputStyle} />
            </div>
            <div>
              <label style={{ fontSize:12, fontWeight:600, color:'#64748b', display:'block', marginBottom:6 }}>PIN Akses</label>
              <input type="password" value={form.password} onChange={e=>setForm(p=>({...p,password:e.target.value}))} placeholder="••••••" style={inputStyle} />
            </div>
            <div>
              <label style={{ fontSize:12, fontWeight:600, color:'#64748b', display:'block', marginBottom:6 }}>Role</label>
              <select value={form.role} onChange={e=>setForm(p=>({...p,role:e.target.value}))} style={inputStyle}>
                {ROLES.map(r=><option key={r} value={r}>{r}</option>)}
              </select>
            </div>
          </div>
          <div style={{ display:'flex', gap:10, justifyContent:'flex-end', marginTop:16 }}>
            <button onClick={()=>setShowAdd(false)} style={{ padding:'9px 20px', borderRadius:9, border:'1.5px solid #e2e8f0', background:'#fff', fontSize:13, cursor:'pointer' }}>Batal</button>
            <button onClick={addUser} style={{ padding:'9px 20px', borderRadius:9, border:'none', background:'#0f172a', color:'#fff', fontSize:13, fontWeight:700, cursor:'pointer' }}>Simpan</button>
          </div>
        </div>
      )}

      {/* Users Table */}
      <div style={{ background:'#fff', borderRadius:16, boxShadow:'0 1px 8px rgba(0,0,0,0.06)', padding:24 }}>
        <table style={{ width:'100%', borderCollapse:'collapse', fontSize:13 }}>
          <thead>
            <tr style={{ borderBottom:'1px solid #f1f5f9' }}>
              {['No.','Nama','Email','Role','PIN','Aksi'].map(h=>(
                <th key={h} style={{ padding:'10px 12px', textAlign:'left', color:'#64748b', fontWeight:600 }}>{h}</th>
              ))}
            </tr>
          </thead>
          <tbody>
            {users.map((u:any, i:number)=>(
              <tr key={u.id} style={{ borderBottom:'1px solid #f8fafc' }}>
                <td style={{ padding:'12px 12px', color:'#94a3b8' }}>{i+1}</td>
                <td style={{ padding:'12px 12px', fontWeight:600, color:'#0f172a' }}>{u.name}</td>
                <td style={{ padding:'12px 12px', color:'#64748b' }}>{u.email}</td>
                <td style={{ padding:'12px 12px' }}>
                  <span style={{ padding:'3px 10px', borderRadius:6, fontSize:11, fontWeight:700, color:roleColor[u.role]||'#64748b', background:roleBg[u.role]||'#f8fafc', textTransform:'uppercase' }}>{u.role}</span>
                </td>
                <td style={{ padding:'12px 12px' }}>
                  {editId === u.id ? (
                    <div style={{ display:'flex', gap:6 }}>
                      <input type="text" value={editPin} onChange={e=>setEditPin(e.target.value)} placeholder="PIN baru" style={{ padding:'5px 10px', borderRadius:7, border:'1px solid #e2e8f0', fontSize:12, width:100 }} />
                      <button onClick={()=>updatePin(u.id)} style={{ padding:'5px 10px', borderRadius:7, border:'none', background:'#0f172a', color:'#fff', fontSize:11, fontWeight:700, cursor:'pointer' }}>Simpan</button>
                      <button onClick={()=>{setEditId(null);setEditPin('')}} style={{ padding:'5px 8px', borderRadius:7, border:'1px solid #e2e8f0', fontSize:11, cursor:'pointer' }}>✕</button>
                    </div>
                  ) : (
                    <button onClick={()=>{setEditId(u.id);setEditPin(u.password)}}
                      style={{ padding:'5px 12px', borderRadius:7, border:'1px solid #e2e8f0', background:'#f8fafc', fontSize:12, cursor:'pointer', fontWeight:500 }}>
                      Ubah PIN
                    </button>
                  )}
                </td>
                <td style={{ padding:'12px 12px' }}>
                  <button onClick={()=>deleteUser(u.id)} style={{ background:'#fee2e2', color:'#ef4444', border:'none', borderRadius:6, padding:'5px 10px', cursor:'pointer', fontSize:12, fontWeight:600 }}>Hapus</button>
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>
    </div>
  )
}
