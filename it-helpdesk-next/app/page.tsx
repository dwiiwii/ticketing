import { redirect } from 'next/navigation'
import { auth } from '@/lib/auth'

export default async function HomePage() {
  const session = await auth()
  if (!session) redirect('/login')

  const role = session.user.role
  const name = session.user.name?.toLowerCase() || ''

  if (role === 'user') {
    if (name === 'finance') redirect('/dashboard/finance')
    if (name === 'accounting') redirect('/dashboard/accounting')
    redirect('/dashboard/operasional')
  }
  redirect('/dashboard/admin')
}
