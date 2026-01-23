# Laravel Authentication API

 * This is an **API-only** Laravel backend. It does not include:
- Blade templates (views)
- Frontend assets (CSS/JS)
- Server-side rendering

The frontend is handled by a separate React application.

## Installation

### 1. Clone the repository
```bash
git clone https://github.com/khaya05/fullstack-crypto-tax-calculator.git
cd fullstack-crypto-tax-calculator/backend
```

### 2. Install dependencies
```bash
composer install
```

### 3. Set up environment variables
```bash
cp .env.example .env
```

Edit `.env` and configure your database:
```env
DB_CONNECTION=sqlite
```

### 4. Generate application key
```bash
php artisan key:generate
```

### 5. Create database file (for SQLite)
```bash
touch database/database.sqlite
```

### 6. Run migrations
```bash
php artisan migrate
```

### 7. Start the development server
```bash
php artisan serve
```

The API will be available at `http://localhost:8000`

## Testing

You can test the API using:
- **cURL** (examples above)
- **Postman** - Import the endpoints and test
- **Your React frontend** - See the frontend repository
