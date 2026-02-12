# Crypto Tax Calculator - Backend Documentation

## Installation

### 1. Requirements
- PHP 8.2+
- Composer
- SQLite

### 2. Install Dependencies
```bash
cd backend
composer install
```

### 3. Setup Environment
```bash
cp .env.example .env
php artisan key:generate
```

### 4. Configure Database
Edit `.env`:
```
DB_CONNECTION=sqlite
```

Create database:
```bash
touch database/database.sqlite
php artisan migrate
```

### 5. Start Server
```bash
php artisan serve
```

Server runs at: `http://localhost:8000`

---

## API Endpoints

Base URL: `http://localhost:8000/api`

### Import Transactions

#### Upload File (CSV/Excel)
```
POST /api/transactions/upload
Content-Type: multipart/form-data
```

**Request:**
- `file`: CSV or Excel file (.csv, .txt, .xlsx, .xls)

**Example:**
```bash
curl -X POST http://localhost:8000/api/transactions/upload \
  -F "file=@transactions.csv"
```

**CSV Format:**
```csv
date,type,coin,quantity,price_zar,fee
2023-03-15,BUY,BTC,0.5,400000,50
2023-06-15,SELL,BTC,0.3,450000,25
```

**Response:**
```json
{
  "status": "success",
  "message": "Imported 8 transactions successfully",
  "data": {
    "session_id": "calc-abc123",
    "count": 8,
    "errors": []
  }
}
```

---

#### Paste Transactions
```
POST /api/transactions/paste
Content-Type: application/json
```

**Request:**
```json
{
  "text": "date,type,coin,quantity,price_zar,fee\n2023-03-15,BUY,BTC,0.5,400000,50"
}
```

**Response:**
```json
{
  "status": "success",
  "message": "Imported 3 transactions successfully",
  "data": {
    "session_id": "calc-def456",
    "count": 3,
    "errors": []
  }
}
```

---

### Calculate Tax

#### Get Tax Calculation (By Tax Year)
```
POST /api/calculate
Content-Type: application/json
```

**Request:**
```json
{
  "session_id": "calc-abc123"
}
```

**Response:**
```json
{
  "status": "success",
  "data": {
    "session_id": "calc-abc123",
    "overall_summary": {
      "total_years": 2,
      "total_transactions": 15,
      "total_capital_gain_all_years": 99700,
      "total_taxable_all_years": 3940
    },
    "tax_years": [
      {
        "tax_year": 2024,
        "period": "2024-03-01 to 2025-02-28",
        "status": "current",
        "total_gain": 49850,
        "fifo_calculation": {
          "total_capital_gain": 49850,
          "annual_exclusion_applied": 40000,
          "net_capital_gain": 9850,
          "taxable_capital_gain": 3940
        },
        "coins": [...]
      }
    ]
  }
}
```

---

#### Download PDF Report
```
POST /api/calculate/download-pdf
Content-Type: application/json
```

**Request:**
```json
{
  "session_id": "calc-abc123"
}
```

**Response:** PDF file download

**Example:**
```bash
curl -X POST http://localhost:8000/api/calculate/download-pdf \
  -H "Content-Type: application/json" \
  -d '{"session_id":"calc-abc123"}' \
  --output report.pdf
```

---

### View Transactions

#### Get All Transactions
```
GET /api/transactions?session_id=calc-abc123
```

**Response:**
```json
{
  "status": "success",
  "data": [...],
  "count": 8
}
```

---

#### Get Transactions by Tax Year
```
GET /api/transactions/by-tax-year?session_id=calc-abc123
```

**Response:**
```json
{
  "status": "success",
  "data": [
    {
      "tax_year": 2024,
      "period": "2024-03-01 to 2025-02-28",
      "transactions": [...],
      "count": 5
    }
  ],
  "total_tax_years": 2
}
```

---

#### Get Session Transactions
```
GET /api/transactions/session/{sessionId}
```

**Example:**
```
GET /api/transactions/session/calc-abc123
```

---

### Delete Transactions

#### Delete Session
```
DELETE /api/transactions/session/{sessionId}
```

**Example:**
```bash
curl -X DELETE http://localhost:8000/api/transactions/session/calc-abc123
```

**Response:**
```json
{
  "status": "success",
  "message": "Deleted 8 transactions"
}
```

---

## Tax Calculation Details

### South African Tax Rules

- **Tax Year:** March 1 - February 28/29
- **Annual Exclusion:** R40,000 per tax year
- **Inclusion Rate:** 40% (only 40% of net gain is taxable)
- **Method:** FIFO (First-In-First-Out)

### Calculation Steps

1. Group transactions by tax year
2. Calculate capital gains using FIFO method
3. Apply R40,000 annual exclusion per year
4. Apply 40% inclusion rate
5. Result = taxable amount to add to income


## Column Name Variations

The importer recognizes these column names:

| Field | Accepted Names |
|-------|---------------|
| date | date, transaction_date, Date, DATE |
| type | type, transaction_type, Type, TYPE |
| coin | coin, asset, cryptocurrency, Asset, symbol |
| quantity | quantity, amount, qty, Volume |
| price_zar | price_zar, price, Price, rate |
| fee | fee, fees, commission, Fee |

---
