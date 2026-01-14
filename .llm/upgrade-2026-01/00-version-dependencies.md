# Version Dependencies Matrix

Date: 2026-01-XX
Project: Akeneo PIM Community Dev

## Critical Version Dependencies

This document outlines all version dependencies that must be respected during the migration.

## PHP and Symfony Dependencies

| Symfony Version | Minimum PHP Required | Maximum PHP Supported | Notes |
|----------------|----------------------|------------------------|-------|
| 5.4 | 7.2.5+ | 8.3.x | Current version |
| 6.0 | 8.1.0+ | 8.5.x | Requires PHP 8.1+ |
| 6.4 | 8.1.0+ | 8.5.x | LTS version, requires PHP 8.1+ |
| 7.0 | 8.2.0+ | 8.5.x | Requires PHP 8.2+ |
| 8.0 | **8.4.0+** | 8.5.x | **Requires PHP 8.4.0+** |

## Migration Phases Based on Dependencies

### Phase 2: PHP 8.1 → 8.4 (BEFORE Symfony 8.0)
**Goal**: Reach PHP 8.4.0+ required for Symfony 8.0
**Git Flow Branch**: `feature/upgrade-2026-01-php-8.4`

**Steps**:
1. PHP 8.1 → 8.2 (no Symfony dependency)
2. PHP 8.2 → 8.3 (no Symfony dependency)
3. PHP 8.3 → 8.4 (no Symfony dependency)

**Prerequisites**: None
**Can be done in parallel with**: TypeScript, React migrations
**Branch workflow**: Create from `develop`, merge to `develop` when complete

**⚠️ CRITICAL**: Must complete PHP 8.4 and merge branch to develop before starting Symfony 8.0 migration

### Phase 5: Symfony 5.4 → 8.0 (REQUIRES PHP 8.4+)
**Goal**: Migrate to Symfony 8.0
**Git Flow Branch**: `feature/upgrade-2026-01-symfony-8.0`

**Steps**:
1. Symfony 5.4 → 6.0 (requires PHP 8.1+)
2. Symfony 6.0 → 6.4 (requires PHP 8.1+)
3. Symfony 6.4 → 7.0 (requires PHP 8.2+)
4. Symfony 7.0 → 8.0 (requires PHP 8.4.0+)

**Prerequisites**: 
- PHP 8.4.0+ MUST be completed (Phase 2)
- Phase 2 branch MUST be merged to develop
**Can be done in parallel with**: TypeScript, React migrations (if not already done)
**Branch workflow**: Create from `develop` (after Phase 2 merge), merge to `develop` when complete

**⚠️ CRITICAL**: Cannot start until PHP 8.4.0+ is verified and Phase 2 branch is merged

### Phase 6: PHP 8.4 → 8.5 (AFTER Symfony 8.0)
**Goal**: Migrate to PHP 8.5
**Git Flow Branch**: `feature/upgrade-2026-01-php-8.5`

**Steps**:
1. PHP 8.4 → 8.5

**Prerequisites**: 
- Symfony 8.0 MUST be completed and stable (Phase 5)
- Phase 5 branch MUST be merged to develop
**Can be done in parallel with**: Development tools migration
**Branch workflow**: Create from `develop` (after Phase 5 merge), merge to `develop` when complete

**⚠️ CRITICAL**: Must wait until Symfony 8.0 is stable and Phase 5 branch is merged

## Other Dependencies

### PHPUnit
- **PHPUnit 9**: Requires PHP 7.3+
- **PHPUnit 10**: Requires PHP 8.1+
- **Migration**: Can be done after PHP 8.1+ is reached

### React/TypeScript
- **No PHP dependency**: Can be migrated at any time
- **Can be done in parallel** with PHP/Symfony migrations
- **Recommended**: Do during Phase 1 (PHP 8.1 → 8.4) to save time

### Development Tools
- **Jest**: No PHP dependency
- **ESLint**: No PHP dependency
- **Prettier**: No PHP dependency
- **Can be migrated at any time** after main migrations

## Dependency Graph

```
PHP 8.1 (current)
  ↓
PHP 8.2
  ↓
PHP 8.3
  ↓
PHP 8.4 ──────┐
  │            │
  │            ↓
  │      Symfony 6.0 (requires PHP 8.1+)
  │            ↓
  │      Symfony 6.4 (requires PHP 8.1+)
  │            ↓
  │      Symfony 7.0 (requires PHP 8.2+)
  │            ↓
  │      Symfony 8.0 (requires PHP 8.4.0+)
  │            │
  │            ↓
  └────────────┘
         ↓
    PHP 8.5 (requires Symfony 8.0 to be stable)
```

## Verification Checklist

Before starting each phase, verify:

### Before Phase 2 (PHP 8.1 → 8.4)
- [ ] Current PHP version in `composer.json`: 8.1.*
- [ ] On `develop` branch: `git checkout develop`
- [ ] Docker stack is running
- [ ] All tests passing
- [ ] Create branch: `git checkout -b feature/upgrade-2026-01-php-8.4`

### Before Phase 5 (Symfony 5.4 → 8.0)
- [ ] PHP version in `composer.json`: ^8.4
- [ ] PHP 8.4 migration completed
- [ ] All PHP 8.4 tests passing
- [ ] Phase 2 branch merged to develop: `git log develop | grep "feature/upgrade-2026-01-php-8.4"`
- [ ] On `develop` branch: `git checkout develop && git pull`
- [ ] Docker stack configured for PHP 8.4+
- [ ] Create branch: `git checkout -b feature/upgrade-2026-01-symfony-8.0`

### Before Phase 6 (PHP 8.4 → 8.5)
- [ ] Symfony 8.0 migration completed
- [ ] Symfony 8.0 is stable
- [ ] All Symfony 8.0 tests passing
- [ ] Phase 5 branch merged to develop: `git log develop | grep "feature/upgrade-2026-01-symfony-8.0"`
- [ ] On `develop` branch: `git checkout develop && git pull`
- [ ] No critical issues with Symfony 8.0
- [ ] Create branch: `git checkout -b feature/upgrade-2026-01-php-8.5`

## Important Notes

1. **Never skip phases** - the order is critical
2. **Use Git Flow branches** - one branch per phase:
   - Phase 2: `feature/upgrade-2026-01-php-8.4`
   - Phase 5: `feature/upgrade-2026-01-symfony-8.0`
   - Phase 6: `feature/upgrade-2026-01-php-8.5`
3. **Merge to develop** - each phase branch must be merged to develop before starting next phase
4. **Always verify prerequisites** before starting a phase (including previous phase merge)
5. **Use Docker** - system PHP versions are irrelevant
6. **Check composer.json** - this is the source of truth for PHP version
7. **Test thoroughly** after each phase before merging to develop
8. **Create pull requests** for code review before merging each phase branch
