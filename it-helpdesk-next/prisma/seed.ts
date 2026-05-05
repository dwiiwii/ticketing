import { prisma } from '../lib/prisma'

async function main() {
  console.log('🌱 Seeding database...')

  // Create users
  const users = [
    { name: 'Admin', email: 'admin@fasremit.com', password: '123', role: 'admin' },
    { name: 'Operasional', email: 'operasional@fasremit.com', password: '345', role: 'user' },
    { name: 'Accounting', email: 'accounting@fasremit.com', password: '678', role: 'user' },
    { name: 'Finance', email: 'finance@fasremit.com', password: '901', role: 'user' },
  ]

  for (const u of users) {
    await prisma.user.upsert({
      where: { email: u.email },
      update: {},
      create: u,
    })
    console.log(`✅ User: ${u.name} (PIN: ${u.password})`)
  }

  console.log('🎉 Seeding selesai!')
}

main()
  .catch(console.error)
  .finally(() => prisma.$disconnect())
