# Invoice Processor - SOLID Principles Implementation

A comprehensive PHP application demonstrating SOLID principles, design patterns, and best practices for invoice processing. This project includes Excel import functionality, flexible export options, and a clean, maintainable architecture.

## 🎯 Project Overview

This application processes invoices from Excel files and provides flexible export options (JSON/XML). It demonstrates:

- **SOLID Principles** - All five principles properly implemented
- **Design Patterns** - Strategy, Factory, Repository, Command, Dependency Injection
- **Excel Processing** - Professional Excel file reading using PhpSpreadsheet library
- **Database Flexibility** - Support for SQLite, MySQL, PostgreSQL, MongoDB
- **Clean Architecture** - Well-structured, testable, and maintainable code

##  Architecture Overview

### SOLID Principles Implementation

#### 1. **Single Responsibility Principle (SRP)**
- Each class has a single, well-defined responsibility
- `ExportService` handles only export operations
- `ImportService` handles only import operations
- `InvoiceService` orchestrates business logic
- Repositories handle only data access
- `PhpSpreadsheetReader` handles only Excel file parsing

#### 2. **Open/Closed Principle (OCP)**
- The system is open for extension but closed for modification
- New export formats can be added by implementing `ExportStrategyInterface`
- New import formats can be added by implementing `ImportStrategyInterface`
- New database types can be added by implementing `ConnectionInterface`

#### 3. **Liskov Substitution Principle (LSP)**
- All repository implementations can be substituted without breaking the application
- `MongoDBCustomerRepository` can replace `CustomerRepository` seamlessly
- Different database connections can be swapped using the factory pattern

#### 4. **Interface Segregation Principle (ISP)**
- Interfaces are specific to client needs
- `CustomerRepositoryInterface` extends `RepositoryInterface` with customer-specific methods
- `ExportStrategyInterface` focuses only on export operations

#### 5. **Dependency Inversion Principle (DIP)**
- High-level modules depend on abstractions, not concretions
- Services depend on repository interfaces, not concrete implementations
- Commands depend on service interfaces, not concrete services
- Dependency injection container manages all dependencies

## Design Patterns Implemented

### 1. **Strategy Pattern**
- **Export Strategies**: `JsonExportStrategy`, `XmlExportStrategy`
- **Import Strategies**: `ExcelImportStrategy` with `PhpSpreadsheetReader`
- Allows easy addition of new formats without modifying existing code

### 2. **Factory Pattern**
- **ConnectionFactory**: Creates different database connections
- Supports SQLite, MySQL, PostgreSQL
- Easy to extend for new database types

### 3. **Repository Pattern**
- Abstract data access layer
- `AbstractRepository` provides common functionality
- Specific repositories implement domain-specific operations

### 4. **Command Pattern**
- `CommandInterface` defines command contract
- `CommandHandler` manages command execution
- Easy to add new commands

### 5. **Dependency Injection**
- `Container` manages all dependencies
- Automatic resolution of dependencies
- Singleton pattern for shared instances

##  Project Structure

```
invoice-processor/
├──  bin/                     # Command line tools
│   └── console                 # Main CLI entry point
├──  database/                # Database setup and files
│   └── setup.php              # Database initialization
├──  public/                  # Web interface
│   └── index.php              # Web entry point
├──  src/                     # Main application code
│   ├──  Commands/           # CLI commands
│   ├──  Contracts/          # Interfaces and contracts
│   ├──  Core/               # Core framework classes
│   ├──  Database/           # Database layer
│   ├──  Export/             # Export strategies
│   ├──  Import/             # Import strategies (PhpSpreadsheetReader)
│   ├──  Models/             # Data models
│   ├──  Repositories/       # Data access layer
│   └──  Services/           # Business logic
├──  tests/                   # Unit tests
├──  vendor/                  # Composer dependencies
├──  .gitignore              # Git ignore rules
├──  composer.json           # Project dependencies
├──  composer.lock           # Locked dependency versions
├──  data.xlsx               # Sample Excel data
├──  erd.md                  # Database design documentation
├──  MTS-invoice-erd.png     # ERD diagram
├──  phpunit.xml             # PHPUnit configuration
└──  README.md               # Project documentation
```

## 🚀 Quick Start

### Prerequisites
- PHP 8.0+
- Composer
- SQLite (or MySQL/x)

### Installation

1. **Clone the repository**
```bash
git clone <repository-url>
cd invoice-processor
```

2. **Install dependencies**
   ```bash
   composer install
   ```

3. **Setup database**
   ```bash
php database/setup.php
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

## 📊 Excel Import Features

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

### PhpSpreadsheet Implementation Benefits
- **🔧 Professional Library** - Industry-standard Excel processing
- **⚡ Performance** - Optimized for large Excel files
- **🛡️ Reliability** - Comprehensive error handling and validation
- **📅 Date Handling** - Automatic Excel date conversion
- **🧹 Memory Management** - Proper resource cleanup
- **🔍 Flexible Headers** - Handles column name variations and typos

### Technical Implementation
The `PhpSpreadsheetReader` class provides:
- **File Validation** - Checks file existence and supported formats
- **Header Detection** - Flexible column name matching with typo tolerance
- **Data Processing** - Efficient row-by-row processing with memory management
- **Date Conversion** - Automatic Excel timestamp to PHP date conversion
- **Error Handling** - Comprehensive validation and clear error messages

## 🔧 Usage Examples

### Database Flexibility

```php
// SQLite (default)
$container = new Container();

// MySQL
$container->bind(ConnectionInterface::class, function () {
    return ConnectionFactory::createMysql(
        'localhost',
        'invoice_db',
        'username',
        'password'
    );
});

// MongoDB Repository
$container->bind(CustomerRepositoryInterface::class, function () {
    $mongoClient = new MongoDB\Client();
    return new MongoDBCustomerRepository($mongoClient);
});
```

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
    
    public function getSupportedExtensions(): array
    {
        return ['csv'];
    }
}

// Register the new strategy
$importService = $container->resolve(ImportService::class);
$importService->registerStrategy(new CsvImportStrategy());
```

## CLI Commands

```bash
# Export invoices to JSON
php bin/console export json

# Export invoices to XML
php bin/console export xml

# Import from Excel file
php bin/console import data.xlsx
```
## Web Interface

Access the web interface to view and export data:

```bash
# Start PHP development server
php -S localhost:8000 -t public

# Visit in browser
http://localhost:8000
```

##  Testing

The application includes comprehensive tests:

```bash
# Run all tests
vendor/bin/phpunit

# Run specific test suite
vendor/bin/phpunit --testsuite Import
vendor/bin/phpunit --testsuite Services
vendor/bin/phpunit --testsuite Database
```

##  Benefits of This Architecture

1. ** Flexibility**: Easy to switch between different databases, export formats, and import formats
2. ** Maintainability**: Clear separation of concerns and single responsibilities
3. ** Testability**: Dependencies are injected and can be easily mocked
4. ** Extensibility**: New features can be added without modifying existing code
5. ** Scalability**: Architecture supports growth and complexity
6. ** Excel Processing**: Professional Excel reading using PhpSpreadsheet library
7. ** Error Handling**: Robust error handling and validation

## 🔄 Migration from Old Architecture

The refactoring addressed these specific issues:

1. ** Tight Coupling**: Commands were directly instantiating repositories and database connections
2. ** LSP Violations**: No interface contracts for repositories and services
3. ** DIP Violations**: High-level modules depended on concrete implementations
4. ** Poor Flexibility**: Hard to switch databases or add new export/import formats
5. ** Missing Patterns**: No use of proven design patterns for common problems

## ✨ Key Improvements

- ** SOLID Principles**: All five principles properly implemented
- ** Design Patterns**: Strategy, Factory, Repository, Command, DI patterns
- ** Interface Contracts**: Clear interfaces for all major components
- ** Dependency Injection**: Automatic dependency resolution
- ** Database Flexibility**: Easy to switch between SQLite, MySQL, PostgreSQL, MongoDB
- ** Format Flexibility**: Easy to add new export/import formats
- ** Excel Processing**: Professional Excel file reading using PhpSpreadsheet
- ** Testability**: All components can be easily unit tested
- ** Maintainability**: Clear separation of concerns and responsibilities
- ** Clean Code**: No unnecessary files, proper structure

