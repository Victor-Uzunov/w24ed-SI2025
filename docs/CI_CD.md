# CI/CD Pipeline Documentation

## Overview
The project uses GitHub Actions for continuous integration. The pipeline runs code quality checks and tests in a single job.

## Pipeline Structure

### Build and Test Job
This job handles all checks in a streamlined process.

#### Steps:
1. **Checkout Code**
   - Uses: `actions/checkout@v3`
   - Purpose: Fetches the repository code

2. **Setup PHP**
   - Uses: `shivammathur/setup-php@v2`
   - Configuration:
     - PHP version: 7.4
     - Extensions: PDO, JSON
     - Coverage: Xdebug
     - Tools: Composer v2, cs2pr

3. **Composer Validation**
   - Validates composer.json structure
   - Ensures dependency configuration is correct

4. **Dependency Caching**
   - Uses: `actions/cache@v3`
   - Caches Composer packages to speed up builds
   - Key based on composer.lock hash

5. **Install Dependencies**
   - Runs: `composer install`
   - Installs all required packages

6. **Code Quality Checks**
   - Runs PHP_CodeSniffer (PSR-12 standard)
   - Runs PHPStan (Level 5)
   - Non-blocking: Pipeline continues even if issues found

7. **Tests**
   - Creates database directory
   - Runs PHPUnit test suite

## Running Locally
You can run the same checks locally using Composer scripts:

```bash
# Install dependencies
composer install

# Run code style check
composer cs

# Run static analysis
composer phpstan

# Run tests
composer test

# Run all checks
composer build
```

## Configuration Files

### PHP_CodeSniffer (`phpcs.xml`)
- Uses PSR-12 standard
- Checks php/ and tests/ directories
- Line length limit: 120 characters
- Excludes vendor/ and database/

### PHPStan (`phpstan.neon`)
- Analysis level: 5
- Analyzes php/ and tests/ directories
- Excludes vendor/ and database/

## Troubleshooting

### Common Issues
1. **Composer Validation Fails**
   - Check composer.json syntax
   - Verify all required fields are present

2. **Code Style Issues**
   - Run `composer cs-fix` locally
   - Review phpcs.xml rules

3. **Static Analysis Errors**
   - Check PHPStan error report
   - Consider adding necessary type hints

### Getting Help
- Review GitHub Actions logs
- Consult PHP_CodeSniffer and PHPStan documentation 