# Migration Commands - Quick Reference

## PHP/Symfony Commands

### Rector - Dry-run (always start with this)
```bash
# PHP 8.2
vendor/bin/rector process --set=PHP_82 --dry-run

# PHP 8.3
vendor/bin/rector process --set=PHP_83 --dry-run

# PHP 8.4 (if available)
vendor/bin/rector process --set=PHP_84 --dry-run

# PHP 8.5 (if available)
vendor/bin/rector process --set=PHP_85 --dry-run

# Symfony 6.0
vendor/bin/rector process --set=SYMFONY_60 --dry-run

# Symfony 6.4
vendor/bin/rector process --set=SYMFONY_64 --dry-run

# Symfony 7.0
vendor/bin/rector process --set=SYMFONY_70 --dry-run

# Symfony 8.0 (if available)
vendor/bin/rector process --set=SYMFONY_80 --dry-run
```

### Rector - Application
```bash
# PHP 8.2
vendor/bin/rector process --set=PHP_82

# PHP 8.3
vendor/bin/rector process --set=PHP_83

# PHP 8.4 (if available)
vendor/bin/rector process --set=PHP_84

# PHP 8.5 (if available)
vendor/bin/rector process --set=PHP_85

# Symfony 6.0
vendor/bin/rector process --set=SYMFONY_60

# Symfony 6.4
vendor/bin/rector process --set=SYMFONY_64

# Symfony 7.0
vendor/bin/rector process --set=SYMFONY_70

# Symfony 8.0 (if available)
vendor/bin/rector process --set=SYMFONY_80
```

### PHP Tests
```bash
# PHPUnit
vendor/bin/phpunit

# Behat
vendor/bin/behat

# PHPStan
vendor/bin/phpstan analyse

# PHP-CS-Fixer (dry-run)
vendor/bin/php-cs-fixer fix --dry-run

# PHP-CS-Fixer (application)
vendor/bin/php-cs-fixer fix
```

### Composer
```bash
# Update Symfony
composer update symfony/* --with-all-dependencies

# Update PHPUnit
composer update phpunit/phpunit --with-all-dependencies

# Update all dependencies
composer update
```

## JavaScript/TypeScript Commands

### React - Migration
```bash
# React 18 (dry-run)
npx react-codemod react-18-upgrade src/ --dry

# React 18 (application)
npx react-codemod react-18-upgrade src/

# React 19 (if available)
npx react-codemod react-19-upgrade src/
```

### TypeScript
```bash
# Error verification
yarn tsc --noEmit > ts-errors.log

# Verification with display
yarn tsc --noEmit
```

### JS/TS Tests
```bash
# Unit tests
yarn unit

# Integration tests
yarn integration

# E2E Cypress tests
yarn test:e2e:run

# All tests
yarn test
```

### Lint and Formatting
```bash
# ESLint (dry-run)
yarn lint

# ESLint (fix)
yarn lint-fix

# Prettier (check)
yarn prettier:check

# Prettier (fix)
yarn prettier:run
```

### Build
```bash
# Build dev
yarn webpack-dev

# Build prod
yarn webpack
```

### Yarn
```bash
# Update TypeScript
yarn add -D typescript@^5.6.0

# Update React
yarn add react@^19.2.0 react-dom@^19.2.0

# Update Jest
yarn add -D jest@^29.0.0

# Update ESLint
yarn add -D eslint@^9.0.0

# Install dependencies
yarn install
```

## Typical Migration Sequence

### For a PHP/Symfony Rector Rule
```bash
# 1. Dry-run (example PHP 8.3)
vendor/bin/rector process --set=PHP_83 --dry-run

# 2. Review proposed changes
# 3. Apply
vendor/bin/rector process --set=PHP_83

# 4. Tests
vendor/bin/phpunit && vendor/bin/behat

# 5. Static analysis
vendor/bin/phpstan analyse

# 6. Formatting
vendor/bin/php-cs-fixer fix
```

### For a React Transformation
```bash
# 1. Dry-run
npx react-codemod react-18-upgrade src/ --dry

# 2. Review proposed changes
# 3. Apply
npx react-codemod react-18-upgrade src/

# 4. Tests
yarn unit && yarn integration

# 5. Lint
yarn lint

# 6. Build
yarn webpack-dev
```

### For a TypeScript Update
```bash
# 1. Update
yarn add -D typescript@^5.6.0
yarn install

# 2. Error verification
yarn tsc --noEmit > ts-errors.log

# 3. Manual correction (one error at a time)
# 4. Tests after each correction
yarn unit

# 5. Build
yarn webpack-dev
```

## Global Verification Commands

### Before Starting
```bash
# Verify everything works
vendor/bin/phpunit
yarn unit
yarn integration
```

### After Each Major Migration
```bash
# Complete PHP tests
vendor/bin/phpunit && vendor/bin/behat

# Complete JS/TS tests
yarn test

# PHP static analysis
vendor/bin/phpstan analyse
vendor/bin/php-cs-fixer fix --dry-run

# JS/TS Lint
yarn lint
yarn prettier:check

# Build
yarn webpack-dev
```

## Diagnostic Commands

### Check Versions
```bash
# PHP
php -v

# Node.js
node -v

# Composer
composer --version

# Yarn
yarn --version

# Installed Symfony versions
composer show | grep symfony/

# Installed React versions
yarn list --pattern "react" --depth=0
```

### Check Errors
```bash
# PHP errors
vendor/bin/phpstan analyse

# TypeScript errors
yarn tsc --noEmit

# ESLint errors
yarn lint

# Prettier errors
yarn prettier:check
```

## Useful Git Commands

```bash
# Create migration branch
git checkout -b upgrade-2026-01

# Commit after each successful rule
git add .
git commit -m "feat: apply PHP_83 rule X"

# View changes
git diff

# View history
git log --oneline
```

## Important Notes

1. **Always do a dry-run before applying**
2. **Always run tests after each change**
3. **Document in appropriate tracking files**
4. **Make frequent commits**
5. **Don't hesitate to rollback if necessary**
