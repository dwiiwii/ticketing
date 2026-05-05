# 🚀 Panduan Deploy IT Helpdesk (Next.js) ke Vercel

## File yang sudah dibuat:
- ✅ Next.js 14 App Router + TypeScript
- ✅ Autentikasi PIN (NextAuth.js)
- ✅ Dashboard Admin, Operasional, Finance, Accounting
- ✅ Semua Tiket dengan filter
- ✅ Buat Tiket + Upload Lampiran (Cloudinary)
- ✅ Lacak Status Tiket
- ✅ Pengaturan User
- ✅ Laporan + Export Excel
- ✅ Grafik bar chart bulanan
- ✅ Pengambilan Aset (Coming Soon)
- ✅ Sidebar role-based

---

## STEP 1: Install Node.js v20+
Download dari https://nodejs.org (pilih versi LTS)

---

## STEP 2: Setup Database - Neon (Gratis)
1. Buka https://neon.tech dan daftar (bisa pakai GitHub)
2. Klik "New Project" → beri nama "it-helpdesk"
3. Setelah dibuat, klik "Connect" → copy string yang mulai dari `postgresql://...`

---

## STEP 3: Setup Cloudinary (Gratis)
1. Buka https://cloudinary.com dan daftar
2. Di Dashboard, catat:
   - Cloud Name
   - API Key
   - API Secret

---

## STEP 4: Buat file .env.local
Di folder `it-helpdesk-next`, buat file `.env.local` (copy dari `.env.local.example`):
```
DATABASE_URL="postgresql://USER:PASSWORD@HOST/DBNAME?sslmode=require"
AUTH_SECRET="random-string-panjang-apa-saja-minimal-32-karakter"
NEXTAUTH_URL="http://localhost:3000"
CLOUDINARY_CLOUD_NAME="nama_cloud_kamu"
CLOUDINARY_API_KEY="api_key_kamu"
CLOUDINARY_API_SECRET="api_secret_kamu"
```

---

## STEP 5: Install & Jalankan
Buka terminal di folder `it-helpdesk-next`:
```bash
npm install
npx prisma db push
npx prisma db seed
npm run dev
```
Buka http://localhost:3000

---

## STEP 6: Deploy ke Vercel
1. Push folder `it-helpdesk-next` ke GitHub
2. Buka https://vercel.com → Login dengan GitHub
3. Klik "Add New Project" → pilih repo
4. Di bagian "Environment Variables", tambahkan semua isi .env.local (TANPA NEXTAUTH_URL)
5. Tambahkan: `NEXTAUTH_URL` = URL Vercel kamu (contoh: https://it-helpdesk.vercel.app)
6. Klik Deploy!

---

## PIN Login Default:
| Pengguna | PIN |
|---|---|
| Admin | 123 |
| Operasional | 345 |
| Accounting | 678 |
| Finance | 901 |

---
