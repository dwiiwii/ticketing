import { prisma } from '@/lib/prisma'
import { auth } from '@/lib/auth'
import { redirect } from 'next/navigation'
import AssignedClient from './AssignedClient'

export default async function AssignedPage() {
  const session = await auth()
  if (!session) redirect('/login')

  // Show tickets that are in 'Proses' status (assigned to agent)
  const tickets = await prisma.ticket.findMany({
    where: { status: 'Proses' },
    include: { user: true },
    orderBy: { updatedAt: 'desc' },
  })

  return <AssignedClient tickets={tickets} />
}
