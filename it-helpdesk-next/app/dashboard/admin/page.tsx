import { prisma } from '@/lib/prisma'
import { auth } from '@/lib/auth'
import { redirect } from 'next/navigation'
import AdminDashboardClient from './AdminDashboardClient'

export default async function AdminDashboardPage() {
  const session = await auth()
  if (!session || !['admin','master','agent'].includes(session.user.role || ''))
    redirect('/')

  const tickets = await prisma.ticket.findMany({
    include: { user: true },
    orderBy: { createdAt: 'desc' },
  })

  const totalTickets = tickets.length
  const bukaCount = tickets.filter(t => t.status === 'Buka').length
  const prosesCount = tickets.filter(t => t.status === 'Proses').length
  const selesaiCount = tickets.filter(t => t.status === 'Selesai').length

  return (
    <AdminDashboardClient
      tickets={tickets}
      stats={{ totalTickets, bukaCount, prosesCount, selesaiCount }}
    />
  )
}
