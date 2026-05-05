import { NextRequest, NextResponse } from 'next/server'
import { prisma } from '@/lib/prisma'
import { auth } from '@/lib/auth'
import * as XLSX from 'xlsx'

export async function GET(req: NextRequest) {
  const session = await auth()
  if (!session) return NextResponse.json({ error: 'Unauthorized' }, { status: 401 })

  const { searchParams } = new URL(req.url)
  const month = parseInt(searchParams.get('month') || String(new Date().getMonth() + 1))
  const year  = parseInt(searchParams.get('year')  || String(new Date().getFullYear()))

  const monthNames = ['Januari','Februari','Maret','April','Mei','Juni',
                      'Juli','Agustus','September','Oktober','November','Desember']
  const monthLabel = `${monthNames[month - 1]} ${year}`

  const startDate = new Date(year, month - 1, 1)
  const endDate   = new Date(year, month, 1)

  const tickets = await prisma.ticket.findMany({
    where: { status: 'Selesai', updatedAt: { gte: startDate, lt: endDate } },
    include: { user: true },
    orderBy: { updatedAt: 'asc' },
  })

  const rows = tickets.map((t, i) => ({
    No: i + 1,
    'No. Ticket': t.ticketNumber,
    'Nama Pengaju': t.requesterName,
    Divisi: t.user?.name || '-',
    Kategori: t.category,
    Subjek: t.subject,
    Prioritas: t.priority,
    SLA: t.sla || '-',
    'Tanggal Dibuat': t.createdAt.toLocaleDateString('id-ID'),
    'Tanggal Selesai': t.updatedAt.toLocaleDateString('id-ID'),
  }))

  const wb = XLSX.utils.book_new()
  const ws = XLSX.utils.json_to_sheet(rows)
  XLSX.utils.book_append_sheet(wb, ws, 'Laporan')

  const buf = XLSX.write(wb, { type: 'buffer', bookType: 'xlsx' })

  return new NextResponse(buf, {
    headers: {
      'Content-Type': 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
      'Content-Disposition': `attachment; filename="Laporan_${monthLabel}.xlsx"`,
    },
  })
}
