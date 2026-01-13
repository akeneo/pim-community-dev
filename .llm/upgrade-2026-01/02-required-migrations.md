# Required Migrations

Date: 2026-01-XX
Project: Akeneo PIM Community Dev

## 1. PHP 8.1 → 8.5 Migration

### Major Changes
- **PHP 8.2**: Readonly classes, null/false/true types, constants in traits
- **PHP 8.3**: Typed class constants, Override attribute, Dynamic class constant fetch
- **PHP 8.4**: PHP 8.4 new features
- **PHP 8.5**: PHP 8.5 new features (published November 2025)
- **Deprecations**: Removal of some deprecated functions

### Rector Rules to Apply (in order)
1. `PHP_82` - PHP 8.2 specific rules
2. `PHP_83` - PHP 8.3 specific rules
3. `PHP_84` - PHP 8.4 specific rules (if available)
4. `PHP_85` - PHP 8.5 specific rules (if available)
5. `TypedPropertyRector` - Property typing
6. `ReadOnlyPropertyRector` - Conversion to readonly properties
7. `OverrideAttributeRector` - Adding Override attribute
8. `DynamicClassConstantFetchRector` - Dynamic constants support

### Tests to Run After Each Rule
- PHPUnit: `vendor/bin/phpunit`
- Behat: `vendor/bin/behat`
- PHPStan: `vendor/bin/phpstan analyse`

## 2. Symfony 5.4 → 8.0 Migration

### Recommended Intermediate Steps
1. **Symfony 5.4 → 6.0** (intermediate step)
2. **Symfony 6.0 → 6.4** (LTS)
3. **Symfony 6.4 → 7.0** (intermediate step)
4. **Symfony 7.0 → 8.0** (latest stable version, published November 2025)
- **Important note**: Symfony 8.0 requires PHP 8.4.0 or higher

### Major Changes Symfony 6.0
- **PHP 8.1 minimum required**
- **Removal of backward compatibility**: No more support for older versions
- **New attributes**: Replacement of annotations with PHP 8 attributes
- **Security**: Changes in security system
- **Form**: Changes in form system
- **Routing**: Routing improvements
- **Messenger**: Message system improvements

### Major Changes Symfony 7.0
- **PHP 8.2 minimum required**
- **New components**: Addition of new components
- **Performance improvements**: Various optimizations
- **New features**: New APIs

### Major Changes Symfony 8.0
- **PHP 8.4.0 minimum required** (critical!)
- **New components**: Addition of new components
- **Performance improvements**: Various optimizations
- **New features**: New APIs
- **Breaking changes**: Check official changelog

### Rector Rules to Apply (in order)
1. `SYMFONY_60` - Migration to Symfony 6.0
   - `SymfonySetList::SYMFONY_60`
   - Tests after each sub-rule
2. `SYMFONY_64` - Migration to Symfony 6.4
   - `SymfonySetList::SYMFONY_64`
   - Tests after each sub-rule
3. `SYMFONY_70` - Migration to Symfony 7.0
   - `SymfonySetList::SYMFONY_70`
   - Tests after each sub-rule
4. `SYMFONY_80` - Migration to Symfony 8.0 (if available)
   - `SymfonySetList::SYMFONY_80`
   - Tests after each sub-rule

### Important Specific Rules
- `ReplaceAnnotationWithAttributeRector` - Replacing annotations with attributes
- `ReplaceServiceSubscriberArgumentInterfaceRector` - Service migration
- `ReplaceStringParameterWithEventClassRector` - Event migration
- `ReplaceRouteAnnotationWithAttributeRector` - Route migration
- `ReplaceDoctrineRepositoryInheritanceRector` - Doctrine migration

### Tests to Run After Each Rule
- PHPUnit: `vendor/bin/phpunit`
- Behat: `vendor/bin/behat`
- PHPStan: `vendor/bin/phpstan analyse`
- PHP-CS-Fixer: `vendor/bin/php-cs-fixer fix --dry-run`

## 3. React 17 → 19 Migration

### Major Changes React 18
- **Concurrent Rendering**: New rendering system
- **Automatic Batching**: Automatic batching of updates
- **New Hooks**: useId, useTransition, useDeferredValue
- **Improved Suspense**: Better Suspense support
- **Strict Mode**: Changes in strict mode

### Major Changes React 19
- **Actions**: New action system
- **useFormStatus**: New hook for forms
- **useFormState**: New hook for form state
- **useOptimistic**: New hook for optimism
- **Refs as props**: Refs can be passed as props
- **Removal of some APIs**: Some deprecated APIs removed
- **React 19.x**: Latest stable version with fixes and improvements

### React Migration Tools
- **react-codemod**: Official tool for React migrations
- **@react-codemod/transforms**: Available transformations
- **Manual migration**: Some migrations must be done manually

### Transformations to Apply (in order)
1. **react-18-upgrade**: Migration to React 18
   - `npx react-codemod react-18-upgrade`
   - Unit tests after
2. **react-19-upgrade**: Migration to React 19
   - `npx react-codemod react-19-upgrade` (if available)
   - Unit tests after
3. **Manual changes**: Required manual changes
   - Update deprecated hooks
   - Migration to new APIs
   - Unit tests after each change

### Tests to Run After Each Transformation
- Jest unit: `yarn unit`
- Jest integration: `yarn integration`
- E2E Cypress tests: `yarn test:e2e:run`
- Lint: `yarn lint`

## 4. TypeScript 4.0 → 5.6 Migration

### Major Changes TypeScript 5.0
- **New utility types**: Awaited, etc.
- **Type checking improvements**: Better error detection
- **Improved performance**: Faster compilation
- **New configuration options**: New options

### Major Changes TypeScript 5.1-5.6
- **New features**: Features added progressively
- **Type system improvements**: Continuous improvements
- **Minor breaking changes**: Some breaking changes

### TypeScript Migration Tools
- **ts-migrate**: TypeScript migration tool (Facebook)
- **Manual migration**: Manual migration required for some cases

### Migration Steps
1. **TypeScript update**: `yarn add -D typescript@^5.6.0`
2. **Error verification**: `yarn tsc --noEmit`
3. **Progressive correction**: Fix errors one by one
4. **Tests after each correction**: `yarn unit && yarn integration`

### Tests to Run After Each Step
- TypeScript check: `yarn tsc --noEmit`
- Jest unit: `yarn unit`
- Jest integration: `yarn integration`
- Build: `yarn webpack-dev`

## 5. Development Tools Migration

### PHPUnit 9 → 10
- **PHP 8.1 minimum required**
- **New assertions**: New assertion methods
- **Mock changes**: Mock improvements
- **Migration**: `composer require --dev phpunit/phpunit ^10.0`

### Jest 26 → 29+
- **Node.js 18+ required**
- **New APIs**: New features
- **Configuration changes**: New configuration
- **Migration**: `yarn add -D jest@^29.0.0`

### ESLint 6 → 9
- **New rules**: New rules available
- **Configuration changes**: New flat configuration
- **Migration**: `yarn add -D eslint@^9.0.0`

## 6. Third-Party Dependencies Migration

### Backend
- **Doctrine ORM**: Check Symfony 8.0 compatibility
- **Twig**: Check Symfony 8.0 compatibility
- **Monolog**: Check Symfony 8.0 compatibility
- **Other bundles**: Check compatibility individually

### Frontend
- **react-redux**: Check React 19 compatibility
- **react-router-dom**: Check React 19 compatibility
- **react-query**: Check React 19 compatibility
- **styled-components**: Check React 19 compatibility
- **Other libraries**: Check compatibility individually

## 7. Recommended Migration Order (Phased Approach)

Due to version dependencies, migration must follow this exact order:

### Phase 1: PHP 8.1 → 8.4 (MUST complete before Symfony 8.0)
1. **PHP 8.1 → 8.2** (Rector rules)
2. **PHP 8.2 → 8.3** (Rector rules)
3. **PHP 8.3 → 8.4** (Rector rules)
4. **Verify PHP 8.4.0+** is working
5. **DO NOT proceed to PHP 8.5 yet**

**Parallel migrations** (can be done during Phase 1):
- **TypeScript 4.0 → 5.6** (no PHP/Symfony dependency)
- **React 17 → 19** (no PHP/Symfony dependency)

### Phase 2: Symfony 5.4 → 8.0 (requires PHP 8.4+)
**Prerequisite**: PHP 8.4.0+ must be completed
1. **Symfony 5.4 → 6.0** (requires PHP 8.1+)
2. **Symfony 6.0 → 6.4** (requires PHP 8.1+)
3. **Symfony 6.4 → 7.0** (requires PHP 8.2+)
4. **Symfony 7.0 → 8.0** (requires PHP 8.4.0+)
5. **Verify Symfony 8.0** is stable

### Phase 3: PHP 8.4 → 8.5 (after Symfony 8.0)
**Prerequisite**: Symfony 8.0 must be completed and stable
1. **PHP 8.4 → 8.5** (Rector rules)
2. **Verify Symfony 8.0 compatibility** with PHP 8.5
3. **Final testing**

### Phase 4: Development Tools (after main migrations)
- **PHPUnit 9 → 10** (requires PHP 8.1+)
- **Jest 26 → 29+** (no PHP dependency)
- **ESLint 6 → 9** (no PHP dependency)

## 8. Identified Risks

1. **Version dependency chain**: PHP 8.4 → Symfony 8.0 → PHP 8.5 must be respected
2. **Symfony breaking changes**: Many breaking changes between 5.4 and 8.0
3. **PHP 8.4 required for Symfony 8.0**: PHP 8.4 migration MUST be completed before Symfony 8.0
4. **PHP 8.5 after Symfony 8.0**: PHP 8.5 can only be done after Symfony 8.0 is stable
5. **React breaking changes**: Significant changes in React 19
6. **Bundle compatibility**: Some bundles may not be compatible with Symfony 8.0
7. **Library compatibility**: Some libraries may require updates
8. **Tests**: Some tests may require adaptations

## 9. Version Dependency Matrix

| Technology | Version | Minimum PHP Required | Can be done in parallel with |
|------------|---------|---------------------|------------------------------|
| PHP 8.2 | 8.2.x | 8.1+ | TypeScript, React |
| PHP 8.3 | 8.3.x | 8.2+ | TypeScript, React |
| PHP 8.4 | 8.4.x | 8.3+ | TypeScript, React |
| PHP 8.5 | 8.5.x | 8.4+ | **After Symfony 8.0** |
| Symfony 6.0 | 6.0.x | 8.1+ | TypeScript, React |
| Symfony 6.4 | 6.4.x | 8.1+ | TypeScript, React |
| Symfony 7.0 | 7.0.x | 8.2+ | TypeScript, React |
| Symfony 8.0 | 8.0.x | **8.4.0+** | TypeScript, React |
| PHPUnit 10 | 10.x | 8.1+ | After PHP 8.1+ |
| React 19 | 19.x | None | Any time |
| TypeScript 5.6 | 5.6.x | None | Any time |
