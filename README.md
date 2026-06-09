# MAAZ - MLM + E-Commerce Platform

A comprehensive MLM (Multi-Level Marketing) and E-commerce platform built with Laravel and Next.js.

## 🎯 Features

- **User Authentication**: Secure registration and login
- **E-Commerce**: Product catalog, shopping cart, checkout
- **MLM System**: Referral, team hierarchy, commission engine
- **Wallet System**: Balance management and withdrawals
- **Admin Dashboard**: Complete management interface
- **Responsive UI**: Mobile-first design with Tailwind CSS

## 🏗️ Architecture

### Backend
- **Framework**: Laravel 11
- **Database**: MySQL with Redis cache
- **API**: RESTful with JWT authentication
- **Queue**: Redis-backed job queue

### Frontend
- **Framework**: Next.js 14
- **Styling**: Tailwind CSS + shadcn/ui
- **State Management**: React Query
- **Authentication**: JWT-based

## 📦 Modules

1. User Authentication
2. Product Catalog
3. Shopping Cart
4. Checkout & Payments
5. Order Management
6. Referral System
7. MLM Team Tree
8. Commission Engine
9. Wallet System
10. Withdrawal Requests
11. Rank Management
12. Admin Dashboard

## 🚀 Getting Started

### Prerequisites
- Docker & Docker Compose
- Node.js 18+
- PHP 8.2+

### Installation

```bash
# Clone repository
git clone https://github.com/maazinfiniwebtechnologies-lgtm/maaz.git
cd maaz

# Setup backend
cd backend
composer install
cp .env.example .env
php artisan key:generate

# Setup frontend
cd ../frontend
npm install

# Run with Docker
docker-compose up
```

## 📚 API Documentation

See `API_DOCS.md` for complete API reference.

## 📝 License

MIT
