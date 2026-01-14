# Git Flow Strategy for Migration

Date: 2026-01-XX
Project: Akeneo PIM Community Dev

## Overview

This migration uses **Git Flow** with **one branch per phase**. Each phase is developed in its own feature branch, merged to `develop` when complete, before starting the next phase.

## Branch Naming Convention

Following Git Flow conventions, we use `feature/` prefix for all migration branches:

- **Phase 2 (PHP 8.1 → 8.4)**: `feature/upgrade-2026-01-php-8.4`
- **Phase 5 (Symfony 5.4 → 8.0)**: `feature/upgrade-2026-01-symfony-8.0`
- **Phase 6 (PHP 8.4 → 8.5)**: `feature/upgrade-2026-01-php-8.5`
- **Optional - TypeScript**: `feature/upgrade-2026-01-typescript-5.6`
- **Optional - React**: `feature/upgrade-2026-01-react-19`
- **Optional - Tools**: `feature/upgrade-2026-01-tools`

## Branch Workflow

### Phase 2: PHP 8.1 → 8.4

```bash
# 1. Start from develop
git checkout develop
git pull origin develop

# 2. Create Phase 2 branch
git checkout -b feature/upgrade-2026-01-php-8.4

# 3. Work on migration (commit frequently)
git add .
git commit -m "feat(upgrade): apply PHP_82 rule"
git commit -m "feat(upgrade): apply PHP_83 rule"
# ... continue with migration steps

# 4. When phase is complete, create pull request
# (via GitHub/GitLab UI or CLI)

# 5. After code review and approval, merge to develop
git checkout develop
git pull origin develop
git merge feature/upgrade-2026-01-php-8.4
git push origin develop

# 6. Delete local branch
git branch -d feature/upgrade-2026-01-php-8.4

# 7. Delete remote branch (if exists)
git push origin --delete feature/upgrade-2026-01-php-8.4
```

### Phase 5: Symfony 5.4 → 8.0

```bash
# 1. Verify Phase 2 is merged
git checkout develop
git log --oneline | grep "feature/upgrade-2026-01-php-8.4"

# 2. Pull latest develop
git pull origin develop

# 3. Create Phase 5 branch
git checkout -b feature/upgrade-2026-01-symfony-8.0

# 4. Work on migration (commit frequently)
git add .
git commit -m "feat(upgrade): migrate Symfony 5.4 → 6.0"
# ... continue with migration steps

# 5. When phase is complete, create pull request
# 6. After code review and approval, merge to develop
git checkout develop
git merge feature/upgrade-2026-01-symfony-8.0
git push origin develop

# 7. Delete branches
git branch -d feature/upgrade-2026-01-symfony-8.0
git push origin --delete feature/upgrade-2026-01-symfony-8.0
```

### Phase 6: PHP 8.4 → 8.5

```bash
# 1. Verify Phase 5 is merged
git checkout develop
git log --oneline | grep "feature/upgrade-2026-01-symfony-8.0"

# 2. Pull latest develop
git pull origin develop

# 3. Create Phase 6 branch
git checkout -b feature/upgrade-2026-01-php-8.5

# 4. Work on migration (commit frequently)
git add .
git commit -m "feat(upgrade): apply PHP_85 rule"

# 5. When phase is complete, create pull request
# 6. After code review and approval, merge to develop
git checkout develop
git merge feature/upgrade-2026-01-php-8.5
git push origin develop

# 7. Delete branches
git branch -d feature/upgrade-2026-01-php-8.5
git push origin --delete feature/upgrade-2026-01-php-8.5
```

## Commit Message Convention

**CRITICAL**: Follow [Conventional Commits](https://www.conventionalcommits.org/en/v1.0.0/) specification strictly.

### Commit Strategy: Atomic Commits

**Principle**: Each commit should be the smallest possible change that:
- ✅ Passes all tests (PHPStan → PHPUnit → Behat)
- ✅ Passes static analysis (PHPStan)
- ✅ Meets coding standards (PHP-CS-Fixer)
- ✅ Can be deployed to production without errors
- ✅ Contains only one logical change

### Commit Rules

1. **One change per commit**: Each commit should address a single concern
2. **Multiple files OK**: If files are logically related to the same change, they can be in one commit
3. **Test fixes separate**: If multiple tests need fixing, create one commit per test fix
4. **Never commit broken code**: Each commit must pass all validations
5. **Deployable commits**: Each commit should be production-ready

### Commit Message Format

```
<type>[optional scope]: <description>

[optional body]

[optional footer(s)]
```

### Commit Types

- **`feat`**: New feature (e.g., applying a Rector rule, adding migration step)
- **`fix`**: Bug fix (e.g., fixing compatibility issue, correcting test)
- **`test`**: Adding or updating tests
- **`refactor`**: Code refactoring without changing functionality
- **`chore`**: Maintenance tasks (e.g., updating dependencies, configuration)
- **`docs`**: Documentation changes
- **`style`**: Code style changes (formatting, whitespace)
- **`perf`**: Performance improvements

### Scope Examples

- `feat(php)`: PHP version migration
- `feat(symfony)`: Symfony version migration
- `feat(react)`: React version migration
- `feat(typescript)`: TypeScript version migration
- `fix(compatibility)`: Compatibility fixes
- `test(phpunit)`: PHPUnit test updates

### Commit Examples

#### Good: Single Rector Rule Application
```bash
# Apply one Rector rule, fix resulting issues, commit
git add src/
git commit -m "feat(php): apply PHP_82 rule for readonly properties

Apply Rector PHP_82 rule to convert properties to readonly where applicable.
All tests passing: PHPStan → PHPUnit → Behat"
```

#### Good: Single Test Fix
```bash
# Fix one failing test, commit
git add tests/Unit/SomeTest.php
git commit -m "fix(test): update test expectation for PHP 8.2

Update test to match new readonly property behavior.
Test now passes with PHP 8.2."
```

#### Good: Multiple Related Files (Same Change)
```bash
# Update related configuration files for same change
git add composer.json composer.lock
git commit -m "chore(deps): update Symfony components to 6.0

Update all Symfony components to version 6.0.
Composer dependencies updated and locked."
```

#### Bad: Multiple Unrelated Changes
```bash
# DON'T DO THIS - mixing unrelated changes
git add src/Controller.php tests/Unit/ControllerTest.php composer.json
git commit -m "feat: various updates"
```

#### Bad: Committing Broken Code
```bash
# DON'T DO THIS - commit doesn't pass tests
git add src/
git commit -m "feat(php): apply PHP_83 rule"
# Tests are failing - this commit should not exist
```

### Commit Workflow

1. **Make smallest change possible**
2. **Run validations**: `vendor/bin/phpstan analyse && vendor/bin/phpunit && vendor/bin/behat`
3. **If validations pass**: Commit with descriptive message
4. **If validations fail**: Fix issues, then commit (one fix per commit)
5. **Repeat** for next smallest change

### Examples for Migration Steps

#### Applying Rector Rule
```bash
# Step 1: Apply Rector rule
vendor/bin/rector process --set=PHP_82 --dry-run
# Review changes

# Step 2: Apply rule
vendor/bin/rector process --set=PHP_82

# Step 3: Fix any issues (one commit per fix)
# If PHPStan fails:
vendor/bin/phpstan analyse
# Fix PHPStan errors
git add src/FixedFile.php
git commit -m "fix(php): resolve PHPStan errors after PHP_82 rule

Fix type hints and nullability issues detected by PHPStan."

# Step 4: Run tests
vendor/bin/phpstan analyse && vendor/bin/phpunit && vendor/bin/behat

# Step 5: Commit Rector changes (if all pass)
git add src/
git commit -m "feat(php): apply PHP_82 readonly properties rule

Apply Rector PHP_82 rule to convert properties to readonly.
All validations passing: PHPStan → PHPUnit → Behat."
```

#### Fixing Multiple Tests
```bash
# Test 1 fails - fix and commit
git add tests/Unit/Test1.php
git commit -m "fix(test): update Test1 for PHP 8.2 compatibility

Update test expectations for readonly properties."

# Test 2 fails - fix and commit separately
git add tests/Unit/Test2.php
git commit -m "fix(test): update Test2 for PHP 8.2 compatibility

Update test mock setup for readonly properties."
```

#### Updating Dependencies
```bash
# Update composer.json
git add composer.json
git commit -m "chore(deps): require PHP 8.2

Update PHP requirement to 8.2.* in composer.json."

# Update composer.lock (separate commit)
composer update --lock
git add composer.lock
git commit -m "chore(deps): update composer.lock for PHP 8.2

Regenerate composer.lock with PHP 8.2 requirement."
```

## Pull Request Strategy

### For Each Phase Branch

1. **Create PR** when phase is complete and tested
2. **Title format**: `feat(upgrade): [Phase X] [Description]`
   - Example: `feat(upgrade): Phase 2 - PHP 8.1 → 8.4 migration`
3. **Description should include**:
   - Phase number and description
   - List of major changes
   - Test results
   - Link to tracking file
   - Prerequisites verification
4. **Review requirements**:
   - Code review approval
   - All tests passing
   - Documentation updated
5. **Merge strategy**: Merge to `develop` (not squash, to preserve history)

## Branch Dependencies

```
develop
  │
  ├── feature/upgrade-2026-01-php-8.4 (Phase 2)
  │     │
  │     └── (merge to develop)
  │
  ├── feature/upgrade-2026-01-symfony-8.0 (Phase 5)
  │     │ (created from develop after Phase 2 merge)
  │     │
  │     └── (merge to develop)
  │
  └── feature/upgrade-2026-01-php-8.5 (Phase 6)
        │ (created from develop after Phase 5 merge)
        │
        └── (merge to develop)
```

## Parallel Migrations

TypeScript and React migrations can be done:
- **Option 1**: In the Phase 2 branch (`feature/upgrade-2026-01-php-8.4`)
- **Option 2**: In separate branches that can be merged independently

If using separate branches:
```bash
# TypeScript branch
git checkout develop
git checkout -b feature/upgrade-2026-01-typescript-5.6
# ... work on migration
# Merge to develop independently

# React branch
git checkout develop
git checkout -b feature/upgrade-2026-01-react-19
# ... work on migration
# Merge to develop independently
```

## Verification Commands

### Check Current Branch
```bash
git branch
# or
git rev-parse --abbrev-ref HEAD
```

### Verify Phase 2 is Merged
```bash
git checkout develop
git log --oneline --grep="feature/upgrade-2026-01-php-8.4"
# or
git log --oneline develop | grep "PHP 8.1 → 8.4"
```

### Verify Phase 5 is Merged
```bash
git checkout develop
git log --oneline --grep="feature/upgrade-2026-01-symfony-8.0"
# or
git log --oneline develop | grep "Symfony 5.4 → 8.0"
```

### List All Migration Branches
```bash
git branch -a | grep "upgrade-2026-01"
```

### Check if Branch is Merged
```bash
git branch --merged develop | grep "upgrade-2026-01"
```

## Important Rules

1. **Always start from develop** when creating a new phase branch
2. **Always pull latest develop** before creating branch
3. **Never skip phases** - merge order must be respected
4. **Verify previous phase merge** before creating next phase branch
5. **Commit frequently** after each successful step
6. **Create pull requests** for code review
7. **Merge to develop** only after approval and tests passing
8. **Delete branches** after merge (local and remote)
9. **Document branch creation/merge** in tracking files

## Troubleshooting

### If you're on wrong branch
```bash
# Check current branch
git branch

# Switch to correct branch or create it
git checkout develop
git checkout -b feature/upgrade-2026-01-php-8.4
```

### If previous phase not merged
```bash
# Check if Phase 2 is merged
git log develop --oneline | grep "PHP 8.1 → 8.4"

# If not merged, don't start Phase 5
# Complete Phase 2 first
```

### If you need to resume work
```bash
# Check which branches exist
git branch -a | grep "upgrade-2026-01"

# Switch to appropriate branch
git checkout feature/upgrade-2026-01-php-8.4
```

## Best Practices

1. **One phase per branch** - keep branches focused
2. **Small, frequent commits** - easier to review and rollback
3. **Clear commit messages** - use conventional commits format
4. **Test before merging** - ensure all tests pass
5. **Document in tracking files** - update tracking files with branch info
6. **Communicate** - inform team when phase is ready for review
7. **Use pull requests** - never merge directly to develop
8. **Clean up** - delete merged branches to keep repository clean
