import { prisma } from '@/lib/prisma'
import { auth } from '@/lib/auth'
import { redirect } from 'next/navigation'
import AllTicketsClient from './AllTicketsClient'

export default async function AllTicketsPage() {
  const session = await auth()
  if (!session || !['admin','master','agent'].includes(session.user.role || ''))
    redirect('/')

  const tickets = await prisma.ticket.findMany({
    include: { user: true },
    orderBy: { createdAt: 'desc' },
  })

  return <AllTicketsClient tickets={tickets} />
}
