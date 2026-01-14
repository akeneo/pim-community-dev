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
# PHP Tests (execute in this order: static analysis first, then runtime tests)
# 1. PHPStan - Static analysis (validates code before runtime tests)
vendor/bin/phpstan analyse

# 2. PHPUnit - Unit tests
vendor/bin/phpunit

# 3. Behat - Functional tests
vendor/bin/behat

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

# 4. Tests (PHPStan first, then runtime tests)
vendor/bin/phpstan analyse && vendor/bin/phpunit && vendor/bin/behat

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
# Verify everything works (PHPStan first, then runtime tests)
vendor/bin/phpstan analyse && vendor/bin/phpunit
yarn unit
yarn integration
```

### After Each Major Migration
```bash
# Complete PHP tests (PHPStan first, then runtime tests)
vendor/bin/phpstan analyse && vendor/bin/phpunit && vendor/bin/behat

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

## Git Flow Commands

### Phase 2: PHP 8.1 → 8.4
```bash
# Create Phase 2 branch from develop
git checkout develop
git pull origin develop
git checkout -b feature/upgrade-2026-01-php-8.4

# Commit after each smallest successful change (atomic commits)
# Step 1: Run validations
vendor/bin/phpstan analyse && vendor/bin/phpunit && vendor/bin/behat

# Step 2: If all pass, commit with Conventional Commits format
git add src/
git commit -m "feat(php): apply PHP_83 rule for typed constants

Apply Rector PHP_83 rule to add typed class constants.
All validations passing: PHPStan → PHPUnit → Behat"

# If validations fail, fix issues one at a time:
# Fix PHPStan error
git add src/FixedFile.php
git commit -m "fix(php): resolve PHPStan error after PHP_83 rule

Fix type hint issue detected by PHPStan."

# Fix test failure
git add tests/Unit/SomeTest.php
git commit -m "fix(test): update test for PHP_83 typed constants

Update test expectations for new typed constant behavior."

# When phase is complete, merge to develop
git checkout develop
git merge feature/upgrade-2026-01-php-8.4
git branch -d feature/upgrade-2026-01-php-8.4
```

### Phase 5: Symfony 5.4 → 8.0
```bash
# Create Phase 5 branch from develop (after Phase 2 merge)
git checkout develop
git pull origin develop
git checkout -b feature/upgrade-2026-01-symfony-8.0

# Commit after each smallest successful change (atomic commits)
# Example: Migrating Symfony 6.0 → 6.4
# Step 1: Update composer.json
git add composer.json
git commit -m "chore(deps): update Symfony components to 6.4

Update all Symfony components to version 6.4 in composer.json."

# Step 2: Update composer.lock
composer update symfony/* --with-all-dependencies
git add composer.lock
git commit -m "chore(deps): update composer.lock for Symfony 6.4

Regenerate composer.lock with Symfony 6.4 dependencies."

# Step 3: Apply Rector rules (if any)
vendor/bin/rector process --set=SYMFONY_64
# Fix issues one by one, commit each fix separately
git add src/
git commit -m "feat(symfony): apply Symfony 6.4 migration rules

Apply Rector Symfony 6.4 migration rules.
All validations passing: PHPStan → PHPUnit → Behat"

# When phase is complete, merge to develop
git checkout develop
git merge feature/upgrade-2026-01-symfony-8.0
git branch -d feature/upgrade-2026-01-symfony-8.0
```

### Phase 6: PHP 8.4 → 8.5
```bash
# Create Phase 6 branch from develop (after Phase 5 merge)
git checkout develop
git pull origin develop
git checkout -b feature/upgrade-2026-01-php-8.5

# Commit after each smallest successful change (atomic commits)
# Example: Applying PHP_85 rule
vendor/bin/rector process --set=PHP_85 --dry-run
# Review changes

vendor/bin/rector process --set=PHP_85
vendor/bin/phpstan analyse && vendor/bin/phpunit && vendor/bin/behat

# If all pass, commit
git add src/
git commit -m "feat(php): apply PHP_85 rule

Apply Rector PHP_85 rule.
All validations passing: PHPStan → PHPUnit → Behat"

# When phase is complete, merge to develop
git checkout develop
git merge feature/upgrade-2026-01-php-8.5
git branch -d feature/upgrade-2026-01-php-8.5
```

### General Git Commands
```bash
# View current branch
git branch

# View changes
git diff

# View history
git log --oneline

# View commits for current branch
git log --oneline develop..feature/upgrade-2026-01-php-8.4

# Check if branch is merged
git branch --merged develop
```

## Important Notes

1. **Always do a dry-run before applying**
2. **Always run tests after each change**
3. **Document in appropriate tracking files**
4. **Make frequent commits**
5. **Don't hesitate to rollback if necessary**
