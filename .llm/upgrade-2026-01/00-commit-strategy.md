# Atomic Commit Strategy with Conventional Commits

Date: 2026-01-XX
Project: Akeneo PIM Community Dev

## Overview

This document describes the commit strategy for the migration project, following [Conventional Commits](https://www.conventionalcommits.org/en/v1.0.0/) specification and atomic commit principles.

## Core Principles

1. **Atomic Commits**: Each commit should be the smallest possible change
2. **Production-Ready**: Each commit should be deployable without errors
3. **Validation Required**: All validations must pass before committing
4. **One Change Per Commit**: Each commit addresses a single concern
5. **Conventional Commits**: Follow the specification strictly

## Validation Order (MUST Pass Before Commit)

Before committing, run validations in this order:

```bash
# 1. Static analysis (fastest, catches errors early)
vendor/bin/phpstan analyse

# 2. Unit tests (if PHPStan passes)
vendor/bin/phpunit

# 3. Functional tests (if PHPUnit passes)
vendor/bin/behat
```

**All three must pass** before committing.

## Commit Workflow

### Step-by-Step Process

1. **Make smallest change possible**
   - Apply one Rector rule
   - Fix one PHPStan error
   - Update one test
   - Update one dependency

2. **Run validations**
   ```bash
   vendor/bin/phpstan analyse && vendor/bin/phpunit && vendor/bin/behat
   ```

3. **If validations pass**
   - Stage changed files: `git add [files]`
   - Commit with Conventional Commits format
   - Continue to next change

4. **If validations fail**
   - Fix issues one at a time
   - Commit each fix separately
   - Re-run validations after each fix

## Commit Message Format

Follow [Conventional Commits](https://www.conventionalcommits.org/en/v1.0.0/) specification:

```
<type>[optional scope]: <description>

[optional body]

[optional footer(s)]
```

### Required Elements

- **Type**: `feat`, `fix`, `test`, `refactor`, `chore`, `docs`, `style`, `perf`
- **Description**: Short summary (imperative mood, lowercase)
- **Body** (optional): Detailed explanation
- **Footer** (optional): Breaking changes, issue references

### Commit Types

| Type | Use Case | Example |
|------|----------|---------|
| `feat` | New feature | Applying Rector rule, adding migration step |
| `fix` | Bug fix | Fixing compatibility issue, correcting error |
| `test` | Test changes | Adding/updating tests |
| `refactor` | Code refactoring | Restructuring without changing behavior |
| `chore` | Maintenance | Updating dependencies, configuration |
| `docs` | Documentation | Updating docs |
| `style` | Formatting | Code style changes (PHP-CS-Fixer) |
| `perf` | Performance | Performance improvements |

### Scope Examples

- `feat(php)`: PHP version migration
- `feat(symfony)`: Symfony version migration
- `feat(react)`: React version migration
- `feat(typescript)`: TypeScript version migration
- `fix(compatibility)`: Compatibility fixes
- `fix(test)`: Test fixes
- `chore(deps)`: Dependency updates

## Examples

### ✅ Good: Single Rector Rule

```bash
# Apply Rector rule
vendor/bin/rector process --set=PHP_82

# Run validations
vendor/bin/phpstan analyse && vendor/bin/phpunit && vendor/bin/behat

# If all pass, commit
git add src/
git commit -m "feat(php): apply PHP_82 readonly properties rule

Apply Rector PHP_82 rule to convert properties to readonly where applicable.
All validations passing: PHPStan → PHPUnit → Behat"
```

### ✅ Good: Single PHPStan Fix

```bash
# PHPStan reports error
vendor/bin/phpstan analyse
# Error: Property $foo should be readonly

# Fix the error
# Edit src/SomeClass.php: add readonly keyword

# Run validations
vendor/bin/phpstan analyse && vendor/bin/phpunit && vendor/bin/behat

# Commit fix
git add src/SomeClass.php
git commit -m "fix(php): add readonly keyword to property

Resolve PHPStan error by making property readonly as per PHP 8.2 best practices."
```

### ✅ Good: Single Test Fix

```bash
# Test fails after Rector rule
vendor/bin/phpunit
# Test: testSomething() fails

# Fix the test
# Edit tests/Unit/SomeTest.php: update expectations

# Run validations
vendor/bin/phpstan analyse && vendor/bin/phpunit && vendor/bin/behat

# Commit fix
git add tests/Unit/SomeTest.php
git commit -m "fix(test): update test expectations for readonly properties

Update test to match new readonly property behavior after PHP_82 rule application."
```

### ✅ Good: Multiple Related Files (Same Change)

```bash
# Update composer.json and composer.lock together (logically related)
git add composer.json composer.lock
git commit -m "chore(deps): update Symfony components to 6.0

Update all Symfony components to version 6.0.
Composer dependencies updated and locked."
```

### ❌ Bad: Multiple Unrelated Changes

```bash
# DON'T DO THIS - mixing unrelated changes
git add src/Controller.php tests/Unit/ControllerTest.php composer.json
git commit -m "feat: various updates"
```

**Why it's bad**: 
- Hard to review
- Hard to revert specific changes
- Violates atomic commit principle

**Correct approach**: Commit each change separately

### ❌ Bad: Committing Broken Code

```bash
# DON'T DO THIS - commit doesn't pass validations
vendor/bin/rector process --set=PHP_82
git add src/
git commit -m "feat(php): apply PHP_82 rule"
# PHPStan fails, tests fail - this commit should not exist
```

**Why it's bad**:
- Breaks CI/CD pipeline
- Cannot be deployed
- Violates production-ready principle

**Correct approach**: Fix issues first, then commit

### ❌ Bad: Multiple Test Fixes in One Commit

```bash
# DON'T DO THIS - multiple test fixes in one commit
# Fix Test1.php
# Fix Test2.php
# Fix Test3.php
git add tests/
git commit -m "fix(test): update all tests"
```

**Why it's bad**:
- Hard to review each fix
- Hard to revert specific test fix
- Violates atomic commit principle

**Correct approach**: Fix and commit each test separately

## Common Scenarios

### Scenario 1: Applying Rector Rule with Fixes Needed

```bash
# Step 1: Apply Rector rule
vendor/bin/rector process --set=PHP_82

# Step 2: Run validations
vendor/bin/phpstan analyse && vendor/bin/phpunit && vendor/bin/behat
# PHPStan fails with 3 errors
# PHPUnit fails with 2 tests

# Step 3: Fix PHPStan errors one by one
# Fix error 1
git add src/File1.php
git commit -m "fix(php): resolve PHPStan error in File1

Add type hint to resolve PHPStan error after PHP_82 rule."

# Fix error 2
git add src/File2.php
git commit -m "fix(php): resolve PHPStan error in File2

Add nullability check to resolve PHPStan error."

# Fix error 3
git add src/File3.php
git commit -m "fix(php): resolve PHPStan error in File3

Update property access to resolve PHPStan error."

# Step 4: Fix tests one by one
# Fix test 1
git add tests/Unit/Test1.php
git commit -m "fix(test): update Test1 for readonly properties

Update test expectations for readonly property behavior."

# Fix test 2
git add tests/Unit/Test2.php
git commit -m "fix(test): update Test2 for readonly properties

Update test mock setup for readonly properties."

# Step 5: Commit Rector changes (now all validations pass)
git add src/
git commit -m "feat(php): apply PHP_82 readonly properties rule

Apply Rector PHP_82 rule to convert properties to readonly.
All validations passing: PHPStan → PHPUnit → Behat"
```

### Scenario 2: Updating Dependencies

```bash
# Step 1: Update composer.json
# Edit composer.json: change "symfony/symfony": "^5.4" to "^6.0"
git add composer.json
git commit -m "chore(deps): require Symfony 6.0

Update Symfony requirement to 6.0 in composer.json."

# Step 2: Update composer.lock
composer update symfony/* --with-all-dependencies
git add composer.lock
git commit -m "chore(deps): update composer.lock for Symfony 6.0

Regenerate composer.lock with Symfony 6.0 dependencies."

# Step 3: Run validations
vendor/bin/phpstan analyse && vendor/bin/phpunit && vendor/bin/behat
# If any fail, fix and commit separately
```

### Scenario 3: Multiple Files for Same Logical Change

```bash
# Updating related configuration files
git add config/packages/framework.yaml config/packages/security.yaml
git commit -m "feat(symfony): update framework and security configs for Symfony 6.0

Update framework and security bundle configurations for Symfony 6.0 compatibility.
Both files updated together as they're part of the same migration step."
```

## Validation Checklist

Before each commit, verify:

- [ ] **PHPStan passes**: `vendor/bin/phpstan analyse`
- [ ] **PHPUnit passes**: `vendor/bin/phpunit`
- [ ] **Behat passes**: `vendor/bin/behat`
- [ ] **Commit message follows Conventional Commits**: Type, description, optional body
- [ ] **Only related files**: Files in commit are logically related
- [ ] **Smallest change**: Commit contains only one logical change
- [ ] **Production-ready**: Commit can be deployed without errors

## Benefits

1. **Easy Review**: Small, focused commits are easier to review
2. **Easy Revert**: Can revert specific changes without affecting others
3. **Clear History**: Git history shows exactly what changed and why
4. **CI/CD Friendly**: Each commit can be validated independently
5. **Production Safety**: Each commit is deployable
6. **Debugging**: Easy to identify which commit introduced an issue

## Anti-Patterns to Avoid

1. ❌ **"WIP" commits**: Don't commit work in progress
2. ❌ **"Fix everything" commits**: Don't fix multiple unrelated issues
3. ❌ **"Update all tests" commits**: Fix tests one at a time
4. ❌ **Committing without validation**: Always run validations first
5. ❌ **Large commits**: Break down into smaller, logical commits
6. ❌ **Mixed concerns**: Don't mix refactoring with feature changes

## References

- [Conventional Commits Specification](https://www.conventionalcommits.org/en/v1.0.0/)
- `00-git-flow-strategy.md` - Git Flow branch strategy
- `COMMANDS.md` - Command reference with commit examples
