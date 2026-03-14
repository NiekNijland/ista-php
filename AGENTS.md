# AGENTS.md

PHP client library for the Ista energy consumption API. PHP 8.4+, PSR-4 autoloading, PHPStan level 8, Laravel Pint formatting.

## Build & Run Commands

```bash
# Install dependencies
composer install

# Run unit tests (default suite, excludes Integration/)
composer test

# Run a single test file
vendor/bin/phpunit tests/Unit/Data/MeterTest.php

# Run a single test method
vendor/bin/phpunit --filter test_from_array_creates_meter

# Run a single test method within a specific file
vendor/bin/phpunit tests/Unit/IstaTest.php --filter test_http_error_throws_ista_exception

# Run integration tests (requires real API credentials)
composer test-integration

# Run all test suites
composer test-all

# Run tests with coverage report
composer test-coverage

# Format code (Laravel Pint - default laravel preset)
composer format        # or: composer pint

# Static analysis (PHPStan level 8, src/ only)
composer analyse

# Automated refactoring (Rector: deadCode, codeQuality, typeDeclarations)
composer rector
composer rector:dry-run

# Full code style pipeline (rector + pint + phpstan)
composer codestyle
```

## Project Structure

```
src/
  Ista.php                  # Main client (implements IstaInterface)
  IstaInterface.php         # Public API contract
  Data/                     # Readonly value objects / DTOs
  Exception/                # Single IstaException class
  Support/                  # Internal helpers (Authenticator, CacheStore, JwtToken)
  Testing/                  # Fakes and factory classes (shipped with package)
tests/
  ArrayCache.php            # Shared PSR-16 in-memory cache helper
  Fixtures/                 # JSON/HTML fixture files for tests
  Unit/                     # Unit tests (mirrors src/ structure)
  Integration/              # Live API tests (separate suite)
```

## Code Style Guidelines

### File Header

Every PHP file starts with exactly:

```php
<?php

declare(strict_types=1);
```

One blank line between `<?php` and `declare(strict_types=1);`.

### Namespaces & Imports

- Root namespace: `NiekNijland\Ista\` (PSR-4 mapped to `src/`)
- Test namespace: `NiekNijland\Ista\Tests\` (mapped to `tests/`)
- Sub-namespaces mirror directory structure: `Data\`, `Exception\`, `Support\`, `Testing\`
- All used classes must be explicitly imported via `use` statements (no inline FQCNs)
- Imports sorted alphabetically in a single block (enforced by Pint)
- No aliasing (`as`) — use full class names

### Formatting (Laravel Pint defaults / PSR-12)

- 4 spaces indentation, LF line endings, UTF-8
- Opening brace `{` on same line as class/method declaration
- Trailing commas everywhere: parameter lists, arrays, argument lists
- String concatenation: `'message: '.$variable` (dot with no surrounding spaces)
- `new ClassName` without `()` when no constructor arguments
- Named arguments in constructor calls: `new Meter(meterId: $id, value: $v)`
- `static fn` for all arrow functions (never bare `fn`)
- `array_map` with `static fn` for collection transforms; wrap in `array_values()` for list output

### Type System

- PHPStan level 8 — all code must pass the strictest analysis
- Every parameter and return type must have a native type declaration
- Nullable via `?Type` prefix (e.g. `?ClientInterface`)
- `readonly class` for all DTOs/value objects
- Constructor promotion for all DTO properties (public) and most dependency injection (private readonly)
- Typed constants: `private const string NAME = 'value'`
- Docblocks only when native types are insufficient:
  - Array shapes: `@param array<string, mixed>`, `@return array{key: type}`
  - Typed lists: `@param Customer[]`, `@return list<array<string, mixed>>`
  - Generics: `@template T of object`
  - `@throws` on interface methods
- Never add redundant docblocks that just restate type hints

### Naming Conventions

| Element         | Convention           | Example                              |
|-----------------|----------------------|--------------------------------------|
| Classes         | PascalCase           | `ConsumptionPeriod`, `CacheStore`    |
| DTOs/VOs        | No suffix            | `Meter`, `Customer`                  |
| Result DTOs     | `*Result`            | `UserValuesResult`                   |
| Interfaces      | `*Interface`         | `IstaInterface`                      |
| Exceptions      | `*Exception`         | `IstaException`                      |
| Fakes           | `Fake*`              | `FakeIsta`                           |
| Factories       | `*Factory`           | `MeterFactory`                       |
| Methods         | camelCase            | `getUserValues`, `fromArray`         |
| Properties      | camelCase            | `$meterId`, `$httpClient`            |
| Constants       | UPPER_SNAKE_CASE     | `USER_VALUES_URL`                    |
| Test methods    | `test_snake_case`    | `test_from_array_creates_meter`      |
| Test classes    | `*Test`              | `MeterTest`, `IstaTest`              |

### Visibility

- DTOs: `public` promoted properties (via `readonly class`)
- Non-DTO classes: `private readonly` for dependencies and state
- No `protected` — only `public` and `private`
- API methods: `public`; internal helpers: `private`

### Error Handling

- Single custom exception: `IstaException extends RuntimeException`
- Always chain the original exception: `throw new IstaException('msg: '.$e->getMessage(), 0, $e)`
- Error code is always `0` when wrapping
- Cache failures silently ignored (empty catch blocks)
- Invalid JWT tokens return `true` for `isExpired()` instead of throwing
- Interface documents `@throws IstaException`

### Factory / Hydration Pattern

- Static `fromArray(array $data): self` for hydrating from API response arrays
- `toArray(): array` for serialization
- Test factories use `static make(...)` with sensible defaults and named argument overrides

## Test Conventions

- Extend `PHPUnit\Framework\TestCase` directly (no custom base class)
- Method naming: `test_snake_case_description` (no `@test` annotations)
- Directory structure mirrors `src/`: `tests/Unit/Data/MeterTest.php` tests `src/Data/Meter.php`
- Prefer `assertSame` over `assertEquals` (strict comparison)
- Use `expectException` + `expectExceptionMessage` for exception tests (not try/catch)
- HTTP mocking: Guzzle `MockHandler` + `Middleware::history` (no Mockery or PHPUnit mocks)
- Test fakes: `FakeIsta` for integration-style unit tests, with `seed*` and `assert*` methods
- Fixtures loaded via `file_get_contents(__DIR__.'/../Fixtures/'.$name)`
- Each test method is self-contained; private helpers at top of test class

## CI Pipeline

- **Codestyle workflow**: Rector -> Pint -> PHPStan (auto-commits formatting fixes)
- **Tests workflow**: PHPUnit unit suite on PHP 8.4, ubuntu + windows, prefer-lowest + prefer-stable
- **Dependabot**: auto-merges minor/patch dependency updates
