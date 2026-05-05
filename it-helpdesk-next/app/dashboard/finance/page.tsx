import { prisma } from '@/lib/prisma'
import { auth } from '@/lib/auth'
import { redirect } from 'next/navigation'
import DivisionDashboard from '@/components/DivisionDashboard'

export default async function FinancePage() {
  const session = await auth()
  if (!session) redirect('/login')

  const tickets = await prisma.ticket.findMany({
    where: { user: { name: { equals: 'Finance', mode: 'insensitive' } } },
    include: { user: true },
    orderBy: { createdAt: 'desc' },
  })

  return <DivisionDashboard title="Finance" tickets={tickets} />
}
