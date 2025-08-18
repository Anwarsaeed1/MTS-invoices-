# Invoice Processor - SOLID Principles Implementation

A comprehensive PHP application demonstrating SOLID principles, design patterns, and best practices for invoice processing. This project includes Excel import functionality, flexible export options, and a **truly dynamic database architecture** that supports SQLite, MySQL, and MongoDB.

## Project Overview

This application processes invoices from Excel files and provides flexible export options (JSON/XML). It demonstrates:

- **SOLID Principles** - All five principles properly implemented
- **Design Patterns** - Strategy, Factory, Repository, Command, Dependency Injection, Adapter
- **Excel Processing** - Professional Excel file reading using PhpSpreadsheet library
- **Dynamic Database Architecture** - Automatic database setup and switching between SQLite, MySQL, and MongoDB
- **Clean Architecture** - Well-structured, testable, and maintainable code

## Dynamic Database Setup

The application now supports **automatic database setup** based on your `.env` configuration. You can easily switch between SQLite, MySQL, and MongoDB without manual setup.

### Supported Databases

1. **SQLite** - File-based database (default)
2. **MySQL** - Relational database
3. **MongoDB** - NoSQL document database

### Quick Setup

```bash
# Setup database based on current .env configuration
php bin/console setup-database

# Switch to MySQL and setup
php bin/console switch-database mysql
php bin/console setup-database

# Switch to MongoDB and setup
php bin/console switch-database mongodb
php bin/console setup-database
```

### Environment Configuration

Copy `env.example` to `.env` and configure your preferred database:

```env
# For SQLite (default)
DB_TYPE=sqlite
DB_DATABASE=invoices

# For MySQL
DB_TYPE=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=invoices
DB_USERNAME=root
DB_PASSWORD=

# For MongoDB
DB_TYPE=mongodb
DB_HOST=localhost
DB_PORT=27017
DB_DATABASE=invoices
DB_USERNAME=
DB_PASSWORD=
```

### Features

- **Automatic database creation** - Creates databases if they don't exist
- **Automatic table/collection creation** - Sets up all required tables/collections
- **Index creation** - Creates performance indexes automatically
- **Cross-database compatibility** - Same code works with all databases
- **Environment-based switching** - Easy switching via .env file
- **Error handling** - Comprehensive error messages and troubleshooting tips

## Architecture Overview

### SOLID Principles Implementation

#### 1. **Single Responsibility Principle (SRP)**
- Each class has a single, well-defined responsibility
- `ExportService` handles only export operations
- `ImportService` handles only import operations
- `InvoiceService` orchestrates business logic
- Repositories handle only data access
- `PhpSpreadsheetReader` handles only Excel file parsing
- `DatabaseSetup` handles only database initialization

#### 2. **Open/Closed Principle (OCP)**
- The system is open for extension but closed for modification
- New export formats can be added by implementing `ExportStrategyInterface`
- New import formats can be added by implementing `ImportStrategyInterface`
- New database types can be added by implementing `DatabaseAdapterInterface`
- Repository code never needs to change when adding new databases

#### 3. **Liskov Substitution Principle (LSP)**
- All repository implementations can be substituted without breaking the application
- `FlexibleCustomerRepository` works with any database adapter seamlessly
- Different database adapters can be swapped without changing repository code
- Same repository works with SQLite, MySQL, MongoDB, etc.

#### 4. **Interface Segregation Principle (ISP)**
- Interfaces are specific to client needs
- `CustomerRepositoryInterface` extends `RepositoryInterface` with customer-specific methods
- `ExportStrategyInterface` focuses only on export operations
- `DatabaseAdapterInterface` abstracts only database operations

#### 5. **Dependency Inversion Principle (DIP)**
- High-level modules depend on abstractions, not concretions
- Services depend on repository interfaces, not concrete implementations
- Commands depend on service interfaces, not concrete services
- Dependency injection container manages all dependencies

## 🎨 Design Patterns Implemented

### 1. **Strategy Pattern**
- **Export Strategies**: `JsonExportStrategy`, `XmlExportStrategy`
- **Import Strategies**: `ExcelImportStrategy` with `PhpSpreadsheetReader`
- Allows easy addition of new formats without modifying existing code

### 2. **Factory Pattern**
- **ConnectionFactory**: Creates different database connections
- Supports SQLite, MySQL, MongoDB
- Easy to extend for new database types

### 3. **Adapter Pattern**
- **DatabaseAdapterInterface**: Abstracts database operations
- **SqliteAdapter, MysqlAdapter, MongoDbAdapter**: Database-specific implementations
- **FlexibleCustomerRepository**: Database-agnostic repository that works with any adapter
- Eliminates need for database-specific repository names

### 4. **Repository Pattern**
- Abstract data access layer
- `AbstractRepository` provides common functionality
- `FlexibleCustomerRepository` works with any database adapter
- No database-specific repository names needed

### 5. **Command Pattern**
- `CommandInterface` defines command contract
- `CommandHandler` manages command execution
- Easy to add new commands

### 6. **Dependency Injection**
- `Container` manages all dependencies
- Automatic resolution of dependencies
- Singleton pattern for shared instances

## Project Structure

```
invoice-processor/
├──  bin/                     # Command line tools
│   └── console                 # Main CLI entry point
├──  database/                # Database files (auto-created)
├──  public/                  # Web interface
│   └── index.php              # Web entry point
├──  src/                     # Main application code
│   ├──  Commands/           # CLI commands
│   │   ├── SetupDatabaseCommand.php  # Dynamic database setup
│   │   ├── SwitchDatabaseCommand.php # Database switching
│   │   ├── ImportCommand.php         # Data import
│   │   └── ExportCommand.php         # Data export
│   ├──  Contracts/          # Interfaces and contracts
│   │   ├── Commands/        # Command interfaces
│   │   ├── Database/        # Database interfaces
│   │   ├── Export/          # Export strategy interfaces
│   │   ├── Import/          # Import strategy interfaces
│   │   ├── Repositories/    # Repository interfaces
│   │   └── Services/        # Service interfaces
│   ├──  Core/               # Core framework classes
│   │   ├── DatabaseSetup.php        # Dynamic database setup
│   │   ├── DatabaseManager.php      # Database management
│   │   ├── DynamicEnvironmentLoader.php # Environment management
│   │   ├── Container.php            # Dependency injection
│   │   ├── CommandHandler.php       # Command handling
│   │   └── Router.php               # Web routing
│   ├──  Database/           # Database layer
│   │   ├── Adapters/       # Database adapters
│   │   │   ├── SqliteAdapter.php
│   │   │   ├── MysqlAdapter.php
│   │   │   └── MongoDbAdapter.php
│   │   └── Connection.php   # Database connection
│   ├──  Export/             # Export strategies
│   │   ├── JsonExportStrategy.php
│   │   └── XmlExportStrategy.php
│   ├──  Import/             # Import strategies
│   │   ├── ExcelImportStrategy.php
│   │   └── PhpSpreadsheetReader.php
│   ├──  Models/             # Data models
│   │   ├── Customer.php
│   │   ├── Invoice.php
│   │   └── Product.php
│   ├──  Repositories/       # Data access layer
│   │   ├── AbstractRepository.php
│   │   ├── FlexibleCustomerRepository.php
│   │   ├── FlexibleInvoiceRepository.php
│   │   └── FlexibleProductRepository.php
│   ├──  Services/           # Business logic
│   │   ├── ExportService.php
│   │   ├── ImportService.php
│   │   └── InvoiceService.php
│   └──  Controllers/        # Web controllers
│       └── InvoiceController.php
├──  tests/                  # Unit tests
├──  config/                 # Configuration files
│   └── database.php         # Database configuration
├──  .env                    # Environment variables
├──  env.example             # Environment template
├──  composer.json           # Dependencies
└──  README.md               # This file
```

## Installation & Setup

### Prerequisites
- PHP 8.0 or higher
- Composer
- Web server (Apache/Nginx) or PHP built-in server
- MySQL (optional, for MySQL database)
- MongoDB (optional, for MongoDB database)

### Installation Steps

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd invoice-processor
   ```

2. **Install dependencies**
   ```bash
   composer install
   ```

3. **Setup database (Dynamic)**
   ```bash
   # Setup database based on .env configuration
   php bin/console setup-database
   ```

4. **Import sample data**
   ```bash
   php bin/console import data.xlsx
   ```

5. **Export data**
   ```bash
   # Export to JSON
   php bin/console export json
   
   # Export to XML
   php bin/console export xml
   ```

6. **Database switching (Optional)**
   ```bash
   # Switch to MySQL
   php bin/console switch-database mysql
   php bin/console setup-database
   
   # Switch to MongoDB
   php bin/console switch-database mongodb
   php bin/console setup-database
   
   # Switch back to SQLite
   php bin/console switch-database sqlite
   php bin/console setup-database
   ```

7. **Start web server**
   ```bash
   php -S localhost:8000 -t public
   ```

## Excel Import Features

### Professional Excel Processing with PhpSpreadsheet
- **PhpSpreadsheet Library** - Industry-standard Excel processing library
- **Robust Error Handling** - Comprehensive validation and error messages
- **Memory Management** - Proper cleanup to prevent memory leaks
- **Flexible Header Detection** - Handles typos and column name variations
- **Professional API** - Uses industry-standard PhpSpreadsheet methods

### Supported Excel Features
-  `.xlsx` files (Excel 2007+)
-  `.xls` files (legacy Excel)
-  `.csv` files (comma-separated values)
-  `.ods` files (OpenDocument Spreadsheet)
-  Automatic date conversion from Excel format
-  Professional cell value formatting
-  Comprehensive error handling and validation
-  Memory-efficient processing for large files

## 🔧 Usage Examples

### Available Commands

```bash
# Database Management
php bin/console setup-database          # Setup database based on .env
php bin/console switch-database mysql   # Switch to MySQL
php bin/console switch-database sqlite  # Switch to SQLite
php bin/console switch-database mongodb # Switch to MongoDB

# Data Operations
php bin/console import data.xlsx        # Import Excel data
php bin/console export json             # Export to JSON
php bin/console export xml              # Export to XML

# Help
php bin/console                        # Show all available commands
```

### API Endpoints

- `GET /api/invoices` - List all invoices with customer details
- `GET /api/invoices/{id}` - Get specific invoice with items and customer details

### Adding New Export Format

```php
class CsvExportStrategy implements ExportStrategyInterface
{
    public function export(array $data): string
    {
        // CSV export implementation
    }
    
    public function getContentType(): string
    {
        return 'text/csv';
    }
    
    public function getFileExtension(): string
    {
        return 'csv';
    }
}

// Register the new strategy
$exportService = $container->resolve(ExportService::class);
$exportService->registerStrategy('csv', new CsvExportStrategy());
```

### Adding New Import Format

```php
class CsvImportStrategy implements ImportStrategyInterface
{
    public function import(string $filePath): array
    {
        // CSV import implementation
    }
    
    public function canHandle(string $filePath): bool
    {
        return pathinfo($filePath, PATHINFO_EXTENSION) === 'csv';
    }
}

// Register the new strategy
$importService = $container->resolve(ImportService::class);
$importService->registerStrategy(new CsvImportStrategy());
```

### Adding New Database Type

```php
class PostgresAdapter implements DatabaseAdapterInterface
{
    // PostgreSQL implementation
}

// Register in DatabaseManager
public function createPostgresAdapter(array $config): DatabaseAdapterInterface
{
    // PostgreSQL adapter creation
}
```

## Testing

### Running Tests

```bash
# Run all tests
vendor/bin/phpunit

# Run specific test
vendor/bin/phpunit tests/Import/PhpSpreadsheetReaderTest.php
```

### Available Tests

- `PhpSpreadsheetReaderTest` - Excel file reading functionality
- `InvoiceServiceTest` - Business logic testing
- `ConnectionTest` - Database connection testing
- `DatabaseFlexibilityTest` - Cross-database compatibility

##  Database Flexibility

### Truly Database-Agnostic Architecture

The project uses a **database-agnostic repository pattern** with adapters, eliminating the need for database-specific repository names.


```php
// Single repository works with any database
class FlexibleCustomerRepository implements CustomerRepositoryInterface
{
    public function __construct(DatabaseAdapterInterface $adapter)
    {
        // Works with SQLite, MySQL, MongoDB, PostgreSQL, etc.
    }
}
```

### Benefits

- **Zero Code Changes** - Switch databases by updating `.env` only
- **No Database-Specific Names** - Repositories don't contain database names
- **Easy Testing** - Mock any database adapter for testing
- **Future-Proof** - Add new databases without changing existing code
- **Consistent API** - Same methods work across all databases

## Configuration

### Environment Variables

```env
# Database Configuration
DB_TYPE=sqlite                    # sqlite, mysql, mongodb
DB_DATABASE=invoices             # Database name
DB_HOST=localhost                # Database host
DB_PORT=3306                     # Database port
DB_USERNAME=root                 # Database username
DB_PASSWORD=                     # Database password

# Application Configuration
APP_NAME="Invoice Processor"
APP_ENV=development
APP_DEBUG=true

# Server Configuration
SERVER_HOST=localhost
SERVER_PORT=8000
```

### Database Configuration

The `config/database.php` file contains all database connection configurations:

```php
return [
    'default' => env('DB_TYPE', 'sqlite'),
    'connections' => [
        'sqlite' => [...],
        'mysql' => [...],
        'mongodb' => [...],
    ]
];
```

## Performance Features

- **Database Indexing** - Automatic index creation for better performance
- **Memory Management** - Efficient Excel file processing
- **Connection Pooling** - Optimized database connections
- **Caching Ready** - Architecture supports easy caching implementation

## Security Features

- **Input Validation** - Comprehensive data validation
- **SQL Injection Prevention** - Parameterized queries
- **Error Handling** - Secure error messages
- **Environment Separation** - Development/production configuration

## Scalability

- **Horizontal Scaling** - Stateless architecture
- **Database Sharding** - Adapter pattern supports multiple databases
- **Microservice Ready** - Clean separation of concerns
- **API-First Design** - RESTful endpoints for integration


## Project Goals Achieved

**SOLID Principles** - All five principles properly implemented  
**Design Patterns** - Strategy, Factory, Repository, Command, DI, Adapter  
**Excel Processing** - Professional PhpSpreadsheet integration  
**Database Flexibility** - Truly database-agnostic architecture  
**Dynamic Setup** - Automatic database creation and configuration  
**Clean Architecture** - Well-structured, testable, maintainable code  
**Professional Quality** - Production-ready with comprehensive error handling  
**Documentation** - Complete documentation and examples  



