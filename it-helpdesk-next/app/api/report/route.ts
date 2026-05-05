import { NextRequest, NextResponse } from 'next/server'
import { prisma } from '@/lib/prisma'
import { auth } from '@/lib/auth'

export async function GET(req: NextRequest) {
  const session = await auth()
  if (!session) return NextResponse.json({ error: 'Unauthorized' }, { status: 401 })

  const { searchParams } = new URL(req.url)
  const month = parseInt(searchParams.get('month') || String(new Date().getMonth() + 1))
  const year  = parseInt(searchParams.get('year')  || String(new Date().getFullYear()))

  const startDate = new Date(year, month - 1, 1)
  const endDate   = new Date(year, month, 1)

  // Daily completed tickets
  const dailyRaw = await prisma.ticket.findMany({
    where: {
      status: 'Selesai',
      updatedAt: { gte: startDate, lt: endDate },
    },
    select: { updatedAt: true },
  })

  const daysInMonth = new Date(year, month, 0).getDate()
  const dailyMap: Record<number, number> = {}
  dailyRaw.forEach(t => {
    const day = t.updatedAt.getDate()
    dailyMap[day] = (dailyMap[day] || 0) + 1
  })
  const dailyLabels = Array.from({ length: daysInMonth }, (_, i) => i + 1)
  const dailyData   = dailyLabels.map(d => dailyMap[d] || 0)

  // Monthly summary for the year
  const monthNames = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des']
  const monthlyData = await Promise.all(
    monthNames.map(async (_, i) => {
      const s = new Date(year, i, 1)
      const e = new Date(year, i + 1, 1)
      return prisma.ticket.count({
        where: { status: 'Selesai', updatedAt: { gte: s, lt: e } },
      })
    })
  )

  // Division breakdown
  const divisionRaw = await prisma.ticket.findMany({
    where: { status: 'Selesai', updatedAt: { gte: startDate, lt: endDate } },
    include: { user: true },
  })
  const divMap: Record<string, number> = {}
  divisionRaw.forEach(t => {
    const name = t.user?.name || 'Umum'
    divMap[name] = (divMap[name] || 0) + 1
  })
  const division = Object.entries(divMap)
    .map(([divisi, total]) => ({ divisi, total }))
    .sort((a, b) => b.total - a.total)

  return NextResponse.json({
    success: true,
    daily: { labels: dailyLabels, data: dailyData },
    monthly: { labels: monthNames, data: monthlyData },
    division,
    daysInMonth,
    selectedMonth: month,
    selectedYear: year,
  })
}
