'use client'
import Link from 'next/link'
import { usePathname } from 'next/navigation'
import { signOut, useSession } from 'next-auth/react'

const adminLinks = [
  { href: '/dashboard/admin', icon: '📊', label: 'Ringkasan' },
  { href: '/dashboard/tickets', icon: '🎫', label: 'Semua Tiket' },
  { href: '/dashboard/assigned', icon: '👤', label: 'Tugas Saya' },
  { href: '/dashboard/settings', icon: '⚙️', label: 'Pengaturan' },
  { href: '/dashboard/asset-request', icon: '📦', label: 'Pengambilan Aset' },
]

const userLinks = (role: string, name: string) => {
  let ticketHref = '/dashboard/operasional'
  if (name === 'finance') ticketHref = '/dashboard/finance'
  else if (name === 'accounting') ticketHref = '/dashboard/accounting'

  return [
    { href: ticketHref, icon: '🎫', label: 'Ticketing' },
    { href: '/dashboard/track-ticket', icon: '🔍', label: 'Lacak Tiket' },
    { href: '/dashboard/asset-request', icon: '📦', label: 'Pengambilan Aset' },
  ]
}

export default function Sidebar() {
  const pathname = usePathname()
  const { data: session } = useSession()
  const role = session?.user?.role || 'user'
  const name = session?.user?.name?.toLowerCase() || ''

  const isAdmin = ['admin', 'master', 'agent'].includes(role)
  const links = isAdmin ? adminLinks : userLinks(role, name)

  return (
    <aside style={{
      width: 230, background: '#fff', borderRight: '1px solid #f1f5f9',
      display: 'flex', flexDirection: 'column', padding: '20px 0',
      minHeight: '100vh', position: 'sticky', top: 0,
    }}>
      {/* Logo */}
      <div style={{ display: 'flex', justifyContent: 'center', padding: '0 20px 24px' }}>
        <img src="/assets/fasremit.png" alt="Logo" style={{ width: 150, height: 'auto' }} />
      </div>

      {/* Nav Links */}
      <nav style={{ flex: 1, display: 'flex', flexDirection: 'column', gap: 4, padding: '0 12px' }}>
        {links.map(link => {
          const isActive = pathname === link.href
          return (
            <Link key={link.href} href={link.href} style={{
              display: 'flex', alignItems: 'center', gap: 10,
              padding: '10px 14px', borderRadius: 10, textDecoration: 'none',
              fontSize: 14, fontWeight: isActive ? 600 : 500,
              color: isActive ? '#0ea5e9' : '#475569',
              background: isActive ? '#f0f9ff' : 'transparent',
              transition: 'all 0.15s',
            }}>
              <span style={{ fontSize: 16 }}>{link.icon}</span>
              <span>{link.label}</span>
            </Link>
          )
        })}
      </nav>

      {/* Logout */}
      <div style={{ padding: '0 12px', marginTop: 12 }}>
        <button
          onClick={() => signOut({ callbackUrl: '/login' })}
          style={{
            display: 'flex', alignItems: 'center', gap: 10,
            padding: '10px 14px', borderRadius: 10, border: 'none',
            fontSize: 14, fontWeight: 500, color: '#ef4444',
            background: '#fff5f5', cursor: 'pointer', width: '100%',
            transition: 'all 0.15s',
          }}
        >
          <span>🚪</span><span>Logout</span>
        </button>
      </div>
    </aside>
  )
}
