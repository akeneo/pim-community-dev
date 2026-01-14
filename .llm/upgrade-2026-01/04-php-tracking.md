# PHP Migration Tracking 8.1 → 8.4 → 8.5

**⚠️ IMPORTANT**: This migration is split into 2 phases with separate Git Flow branches:
- **Phase 2**: PHP 8.1 → 8.4 (MUST complete before Symfony 8.0)
  - **Git branch**: `feature/upgrade-2026-01-php-8.4`
- **Phase 6**: PHP 8.4 → 8.5 (MUST be done after Symfony 8.0 is stable)
  - **Git branch**: `feature/upgrade-2026-01-php-8.5`

Start date: 2026-01-14
End date: [To be completed]

## Git Flow Branch Information

### Phase 2 Branch: feature/upgrade-2026-01-php-8.4
- [x] Branch created: 2026-01-14 10:56:33 CET
- [ ] Branch merged to master: [To be completed]
- [ ] Merge date: [To be completed]
- [ ] Pull request URL: [To be completed]

### Phase 6 Branch: feature/upgrade-2026-01-php-8.5
- [ ] Branch created: [To be completed]
- [ ] Branch merged to develop: [To be completed]
- [ ] Merge date: [To be completed]
- [ ] Pull request URL: [To be completed]

## Current State
- Required PHP version: 8.1.* (from composer.json)
- Target PHP version Phase 2: 8.4.* (required for Symfony 8.0)
- Target PHP version Phase 6: 8.5.* (latest stable version, after Symfony 8.0)
- **Note**: Docker stack is used - system PHP version is irrelevant

## Applied Rector Rules

### Rule 1: PHP_82 - PHP 8.2
- [ ] Application date: [To be completed]
- [ ] Modified files: [To be completed]
- [ ] Tests executed: [To be completed]
- [ ] Test results: [To be completed]
- [ ] Issues encountered: [To be completed]
- [ ] Solutions applied: [To be completed]

### Rule 2: PHP_83 - Typed class constants
- [ ] Application date: [To be completed]
- [ ] Modified files: [To be completed]
- [ ] Tests executed: [To be completed]
- [ ] Test results: [To be completed]
- [ ] Issues encountered: [To be completed]
- [ ] Solutions applied: [To be completed]

### Rule 3: PHP_84 - PHP 8.4 (if available)
- [ ] Application date: [To be completed]
- [ ] Modified files: [To be completed]
- [ ] Tests executed: [To be completed]
- [ ] Test results: [To be completed]
- [ ] Issues encountered: [To be completed]
- [ ] Solutions applied: [To be completed]

## Phase 2: PHP 8.4 → 8.5 Migration (AFTER Symfony 8.0)

**⚠️ CRITICAL**: This phase can ONLY be started after Symfony 8.0 migration (Phase 5) is completed and stable.

### Prerequisites Check
- [ ] Symfony 8.0 migration completed: [To be completed]
- [ ] Symfony 8.0 is stable: [To be completed]
- [ ] All Symfony 8.0 tests passing: [To be completed]
- [ ] Ready to proceed to PHP 8.5: [Yes/No]

### Rule: PHP_85 - PHP 8.5 (if available)
- [ ] Application date: [To be completed]
- [ ] Modified files: [To be completed]
- [ ] Tests executed: [To be completed]
- [ ] Test results: [To be completed]
- [ ] Symfony 8.0 compatibility verified: [To be completed]
- [ ] Issues encountered: [To be completed]
- [ ] Solutions applied: [To be completed]

### Rule 5: TypedPropertyRector
- [ ] Application date: [To be completed]
- [ ] Modified files: [To be completed]
- [ ] Tests executed: [To be completed]
- [ ] Test results: [To be completed]
- [ ] Issues encountered: [To be completed]
- [ ] Solutions applied: [To be completed]

### Rule 6: ReadOnlyPropertyRector
- [ ] Application date: [To be completed]
- [ ] Modified files: [To be completed]
- [ ] Tests executed: [To be completed]
- [ ] Test results: [To be completed]
- [ ] Issues encountered: [To be completed]
- [ ] Solutions applied: [To be completed]

### Rule 7: OverrideAttributeRector
- [ ] Application date: [To be completed]
- [ ] Modified files: [To be completed]
- [ ] Tests executed: [To be completed]
- [ ] Test results: [To be completed]
- [ ] Issues encountered: [To be completed]
- [ ] Solutions applied: [To be completed]

## Dependency Updates

### Updated Dependencies
- [ ] List of updated dependencies: [To be completed]
- [ ] Identified breaking changes: [To be completed]
- [ ] Required adaptations: [To be completed]

## Tests

### PHPStan (Static Analysis - Execute First)
- [ ] Date: [To be completed]
- [ ] Result: [To be completed]
- [ ] Errors: [To be completed]

### PHPUnit (Unit Tests - Execute After PHPStan)
- [ ] Date: [To be completed]
- [ ] Result: [To be completed]
- [ ] Errors: [To be completed]

### Behat
- [ ] Date: [To be completed]
- [ ] Result: [To be completed]
- [ ] Errors: [To be completed]

### PHPStan
- [ ] Date: [To be completed]
- [ ] Result: [To be completed]
- [ ] Errors: [To be completed]

### PHP-CS-Fixer
- [ ] Date: [To be completed]
- [ ] Result: [To be completed]
- [ ] Errors: [To be completed]

## Issues Encountered

### Issue 1: [Title]
- Description: [To be completed]
- Solution: [To be completed]
- Resolution date: [To be completed]

## Notes
[Additional notes]
