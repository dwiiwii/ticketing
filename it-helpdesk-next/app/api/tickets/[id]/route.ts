import { NextRequest, NextResponse } from 'next/server'
import { prisma } from '@/lib/prisma'
import { auth } from '@/lib/auth'

// PATCH - update ticket (status, sla, etc.)
export async function PATCH(req: NextRequest, { params }: { params: { id: string } }) {
  const session = await auth()
  if (!session) return NextResponse.json({ error: 'Unauthorized' }, { status: 401 })

  const id = parseInt(params.id)
  const body = await req.json()

  const ticket = await prisma.ticket.update({
    where: { id },
    data: body,
  })

  return NextResponse.json(ticket)
}

// DELETE - delete ticket
export async function DELETE(req: NextRequest, { params }: { params: { id: string } }) {
  const session = await auth()
  if (!session) return NextResponse.json({ error: 'Unauthorized' }, { status: 401 })

  const id = parseInt(params.id)
  await prisma.ticket.delete({ where: { id } })

  return NextResponse.json({ success: true })
}
