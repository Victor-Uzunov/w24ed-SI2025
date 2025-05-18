# CI/CD Pipeline Documentation

## Overview
The project uses GitHub Actions for continuous integration and deployment. The pipeline consists of two main jobs: `build` and `test`.

## Pipeline Structure

### Build Job
The build job handles code quality checks and dependency installation.

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

6. **Code Style Check**
   - Runs: PHP_CodeSniffer
   - Uses PSR-12 standard
   - Results saved to: `build/reports/phpcs.txt`
   - Non-blocking: Continues even if issues found

7. **Static Analysis**
   - Runs: PHPStan (Level 5)
   - Results saved to: `build/reports/phpstan.txt`
   - Non-blocking: Continues even if issues found

8. **Upload Reports**
   - Uses: `actions/upload-artifact@v3`
   - Stores code quality reports
   - Retention period: 7 days

### Test Job
The test job runs after the build job completes and handles testing and code coverage.

#### Steps:
1. **Setup Environment**
   - Similar PHP setup as build job
   - Creates required directories

2. **Run Tests**
   - Executes PHPUnit test suite
   - Generates code coverage report

3. **Upload Coverage**
   - Stores coverage report as artifact
   - Retention period: 7 days

## Artifacts
The pipeline produces two main artifacts:

1. **build-reports**
   - Location: `build/reports/`
   - Contains:
     - PHP_CodeSniffer results
     - PHPStan analysis results

2. **coverage-report**
   - Location: `coverage/`
   - Contains HTML coverage report

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

# Generate coverage report
composer test-coverage

# Run all checks (build)
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
- Check artifact reports for detailed error messages
- Consult PHP_CodeSniffer and PHPStan documentation 