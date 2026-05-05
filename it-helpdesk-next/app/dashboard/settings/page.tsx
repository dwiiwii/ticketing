import { prisma } from '@/lib/prisma'
import { auth } from '@/lib/auth'
import { redirect } from 'next/navigation'
import SettingsClient from './SettingsClient'

export default async function SettingsPage() {
  const session = await auth()
  if (!session || !['admin','master'].includes(session.user.role || ''))
    redirect('/')

  const users = await prisma.user.findMany({ orderBy: { id: 'asc' } })

  return <SettingsClient users={users} />
}
