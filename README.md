# 🎫 Smart Ticket Triage & Dashboard

A **Laravel 11 + Vue 3 SPA** for managing support tickets with **AI classification**.  
Users can submit, classify, filter, and analyze tickets with a clean dashboard interface.

---

## ⚡ Setup (≤ 10 Steps)

1. **Clone the repo**
   ```bash
   git clone https://github.com/ohiolei/smart-ticket-test.git
   cd smart-ticket-triage


2. **install all php dependencies**
   ```bash
   composer install


3. **install all javacript dependencies**
   ```bash
   npm install

4. **create enviromental varriables and generate app key**
   ```bash
   cp .env.example .env
   php artisan key:generate

5. **migrate and seed the database**
   ```bash
   php artisan migrate --seed

6. **serve php and javascript**
   ```bash
   php artisan serve
   npm run dev


