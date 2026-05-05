import { NextRequest, NextResponse } from 'next/server'
import { prisma } from '@/lib/prisma'
import { auth } from '@/lib/auth'

// GET all tickets
export async function GET(req: NextRequest) {
  const session = await auth()
  if (!session) return NextResponse.json({ error: 'Unauthorized' }, { status: 401 })

  const { searchParams } = new URL(req.url)
  const division = searchParams.get('division')
  const status = searchParams.get('status')
  const month = searchParams.get('month')
  const year = searchParams.get('year')

  const where: any = {}
  if (status) where.status = status
  if (division) where.user = { name: division }

  const tickets = await prisma.ticket.findMany({
    where,
    include: { user: true },
    orderBy: { createdAt: 'desc' },
  })

  return NextResponse.json(tickets)
}

// POST create ticket
export async function POST(req: NextRequest) {
  const session = await auth()
  if (!session) return NextResponse.json({ error: 'Unauthorized' }, { status: 401 })

  const body = await req.json()
  const { subject, category, requesterName, description, attachment } = body

  // Generate sequential ticket number FR-X
  const lastTicket = await prisma.ticket.findFirst({
    orderBy: { id: 'desc' },
  })
  const nextId = lastTicket ? lastTicket.id + 1 : 1
  const ticketNumber = `FR-${nextId}`

  const userId = parseInt(session.user.id)

  const ticket = await prisma.ticket.create({
    data: {
      ticketNumber,
      requesterName: requesterName || session.user.name || '',
      userId,
      subject,
      category,
      description,
      priority: 'Sedang',
      status: 'Buka',
      sla: null,
      attachment: attachment || null,
    },
  })

  return NextResponse.json(ticket, { status: 201 })
}
