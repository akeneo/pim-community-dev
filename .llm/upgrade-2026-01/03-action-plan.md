# Detailed Action Plan

Date: 2026-01-XX
Project: Akeneo PIM Community Dev

## Phase 1: Preparation

### 1.1 Git Flow Setup
- [ ] Verify you are on `develop` branch: `git checkout develop`
- [ ] Pull latest changes: `git pull origin develop`
- [ ] Verify all tests pass on develop branch
- [ ] Document initial state in tracking files
- [ ] **Create Phase 2 branch**: `git checkout -b feature/upgrade-2026-01-php-8.4`
- [ ] Document branch creation in `04-php-tracking.md`

### 1.2 Dependency Installation
- [ ] Install Composer dependencies: `composer install`
- [ ] Install Yarn dependencies: `yarn install`
- [ ] Verify everything works: `yarn test && vendor/bin/phpstan analyse && vendor/bin/phpunit`

### 1.3 Rector Configuration
- [ ] Create `rector.php` file at project root
- [ ] Configure Rector for PHP and Symfony migrations
- [ ] Test configuration: `vendor/bin/rector --dry-run`

## Phase 2: PHP 8.1 → 8.4 Migration (BEFORE Symfony 8.0)

**⚠️ CRITICAL**: This phase MUST be completed before starting Symfony 8.0 migration. Symfony 8.0 requires PHP 8.4.0+.

### 2.1 Update Required PHP Version to 8.4
- [ ] Modify `composer.json`: `"php": "^8.4"`
- [ ] Run: `composer update --dry-run`
- [ ] Check for potential conflicts
- [ ] **DO NOT** set PHP to 8.5 yet (wait until after Symfony 8.0)

### 2.2 Apply Rector PHP Rules (in order)
**Rule 1: PHP_82 - PHP 8.2**
- [ ] Apply: `vendor/bin/rector process --set=PHP_82 --dry-run`
- [ ] Review proposed changes
- [ ] Apply: `vendor/bin/rector process --set=PHP_82`
- [ ] Run tests: `vendor/bin/phpstan analyse && vendor/bin/phpunit && vendor/bin/behat`
- [ ] Document in `04-php-tracking.md`

**Rule 2: PHP_83 - Typed class constants**
- [ ] Apply: `vendor/bin/rector process --set=PHP_83 --dry-run`
- [ ] Review proposed changes
- [ ] Apply: `vendor/bin/rector process --set=PHP_83`
- [ ] Run validations: `vendor/bin/phpstan analyse && vendor/bin/phpunit && vendor/bin/behat`
- [ ] **If PHPStan fails**: Fix errors one by one, commit each fix separately
- [ ] **If tests fail**: Fix tests one by one, commit each fix separately
- [ ] **When all pass**: Commit Rector changes atomically
- [ ] Document in `04-php-tracking.md`

**Rule 3: PHP_84 - PHP 8.4 (REQUIRED before Symfony 8.0)**
- [ ] Apply: `vendor/bin/rector process --set=PHP_84 --dry-run`
- [ ] Review proposed changes
- [ ] Apply: `vendor/bin/rector process --set=PHP_84`
- [ ] Run validations: `vendor/bin/phpstan analyse && vendor/bin/phpunit && vendor/bin/behat`
- [ ] **If PHPStan fails**: Fix errors one by one, commit each fix separately
- [ ] **If tests fail**: Fix tests one by one, commit each fix separately
- [ ] **When all pass**: Commit Rector changes atomically
- [ ] Document in `04-php-tracking.md`
- [ ] **Verify PHP 8.4.0+ is working**: Check `composer.json` and Docker configuration

### 2.3 Update PHP Dependencies (PHP 8.4 compatible)
- [ ] Run: `composer update`
- [ ] Check breaking changes in dependencies
- [ ] Verify all dependencies are compatible with PHP 8.4
- [ ] Run tests: `vendor/bin/phpstan analyse && vendor/bin/phpunit && vendor/bin/behat`
- [ ] Document in `04-php-tracking.md`

### 2.4 Final PHP 8.4 Verification
- [ ] Run PHPStan: `vendor/bin/phpstan analyse` (static analysis - validates code before runtime tests)
- [ ] Run PHP-CS-Fixer: `vendor/bin/php-cs-fixer fix`
- [ ] Run all tests: `vendor/bin/phpunit && vendor/bin/behat` (after PHPStan passes)
- [ ] **Verify PHP version**: Confirm PHP 8.4.0+ in `composer.json`
- [ ] Document in `04-php-tracking.md`
- [ ] **Ready for Symfony 8.0 migration**: PHP 8.4.0+ requirement met

### 2.5 Phase 2 Branch Completion
- [ ] **Verify all commits are atomic**: Each commit should pass validations independently
- [ ] **No uncommitted changes**: All changes should be committed atomically
- [ ] Create pull request: `feature/upgrade-2026-01-php-8.4` → `develop`
- [ ] **Note**: Do NOT create a "complete migration" commit - all changes should already be committed atomically
- [ ] Code review and approval
- [ ] Merge to develop: `git checkout develop && git merge feature/upgrade-2026-01-php-8.4`
- [ ] Delete Phase 2 branch: `git branch -d feature/upgrade-2026-01-php-8.4`
- [ ] Document merge completion in `04-php-tracking.md`

## Phase 3: TypeScript 4.0 → 5.6 Migration

### 3.1 TypeScript Update
- [ ] Modify `package.json`: `"typescript": "^5.6.0"`
- [ ] Run: `yarn install`
- [ ] Verify: `yarn tsc --noEmit`

### 3.2 TypeScript Error Correction
- [ ] List errors: `yarn tsc --noEmit > ts-errors.log`
- [ ] Fix errors one by one
- [ ] Run tests after each correction: `yarn unit`
- [ ] Document in `05-typescript-tracking.md`

### 3.3 TypeScript Configuration Update
- [ ] Update `tsconfig.json` if necessary
- [ ] Add new TypeScript 5.6 options
- [ ] Verify: `yarn tsc --noEmit`
- [ ] Run tests: `yarn unit && yarn integration`
- [ ] Document in `05-typescript-tracking.md`

### 3.4 Final TypeScript Verification
- [ ] Run build: `yarn webpack-dev`
- [ ] Run all tests: `yarn test`
- [ ] Document in `05-typescript-tracking.md`

## Phase 4: React 17 → 19 Migration

### 4.1 React 17 → 18 Migration
- [ ] Modify `package.json`: `"react": "^18.0.0", "react-dom": "^18.0.0"`
- [ ] Run: `yarn install`
- [ ] Apply react-codemod: `npx react-codemod react-18-upgrade`
- [ ] Run tests: `yarn unit && yarn integration`
- [ ] Document in `06-react-tracking.md`

### 4.2 React 18 Manual Corrections
- [ ] Update deprecated hooks
- [ ] Adapt code for Concurrent Rendering
- [ ] Update tests if necessary
- [ ] Run tests after each change: `yarn unit`
- [ ] Document in `06-react-tracking.md`

### 4.3 React 18 → 19 Migration
- [ ] Modify `package.json`: `"react": "^19.2.0", "react-dom": "^19.2.0"`
- [ ] Run: `yarn install`
- [ ] Apply react-codemod if available: `npx react-codemod react-19-upgrade`
- [ ] Run tests: `yarn unit && yarn integration`
- [ ] Document in `06-react-tracking.md`

### 4.4 React 19 Manual Corrections
- [ ] Update to new APIs (Actions, useFormStatus, etc.)
- [ ] Adapt code for new features
- [ ] Update tests if necessary
- [ ] Run tests after each change: `yarn unit`
- [ ] Document in `06-react-tracking.md`

### 4.5 React Dependencies Update
- [ ] Check react-redux compatibility with React 19
- [ ] Check react-router-dom compatibility with React 19
- [ ] Check react-query compatibility with React 19
- [ ] Check styled-components compatibility with React 19
- [ ] Update dependencies if necessary
- [ ] Run tests: `yarn test`
- [ ] Document in `06-react-tracking.md`

### 4.6 Final React Verification
- [ ] Run build: `yarn webpack-dev`
- [ ] Run all tests: `yarn test`
- [ ] Run E2E tests: `yarn test:e2e:run`
- [ ] Document in `06-react-tracking.md`

## Phase 5: Symfony 5.4 → 8.0 Migration

**⚠️ CRITICAL PREREQUISITE**: 
- PHP 8.4.0+ MUST be completed (Phase 2)
- Phase 2 branch (`feature/upgrade-2026-01-php-8.4`) MUST be merged to develop
- Verify PHP version in `composer.json` is `^8.4` before starting

### 5.0 Git Flow Branch Setup
- [ ] Verify you are on `develop` branch: `git checkout develop`
- [ ] Verify Phase 2 branch is merged: Check git log for Phase 2 commits
- [ ] Pull latest changes: `git pull origin develop`
- [ ] **Create Phase 5 branch**: `git checkout -b feature/upgrade-2026-01-symfony-8.0`
- [ ] Document branch creation in `07-symfony-tracking.md`

**Migration order**: 5.4 → 6.0 → 6.4 → 7.0 → 8.0 (sequential, each step requires specific PHP version)

### 5.1 Symfony 5.4 → 6.0 Migration
**Rule 1: SYMFONY_60 - Replace annotations with attributes**
- [ ] Apply: `vendor/bin/rector process --set=SYMFONY_60 --dry-run`
- [ ] Review proposed changes
- [ ] Apply: `vendor/bin/rector process --set=SYMFONY_60`
- [ ] Run tests: `vendor/bin/phpstan analyse && vendor/bin/phpunit && vendor/bin/behat`
- [ ] Document in `07-symfony-tracking.md`

**Rule 2: SYMFONY_60 - Route migration**
- [ ] Apply route migration rules
- [ ] Run tests: `vendor/bin/phpstan analyse && vendor/bin/phpunit && vendor/bin/behat`
- [ ] Document in `07-symfony-tracking.md`

**Rule 3: SYMFONY_60 - Event migration**
- [ ] Apply event migration rules
- [ ] Run tests: `vendor/bin/phpstan analyse && vendor/bin/phpunit && vendor/bin/behat`
- [ ] Document in `07-symfony-tracking.md`

**Rule 4: SYMFONY_60 - Service migration**
- [ ] Apply service migration rules
- [ ] Run tests: `vendor/bin/phpstan analyse && vendor/bin/phpunit && vendor/bin/behat`
- [ ] Document in `07-symfony-tracking.md`

**Rule 5: SYMFONY_60 - Doctrine migration**
- [ ] Apply Doctrine migration rules
- [ ] Run tests: `vendor/bin/phpstan analyse && vendor/bin/phpunit && vendor/bin/behat`
- [ ] Document in `07-symfony-tracking.md`

### 5.2 Symfony 6.0 Dependencies Update
- [ ] Modify `composer.json`: Update all Symfony dependencies to ^6.0
- [ ] Run: `composer update symfony/* --with-all-dependencies`
- [ ] Check breaking changes
- [ ] Run tests: `vendor/bin/phpstan analyse && vendor/bin/phpunit && vendor/bin/behat`
- [ ] Document in `07-symfony-tracking.md`

### 5.3 Symfony 6.0 → 6.4 Migration
- [ ] Apply: `vendor/bin/rector process --set=SYMFONY_64`
- [ ] Run tests: `vendor/bin/phpstan analyse && vendor/bin/phpunit && vendor/bin/behat`
- [ ] Modify `composer.json`: Update to ^6.4
- [ ] Run: `composer update symfony/* --with-all-dependencies`
- [ ] Run tests: `vendor/bin/phpstan analyse && vendor/bin/phpunit && vendor/bin/behat`
- [ ] Document in `07-symfony-tracking.md`

### 5.4 Symfony 6.4 → 7.0 Migration
- [ ] Apply: `vendor/bin/rector process --set=SYMFONY_70`
- [ ] Run tests: `vendor/bin/phpstan analyse && vendor/bin/phpunit && vendor/bin/behat`
- [ ] Modify `composer.json`: Update to ^7.0
- [ ] Run: `composer update symfony/* --with-all-dependencies`
- [ ] Run tests: `vendor/bin/phpstan analyse && vendor/bin/phpunit && vendor/bin/behat`
- [ ] Document in `07-symfony-tracking.md`

### 5.5 Symfony 7.0 → 8.0 Migration
- [ ] **Verify PHP 8.4+**: Check `composer.json` shows `"php": "^8.4"` (Docker handles version, not system)
- [ ] **Verify PHP 8.4.0+ requirement**: Confirm in Docker container
- [ ] Apply: `vendor/bin/rector process --set=SYMFONY_80` (if available)
- [ ] Run tests: `vendor/bin/phpstan analyse && vendor/bin/phpunit && vendor/bin/behat`
- [ ] Modify `composer.json`: Update to ^8.0
- [ ] Run: `composer update symfony/* --with-all-dependencies`
- [ ] Run tests: `vendor/bin/phpstan analyse && vendor/bin/phpunit && vendor/bin/behat`
- [ ] Document in `07-symfony-tracking.md`
- [ ] **Symfony 8.0 migration complete** - Ready for PHP 8.5 migration (Phase 6)

### 5.6 Third-Party Symfony Bundles Update
- [ ] Check doctrine/doctrine-bundle compatibility with Symfony 8.0
- [ ] Check friendsofsymfony/jsrouting-bundle compatibility with Symfony 8.0
- [ ] Check friendsofsymfony/rest-bundle compatibility with Symfony 8.0
- [ ] Check liip/imagine-bundle compatibility with Symfony 8.0
- [ ] Update bundles if necessary
- [ ] Run tests: `vendor/bin/phpstan analyse && vendor/bin/phpunit && vendor/bin/behat`
- [ ] Document in `07-symfony-tracking.md`

### 5.7 Final Symfony Verification
- [ ] Run PHPStan: `vendor/bin/phpstan analyse`
- [ ] Run PHP-CS-Fixer: `vendor/bin/php-cs-fixer fix`
- [ ] Run all tests: `vendor/bin/phpstan analyse && vendor/bin/phpunit && vendor/bin/behat`
- [ ] Document in `07-symfony-tracking.md`

### 5.8 Phase 5 Branch Completion
- [ ] Commit all changes: `git add . && git commit -m "feat(upgrade): complete Symfony 5.4 → 8.0 migration"`
- [ ] Create pull request: `feature/upgrade-2026-01-symfony-8.0` → `develop`
- [ ] Code review and approval
- [ ] Merge to develop: `git checkout develop && git merge feature/upgrade-2026-01-symfony-8.0`
- [ ] Delete Phase 5 branch: `git branch -d feature/upgrade-2026-01-symfony-8.0`
- [ ] Document merge completion in `07-symfony-tracking.md`
- [ ] **Symfony 8.0 migration complete** - Ready for PHP 8.5 migration (Phase 6)

## Phase 6: PHP 8.4 → 8.5 Migration (AFTER Symfony 8.0)

**⚠️ CRITICAL PREREQUISITE**: 
- Symfony 8.0 MUST be completed and stable (Phase 5)
- Phase 5 branch (`feature/upgrade-2026-01-symfony-8.0`) MUST be merged to develop
- Only proceed after Symfony 8.0 is fully working

### 6.0 Git Flow Branch Setup
- [ ] Verify you are on `develop` branch: `git checkout develop`
- [ ] Verify Phase 5 branch is merged: Check git log for Phase 5 commits
- [ ] Pull latest changes: `git pull origin develop`
- [ ] **Create Phase 6 branch**: `git checkout -b feature/upgrade-2026-01-php-8.5`
- [ ] Document branch creation in `04-php-tracking.md`

### 6.1 Verify Symfony 8.0 Stability
- [ ] Confirm Symfony 8.0 is stable and all tests pass
- [ ] Verify no critical issues with Symfony 8.0
- [ ] Document readiness for PHP 8.5 in `04-php-tracking.md`

### 6.2 Update Required PHP Version to 8.5
- [ ] Modify `composer.json`: `"php": "^8.5"`
- [ ] Run: `composer update --dry-run`
- [ ] Check for potential conflicts with Symfony 8.0
- [ ] Verify Symfony 8.0 compatibility with PHP 8.5

### 6.3 Apply Rector PHP 8.5 Rules
- [ ] Apply: `vendor/bin/rector process --set=PHP_85 --dry-run`
- [ ] Review proposed changes
- [ ] Apply: `vendor/bin/rector process --set=PHP_85`
- [ ] Run tests: `vendor/bin/phpstan analyse && vendor/bin/phpunit && vendor/bin/behat`
- [ ] Document in `04-php-tracking.md`

### 6.4 Verify Symfony 8.0 Compatibility with PHP 8.5
- [ ] Run all Symfony tests: `vendor/bin/phpunit && vendor/bin/behat`
- [ ] Verify no regressions introduced
- [ ] Check Symfony 8.0 bundles compatibility
- [ ] Document in `04-php-tracking.md`

### 6.5 Final PHP 8.5 Verification
- [ ] Run PHPStan: `vendor/bin/phpstan analyse`
- [ ] Run PHP-CS-Fixer: `vendor/bin/php-cs-fixer fix`
- [ ] Run all tests: `vendor/bin/phpstan analyse && vendor/bin/phpunit && vendor/bin/behat`
- [ ] Verify PHP version: Confirm PHP 8.5 in `composer.json`
- [ ] Document in `04-php-tracking.md`

## Phase 7: Development Tools Migration

### 7.1 PHPUnit 9 → 10 Migration
- [ ] Modify `composer.json`: `"phpunit/phpunit": "^10.0"`
- [ ] Run: `composer update phpunit/phpunit --with-all-dependencies`
- [ ] Adapt `phpunit.xml.dist` if necessary
- [ ] Run tests: `vendor/bin/phpstan analyse && vendor/bin/phpunit`
- [ ] Document in `08-tools-tracking.md`

### 7.2 Jest 26 → 29+ Migration
- [ ] Modify `package.json`: `"jest": "^29.0.0"`
- [ ] Run: `yarn install`
- [ ] Adapt Jest configurations if necessary
- [ ] Run tests: `yarn unit && yarn integration`
- [ ] Document in `08-tools-tracking.md`

### 7.3 ESLint 6 → 9 Migration
- [ ] Modify `package.json`: `"eslint": "^9.0.0"`
- [ ] Run: `yarn install`
- [ ] Migrate to ESLint flat configuration
- [ ] Adapt `.eslintrc` to new format
- [ ] Run: `yarn lint`
- [ ] Document in `08-tools-tracking.md`

### 7.4 Other Tools Update
- [ ] Update Prettier if necessary
- [ ] Update Cypress if necessary
- [ ] Update other development tools
- [ ] Run tests: `yarn test && vendor/bin/phpstan analyse && vendor/bin/phpunit`
- [ ] Document in `08-tools-tracking.md`

## Phase 8: Final Tests and Validation

### 8.1 Complete Tests
- [ ] Run all PHP tests: `vendor/bin/phpstan analyse && vendor/bin/phpunit && vendor/bin/behat`
- [ ] Run all JS/TS tests: `yarn test`
- [ ] Run E2E tests: `yarn test:e2e:run`
- [ ] Verify build: `yarn webpack-dev`
- [ ] Document in `09-final-validation.md`

### 8.2 Static Analysis
- [ ] Run PHPStan: `vendor/bin/phpstan analyse`
- [ ] Run PHP-CS-Fixer: `vendor/bin/php-cs-fixer fix --dry-run`
- [ ] Run ESLint: `yarn lint`
- [ ] Run Prettier: `yarn prettier:check`
- [ ] Document in `09-final-validation.md`

### 8.3 Documentation
- [ ] Update README if necessary
- [ ] Update change documentation
- [ ] Create CHANGELOG file for this migration
- [ ] Document in `09-final-validation.md`

### 8.4 Code Review
- [ ] Perform complete code review
- [ ] Verify all changes are documented
- [ ] Verify all tests pass
- [ ] Document in `09-final-validation.md`

## Phase 9: Deployment

### 8.1 Deployment Preparation
- [ ] Create version tag if necessary
- [ ] Prepare release notes
- [ ] Verify everything is ready for deployment

### 8.2 Merge with Main Branch
- [ ] Merge upgrade-2026-01 branch with main branch
- [ ] Resolve any conflicts
- [ ] Verify everything works after merge

### 8.3 Deployment
- [ ] Deploy to staging environment
- [ ] Verify everything works in staging
- [ ] Deploy to production
- [ ] Monitor performance and errors

## Important Notes

1. **CRITICAL Migration order**: 
   - **Phase 2**: PHP 8.1 → 8.4 (MUST complete before Symfony 8.0)
   - **Phase 5**: Symfony 5.4 → 8.0 (requires PHP 8.4+)
   - **Phase 6**: PHP 8.4 → 8.5 (MUST be done after Symfony 8.0 is stable)
   - **DO NOT skip phases or change order**
2. **Version dependencies**: Respect PHP/Symfony version requirements strictly
3. **Tests after each rule**: Always run tests after each Rector rule or transformation
4. **Documentation**: Document each step in appropriate tracking files
5. **Rollback**: Plan a rollback strategy in case of major issues
6. **Communication**: Communicate with the team on important changes
7. **Docker**: All commands run via Docker - system PHP/Node.js versions are irrelevant
