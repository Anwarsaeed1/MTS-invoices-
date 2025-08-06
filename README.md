# Invoice Processor

A PHP-based invoice processing system that can import data from Excel files and export to JSON/XML formats.

## Features

- Import invoice data from Excel files
- Export data to JSON and XML formats
- RESTful API endpoints
- Command-line interface
- SQLite database with flexible design for other DBMS
- PSR-compliant code following SOLID principles

## ERD (Entity Relationship Diagram)

```
┌─────────────┐    ┌─────────────┐    ┌─────────────┐
│  customers  │    │  invoices   │    │  products   │
├─────────────┤    ├─────────────┤    ├─────────────┤
│ id (PK)     │    │ id (PK)     │    │ id (PK)     │
│ name        │    │ invoice_date│    │ name        │
│ address     │    │ customer_id │    │ price       │
└─────────────┘    │ grand_total │    └─────────────┘
                   └─────────────┘           │
                          │                  │
                          │                  │
                   ┌─────────────┐           │
                   │invoice_items│           │
                   ├─────────────┤           │
                   │ id (PK)     │           │
                   │ invoice_id  │◄──────────┘
                   │ product_id  │
                   │ quantity    │
                   │ total       │
                   └─────────────┘
```

## Installation

1. Clone the repository
2. Install dependencies:
   ```bash
   composer install
   ```
3. Create the database:
   ```bash
   sqlite3 database/invoices.db < database/schema.sql
   ```

## Usage

### Command Line Interface

#### Import Excel Data
```bash
php bin/console import data.xlsx
```

#### Export Data
```bash
# Export to JSON
php bin/console export json

# Export to XML
php bin/console export xml
```

### Web Interface

### Serve This Project
--- php -S localhost:8000 -t public

#### API Endpoints

- `GET /api/invoices` - List invoices with pagination
- `GET /api/invoices/{id}` - Get specific invoice details
- `POST /api/import` - Import data from file

#### Export via Web

- `GET /export.php?format=json` - Export to JSON
- `GET /export.php?format=xml` - Export to XML

### Example API Usage

```bash
# List invoices
curl http://localhost/api/invoices

# Get specific invoice
curl http://localhost/api/invoices/1

# Export to JSON
curl http://localhost/export.php?format=json
```

## Project Structure

```
invoice-processor/
├── bin/
│   └── console                 # Command line interface
├── database/
│   ├── invoices.db            # SQLite database
│   └── schema.sql             # Database schema
├── public/
│   ├── index.php              # Main API entry point
│   └── export.php             # Web export script
├── src/
│   ├── Commands/              # Command line commands
│   ├── Controllers/           # API controllers
│   ├── Core/                  # Core framework
│   ├── Database/              # Database connection
│   ├── Exceptions/            # Custom exceptions
│   ├── Export/                # Export functionality
│   ├── Import/                # Import functionality
│   ├── Models/                # Domain models
│   ├── Repositories/          # Data access layer
│   └── Services/              # Business logic
├── tests/                     # PHPUnit tests
├── vendor/                    # Composer dependencies
├── composer.json
├── phpunit.xml
└── README.md
```

## Design Patterns Used

1. **Repository Pattern** - Data access abstraction
2. **Service Layer Pattern** - Business logic encapsulation
3. **Dependency Injection** - Loose coupling
4. **Command Pattern** - CLI commands
5. **Factory Pattern** - Object creation

## SOLID Principles

- **Single Responsibility** - Each class has one reason to change
- **Open/Closed** - Open for extension, closed for modification
- **Liskov Substitution** - Subtypes are substitutable
- **Interface Segregation** - Clients depend on specific interfaces
- **Dependency Inversion** - High-level modules don't depend on low-level modules

## Testing

Run tests with PHPUnit:

```bash
./vendor/bin/phpunit
```

## Database Flexibility

The application uses PDO for database operations, making it compatible with:
- SQLite
- any Database


To use a different database, update the connection string in the relevant files.

## Excel Import

The system includes a mock Excel parser. For production use, integrate with:
- PhpSpreadsheet
- Spout
- Or implement a custom Excel reader

## License

This project is open source and available under the MIT License. 