import { NextRequest, NextResponse } from 'next/server'
import { prisma } from '@/lib/prisma'
import { auth } from '@/lib/auth'

// GET all users
export async function GET(req: NextRequest) {
  const session = await auth()
  if (!session || !['admin','master'].includes(session.user.role || ''))
    return NextResponse.json({ error: 'Unauthorized' }, { status: 401 })

  const users = await prisma.user.findMany({ orderBy: { id: 'asc' } })
  return NextResponse.json(users)
}

// POST create user
export async function POST(req: NextRequest) {
  const session = await auth()
  if (!session || !['admin','master'].includes(session.user.role || ''))
    return NextResponse.json({ error: 'Unauthorized' }, { status: 401 })

  const body = await req.json()
  const user = await prisma.user.create({ data: body })
  return NextResponse.json(user, { status: 201 })
}
