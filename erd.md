# Entity Relationship Diagram (ERD)

## Database Schema

Based on the invoice processing requirements, the database consists of the following tables:

### Tables Structure

#### 1. customers
- `id` (INTEGER, PRIMARY KEY, AUTOINCREMENT)
- `name` (TEXT, NOT NULL)
- `address` (TEXT, NOT NULL)

#### 2. products
- `id` (INTEGER, PRIMARY KEY, AUTOINCREMENT)
- `name` (TEXT, NOT NULL)
- `price` (DECIMAL(10,2), NOT NULL)

#### 3. invoices
- `id` (INTEGER, PRIMARY KEY, AUTOINCREMENT)
- `invoice_date` (DATE, NOT NULL)
- `customer_id` (INTEGER, FOREIGN KEY -> customers.id)
- `grand_total` (DECIMAL(10,2), NOT NULL)

#### 4. invoice_items
- `id` (INTEGER, PRIMARY KEY, AUTOINCREMENT)
- `invoice_id` (INTEGER, FOREIGN KEY -> invoices.id)
- `product_id` (INTEGER, FOREIGN KEY -> products.id)
- `quantity` (INTEGER, NOT NULL)
- `total` (DECIMAL(10,2), NOT NULL)

## Relationships

1. **customers** (1) ←→ (N) **invoices**
   - One customer can have many invoices
   - Each invoice belongs to one customer

2. **invoices** (1) ←→ (N) **invoice_items**
   - One invoice can have many items
   - Each item belongs to one invoice

3. **products** (1) ←→ (N) **invoice_items**
   - One product can be in many invoice items
   - Each invoice item references one product

## ERD Visualization

```
┌─────────────┐         ┌─────────────┐         ┌─────────────┐
│  customers  │         │   invoices  │         │invoice_items│
├─────────────┤         ├─────────────┤         ├─────────────┤
│ id (PK)     │◄────────┤ id (PK)     │◄────────┤ id (PK)     │
│ name        │         │ invoice_date│         │ invoice_id  │
│ address     │         │ customer_id │         │ product_id  │
└─────────────┘         │ grand_total │         │ quantity    │
                        └─────────────┘         │ total       │
                                │               └─────────────┘
                                │                       
                                │                       
                                │               ┌─────────────┐
                                │               │   products  │
                                │               ├─────────────┤
                                └──────────────►│ id (PK)     │
                                                │ name        │
                                                │ price       │
                                                └─────────────┘
```

## SQL Schema

```sql
CREATE TABLE customers (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    address TEXT NOT NULL
);

CREATE TABLE products (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    price DECIMAL(10,2) NOT NULL
);

CREATE TABLE invoices (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    invoice_date DATE NOT NULL,
    customer_id INTEGER NOT NULL,
    grand_total DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (customer_id) REFERENCES customers(id)
);

CREATE TABLE invoice_items (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    invoice_id INTEGER NOT NULL,
    product_id INTEGER NOT NULL,
    quantity INTEGER NOT NULL,
    total DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (invoice_id) REFERENCES invoices(id),
    FOREIGN KEY (product_id) REFERENCES products(id)
);
```
