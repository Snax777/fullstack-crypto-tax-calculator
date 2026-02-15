# SARS Crypto Tax Calculator API Documentation (Multi-Wallet functionality)

This is a complete guide to the Laravel backend for your demo cryptocurrency tax calculator. It follows South African Revenue Service (SARS) rules: FIFO (First In, First Out) method for capital gains, R40,000 annual exclusion, 40% inclusion rate, and estimated additional tax based on 2025/2026 brackets.

The API is built for a React frontend. All endpoints are under `/api` and return JSON (except PDF export).

## Requirements

- Ensure you run the following commands:

```bash
cd backend;
composer install; // installs the necessary packages
php artisan migrate --path=./database/migrations/2026_02_09_125736_create_wallets_transactions_table.php
--path=./database/migrations/2026_02_09_122800_create_wallets_table.php --seed; // Migrates the two database tables and fills them with fake wallet and transactions data for demonstrating the tax report functionality which is included as well
```

---

## Quick Start

1. **Run the server**
   ```bash
   php artisan serve
   ```
   Default URL: http://127.0.0.1:8000

2. **Clear caches if needed**
   ```bash
   php artisan view:clear
   php artisan cache:clear
   php artisan config:clear
   ```

## Key Features

- **Wallets**: Every wallet needs a unique address (required).
- **Demo mode**: If the address contains "demo" (anywhere, case-insensitive), the wallet automatically gets 30–80 random realistic transactions.
- **Multi-wallet support**: Tax reports combine any number of wallets into one aggregated calculation.
- **Tax calculation**: FIFO across all selected wallets, SARS-compliant, with estimated extra tax based on your other income.
- **Outputs**: JSON report or branded PDF (with TaxTim-style logo and highlighted key figures).

## API Endpoints

| Method | Endpoint                          | Description                                      | Required Payload                                      | Example curl Command                                                                                   | Response |
|--------|-----------------------------------|--------------------------------------------------|-------------------------------------------------------|--------------------------------------------------------------------------------------------------------|----------|
| GET    | `/api/wallets`                    | List all wallets                                 | None                                                  | `curl http://127.0.0.1:8000/api/wallets`                                                                | JSON array of wallets with ID, name, address, transaction count |
| POST   | `/api/wallets`                    | Create a new wallet                              | `name` (string), `address` (string, unique)           | `curl -X POST http://127.0.0.1:8000/api/wallets -H "Content-Type: application/json" -d '{"name":"My Demo Wallet","address":"demo-1A1zP1eP5QGefi2DMPTfTL5SLmv7DivfNa"}'` | Created wallet JSON (201) |
| GET    | `/api/wallets/{id}/transactions`  | Get all transactions for a wallet                | None (use wallet ID from list)                        | `curl http://127.0.0.1:8000/api/wallets/1/transactions`                                                 | JSON array of transactions (ordered newest first) |
| POST   | `/api/tax/calculate`              | Calculate tax report (JSON)                      | `wallet_ids` (array), `tax_year` (integer), optional `other_income` (number) | `curl -X POST http://127.0.0.1:8000/api/tax/calculate -H "Content-Type: application/json" -d '{"wallet_ids":[1,2],"tax_year":2025,"other_income":400000}'` | Full JSON tax report |
| POST   | `/api/tax/export-pdf`             | Download PDF tax report                          | Same as calculate                                     | `curl -X POST http://127.0.0.1:8000/api/tax/export-pdf -H "Content-Type: application/json" -d '{"wallet_ids":[1],"tax_year":2025}' --output report.pdf` | PDF file download |
| POST   | `/api/tax/preview`                | View HTML version of report (for debugging)      | Same as calculate                                     | Open in browser or curl to see raw HTML                                                                | HTML page |

## Creating Wallets

- Address is **required** and must be unique.
- If the address contains "demo", random demo transactions are added automatically.

**Demo wallet example** (gets random data):
```json
{
  "name": "Demo Portfolio",
  "address": "demo-bc1qxy2kgdygjrsqtzq2n0yrf2493p83kkfjhx0wlh"
}
```

**Real/empty wallet example** (no auto-data):
```json
{
  "name": "My Binance Wallet",
  "address": "bc1qrealwalletaddress1234567890"
}
```

## Retrieving Transactions

1. List wallets → get the ID.
2. GET `/api/wallets/{id}/transactions`

Returns full details: date, type (buy/sell), asset, quantity, prices, fees, etc.

## Generating Tax Reports

Use the same payload for JSON, PDF, or preview.

**Payload fields**
- `wallet_ids`: Array of wallet IDs (1 or many → aggregates everything)
- `tax_year`: e.g. 2025 (SARS year runs 1 March previous year to end Feb)
- `other_income`: Optional — your other taxable income (R0 if none). Used to show exact marginal tax rate and extra tax you'll pay on the crypto gain.

**Report includes**
- Net capital gain
- After R40,000 exclusion
- Amount added to taxable income (40%)
- Estimated additional tax you'll actually pay (highlighted)
- Marginal tax rate
- Full disposal list
- Detailed FIFO breakdown for every sale

## Connecting to a React Frontend (Axios Examples)

Install Axios in React:
```bash
npm install axios
```

**Example React hooks**

```javascript
import axios from 'axios';
const API = 'http://127.0.0.1:8000/api';

// List wallets
const { data: wallets } = await axios.get(`${API}/wallets`);

// Create demo wallet
await axios.post(`${API}/wallets`, {
  name: 'Demo Wallet',
  address: 'demo-1A1zP1eP5QGefi2DMPTfTL5SLmv7DivfNa'
});

// Get transactions
const { data: transactions } = await axios.get(`${API}/wallets/${walletId}/transactions`);

// Calculate tax (JSON)
const report = await axios.post(`${API}/tax/calculate`, {
  wallet_ids: [1, 3],
  tax_year: 2025,
  other_income: 500000
});

// Download PDF
const response = await axios.post(`${API}/tax/export-pdf`, payload, { responseType: 'blob' });
const url = window.URL.createObjectURL(new Blob([response.data]));
const link = document.createElement('a');
link.href = url;
link.setAttribute('download', 'sars-crypto-tax-2025.pdf');
document.body.appendChild(link);
link.click();
```

## Technical Terms Explained Simply

- **FIFO (First In, First Out)**: When you sell crypto, the system matches the sale to your oldest purchases first to calculate gain/loss accurately.
- **Annual Exclusion**: SARS lets you ignore the first R40,000 of capital gains each year.
- **Inclusion Rate**: 40% of your taxable crypto gain is added to your normal income and taxed at your personal rate (18–45%).
- **Marginal Rate**: The tax percentage on your highest income bracket — this is what the crypto gain gets taxed at.
