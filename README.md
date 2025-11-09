<p align="center">
  <a href="https://laravel.com" target="_blank">
    <img src="https://laravel.com/img/logomark.min.svg" width="90" alt="Laravel Logo">
  </a>
</p>

<h1 align="center">Laravel E-Commerce (Raw Version)</h1>
<p align="center">Simple Laravel 12 E-Commerce Project with RajaOngkir & Midtrans Integration</p>

---

## 🧱 Overview

This repository contains a **raw and educational version** of a Laravel 12 e-commerce project.  
It focuses on backend logic and integration — no frontend frameworks, no heavy styling.  
Inside, you’ll find **ready-to-connect code for RajaOngkir (shipping)** and **Midtrans (payment)** systems.

This project is ideal for those who want to **learn how to connect APIs manually** and understand how an e-commerce system works from the ground up.

---

## 📦 Features

- ✅ Laravel 12 base setup  
- 📦 RajaOngkir Starter API integration (for Indonesian shipping rates)  
- 💳 Midtrans Sandbox integration (for simulated payments)  
- 🧾 Simple product structure and checkout flow  
- 🧠 Educational code structure (easy to modify & extend)

---

## ⚙️ Requirements

- PHP 8.2+  
- Composer  
- MySQL / MariaDB  
- RajaOngkir Starter API key  
- Midtrans Sandbox account  

---

## 🚀 Installation

```bash
# 1. Clone the repository
git clone https://github.com/Asyraf2003/laravel-e-commerce-mentah.git

# 2. Enter the project directory
cd laravel-e-commerce-mentah

# 3. Install dependencies
composer install

# 4. Copy environment example file
cp .env.example .env

# 5. Generate application key
php artisan key:generate

# 6. Run migrations (create tables)
php artisan migrate

# 7. Serve the app
php artisan serve
```

Default local URL: **http://127.0.0.1:8000**

---

## 🧩 API Configuration (.env)

Open your `.env` file and edit these values to enable RajaOngkir & Midtrans.

### 🔹 RajaOngkir (Shipping)

> Register at [https://rajaongkir.com](https://rajaongkir.com) → Dashboard → Get your **Starter API Key**

```dotenv
# RAJAONGKIR CONFIGURATION
RAJAONGKIR_API_KEY=your_rajaongkir_api_key
RAJAONGKIR_BASE_URL=https://api.rajaongkir.com/starter
```

💡 *Usage Note:*  
Starter plan supports only **city-to-city cost checking** — no subdistrict API or international rates.

---

### 🔹 Midtrans (Payment)

> Create a [Midtrans Account](https://dashboard.midtrans.com/) → Switch to **Sandbox Mode** →  
> Copy the `SERVER_KEY` and `CLIENT_KEY` from your dashboard.

```dotenv
# MIDTRANS CONFIGURATION
MIDTRANS_SERVER_KEY=your_midtrans_server_key
MIDTRANS_CLIENT_KEY=your_midtrans_client_key
MIDTRANS_IS_PRODUCTION=false
MIDTRANS_BASE_URL=https://api.sandbox.midtrans.com/v2
```

💡 *Usage Note:*  
Keep `MIDTRANS_IS_PRODUCTION=false` while testing payments.  
Set it to `true` only when switching to real transactions.

---

## 🧠 Example Flow

1. **User selects a product**  
2. **Checkout form** collects shipping details  
3. **RajaOngkir API** calculates delivery cost  
4. **Midtrans** generates a payment link (via Snap / Core API)  
5. Transaction stored and updated upon callback  

This flow helps you understand the basic interaction between shipping and payment gateways.

---

## 🧾 Folder Highlights

```
app/
 ├── Http/
 │   ├── Controllers/
 │   │   ├── RajaOngkirController.php
 │   │   ├── MidtransController.php
 │   │   └── ProductController.php
 │   └── Middleware/
 │
 ├── Models/
 │   └── Product.php
 │
resources/
 └── views/
     ├── checkout.blade.php
     ├── payment.blade.php
     └── success.blade.php

routes/
 └── web.php
```

---

## 🧰 Example .env Summary

```dotenv
APP_NAME="Laravel E-Commerce Mentah"
APP_ENV=local
APP_URL=http://127.0.0.1:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel_ecommerce
DB_USERNAME=root
DB_PASSWORD=

RAJAONGKIR_API_KEY=your_rajaongkir_api_key
RAJAONGKIR_BASE_URL=https://api.rajaongkir.com/starter

MIDTRANS_SERVER_KEY=your_midtrans_server_key
MIDTRANS_CLIENT_KEY=your_midtrans_client_key
MIDTRANS_IS_PRODUCTION=false
MIDTRANS_BASE_URL=https://api.sandbox.midtrans.com/v2
```

---

## 🧯 Troubleshooting

**Problem:** RajaOngkir API returns 403 or "invalid key"  
→ Check if your API key is correct and plan is set to “Starter”.

**Problem:** Midtrans payment page not showing  
→ Ensure your `MIDTRANS_SERVER_KEY` is correct and `MIDTRANS_IS_PRODUCTION=false`.

**Problem:** Payment status not updated  
→ Make sure your callback URL is correctly configured in Midtrans dashboard.

---

## 📘 Learning Focus

This repository is meant for **learning and experimentation**, not production use.  
It’s a foundation for anyone who wants to explore:

- Laravel API integration patterns  
- Payment and shipping API logic  
- Realistic backend workflow of e-commerce systems  

---

## 🧾 License

Open-sourced under the [MIT License](https://opensource.org/licenses/MIT).

---

## 💡 Best Practice Notes

- Don’t commit your `.env` file — it contains private API keys.  
- When going live:
  - Switch `MIDTRANS_IS_PRODUCTION=true`
  - Change `RAJAONGKIR_BASE_URL` to **/pro**
- Always test shipping and payment flows in sandbox first.
- This repo is intentionally **bare** to make it easy to modify for your own use.

---
