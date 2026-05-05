export default function AssetRequestPage() {
  return (
    <div style={{ padding:32 }}>
      <div style={{ marginBottom:28 }}>
        <h1 style={{ fontSize:22, fontWeight:700, color:'#0f172a', margin:0 }}>Pengambilan Aset</h1>
        <p style={{ color:'#64748b', marginTop:4, fontSize:14 }}>Kelola permintaan aset IT perusahaan</p>
      </div>

      <div style={{
        background:'#fff', borderRadius:16, boxShadow:'0 1px 8px rgba(0,0,0,0.06)',
        padding:60, textAlign:'center'
      }}>
        <div style={{ fontSize:64, marginBottom:16 }}>📦</div>
        <h2 style={{ fontSize:20, fontWeight:700, color:'#0f172a', marginBottom:8 }}>Coming Soon</h2>
        <p style={{ color:'#64748b', fontSize:14 }}>
          Fitur Pengambilan Aset sedang dalam pengembangan.<br/>
          Akan segera tersedia dalam waktu dekat.
        </p>
      </div>
    </div>
  )
}
