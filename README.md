# Invoice Processor - SOLID Principles Implementation

This project demonstrates a comprehensive implementation of SOLID principles and design patterns in PHP. The application has been refactored to address tight coupling, improve flexibility, and implement proper separation of concerns.

## Architecture Overview

### SOLID Principles Implementation

#### 1. **Single Responsibility Principle (SRP)**
- Each class has a single, well-defined responsibility
- `ExportService` handles only export operations
- `ImportService` handles only import operations
- `InvoiceService` orchestrates business logic
- Repositories handle only data access
- Etc 


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
- **Import Strategies**: `ExcelImportStrategy`
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
- `Service Container with Class name Container` manages all dependencies
- Automatic resolution of dependencies
- Singleton pattern for shared instances

##  Project Structure

```
src/
├── Contracts/                    # Interfaces (DIP)
│   ├── Commands/
│   ├── Database/
│   ├── Export/
│   ├── Import/
│   ├── Repositories/
│   └── Services/
├── Core/                        # Core infrastructure
│   ├── Container.php           # DI Container
│   ├── CommandHandler.php      # Command pattern
│   └── Database/
│       └── ConnectionFactory.php
├── Database/                    # Database implementations
│   └── Connection.php
├── Export/                      # Export strategies
│   ├── JsonExportStrategy.php
│   └── XmlExportStrategy.php
├── Import/                      # Import strategies
│   └── ExcelImportStrategy.php
├── Models/                      # Domain models
├── Repositories/                # Repository implementations
│   ├── AbstractRepository.php
│   ├── CustomerRepository.php
│   ├── InvoiceRepository.php
│   ├── ProductRepository.php
│   └── MongoDBCustomerRepository.php
├── Services/                    # Business logic services
│   ├── ExportService.php
│   ├── ImportService.php
│   └── InvoiceService.php
└── Commands/                    # CLI commands
    ├── ExportCommand.php
    └── ImportCommand.php
```

##  Usage Examples

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

##  Testing

The application includes comprehensive tests demonstrating the flexibility:

```bash
# Run tests
vendor/bin/phpunit
```

##  Benefits of This Architecture

1. **Flexibility**: Easy to switch between different databases, export formats, and import formats
2. **Maintainability**: Clear separation of concerns and single responsibilities
3. **Testability**: Dependencies are injected and can be easily mocked
4. **Extensibility**: New features can be added without modifying existing code
5. **Scalability**: Architecture supports growth and complexity

## Migration from Old Architecture

The refactoring addressed these specific issues:

1. **Tight Coupling**: Commands were directly instantiating repositories and database connections
2. **LSP Violations**: No interface contracts for repositories and services
3. **DIP Violations**: High-level modules depended on concrete implementations
4. **Poor Flexibility**: Hard to switch databases or add new export/import formats
5. **Missing Patterns**: No use of proven design patterns for common problems

## Key Improvements

- **SOLID Principles**: All five principles properly implemented
- **Design Patterns**: Strategy, Factory, Repository, Command, DI patterns
- **Interface Contracts**: Clear interfaces for all major components
- **Dependency Injection**: Automatic dependency resolution
- **Database Flexibility**: Easy to switch between SQLite, MySQL, PostgreSQL, MongoDB
- **Format Flexibility**: Easy to add new export/import formats
- **Testability**: All components can be easily unit tested
- **Maintainability**: Clear separation of concerns and responsibilities

This refactored architecture provides a solid foundation for a scalable, maintainable, and flexible invoice processing system.
