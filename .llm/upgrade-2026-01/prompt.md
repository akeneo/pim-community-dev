# Migration Session Resume Prompt

## Context

I am working on migrating the Akeneo PIM Community Dev project to the latest versions of frameworks and languages. The migration documentation is located in the `./.llm/upgrade-2026-01/` directory.

**⚠️ IMPORTANT**: This project uses a Docker stack. System PHP and Node.js versions are irrelevant and should not be checked. All commands should be executed via Docker containers. Only check versions defined in `composer.json` and `package.json` configuration files.

## Project Overview

- **Project**: Akeneo PIM Community Dev
- **Current versions** (from project configuration files):
  - PHP: 8.1.* (required in composer.json)
  - Symfony: 5.4.*
  - React: 17.0.2
  - TypeScript: 4.0.3

- **Target versions**:
  - PHP: 8.5.0 (published November 20, 2025)
  - Symfony: 8.0.3 (published December 31, 2025) - **⚠️ REQUIRES PHP 8.4.0+**
  - React: 19.x (latest major version)
  - TypeScript: 5.6.x (latest stable version)

## Migration Documentation Structure

All migration documentation is located in `./.llm/upgrade-2026-01/`:

1. **00-executive-summary.md** - Executive summary and overview
2. **00-migration-tools.md** - Migration tools (Rector equivalents for JS/TS)
3. **00-version-verification.md** - Official version verification
4. **00-version-dependencies.md** - Version dependencies matrix (CRITICAL - read this first)
5. **01-technology-audit.md** - Complete technology audit
6. **02-required-migrations.md** - Required migration details
7. **03-action-plan.md** - Detailed action plan with checklist
8. **04-php-tracking.md** - PHP migration tracking (8.1 → 8.4 → 8.5)
9. **05-typescript-tracking.md** - TypeScript migration tracking (4.0 → 5.6)
10. **06-react-tracking.md** - React migration tracking (17 → 19)
11. **07-symfony-tracking.md** - Symfony migration tracking (5.4 → 8.0)
12. **08-tools-tracking.md** - Development tools migration tracking
13. **09-final-validation.md** - Final validation and summary
14. **COMMANDS.md** - Quick reference commands
15. **README.md** - General guide
16. **rector-example.php** - Rector configuration example

## Critical Prerequisites and Version Dependencies

⚠️ **CRITICAL**: Due to version dependencies, migration MUST follow this exact order:

1. **Phase 2**: PHP 8.1 → 8.4 (MUST complete before Symfony 8.0)
2. **Phase 5**: Symfony 5.4 → 8.0 (requires PHP 8.4.0+)
3. **Phase 6**: PHP 8.4 → 8.5 (MUST be done after Symfony 8.0 is stable)

**See `00-version-dependencies.md` for complete dependency matrix.**

**DO NOT skip phases or change order.**

## Migration Methodology

### For PHP/Symfony
1. Use **Rector** to apply migration rules
2. Apply **one rule at a time**
3. Run **all tests** after each rule:
   - `vendor/bin/phpunit`
   - `vendor/bin/behat`
   - `vendor/bin/phpstan analyse`
4. Document each step in the appropriate tracking files

### For React/TypeScript
1. Use **react-codemod** for React migrations
2. Use **ts-migrate** or manual migration for TypeScript
3. Apply **one transformation at a time**
4. Run **all tests** after each transformation:
   - `yarn unit`
   - `yarn integration`
   - `yarn test:e2e:run`
5. Document each step in the appropriate tracking files

## Recommended Migration Order (Phased Approach)

Due to version dependencies between PHP and Symfony, migration MUST follow this exact order:

### Phase 1: PHP 8.1 → 8.4 (MUST complete before Symfony 8.0)
1. **PHP 8.1 → 8.2** (Rector rules)
2. **PHP 8.2 → 8.3** (Rector rules)
3. **PHP 8.3 → 8.4** (Rector rules)
4. **Verify PHP 8.4.0+** is working
5. **DO NOT proceed to PHP 8.5 yet** (wait until after Symfony 8.0)

**Parallel migrations** (can be done during Phase 1):
- **TypeScript 4.0 → 5.6** (no PHP/Symfony dependency)
- **React 17 → 19** (no PHP/Symfony dependency)

### Phase 2: Symfony 5.4 → 8.0 (requires PHP 8.4+)
**Prerequisite**: PHP 8.4.0+ MUST be completed
1. **Symfony 5.4 → 6.0** (requires PHP 8.1+)
2. **Symfony 6.0 → 6.4** (requires PHP 8.1+)
3. **Symfony 6.4 → 7.0** (requires PHP 8.2+)
4. **Symfony 7.0 → 8.0** (requires PHP 8.4.0+)
5. **Verify Symfony 8.0** is stable

### Phase 3: PHP 8.4 → 8.5 (after Symfony 8.0)
**Prerequisite**: Symfony 8.0 MUST be completed and stable
1. **PHP 8.4 → 8.5** (Rector rules)
2. **Verify Symfony 8.0 compatibility** with PHP 8.5
3. **Final testing**

### Phase 4: Development Tools (after main migrations)
- **PHPUnit 9 → 10** (requires PHP 8.1+)
- **Jest 26 → 29+** (no PHP dependency)
- **ESLint 6 → 9** (no PHP dependency)

## How to Resume the Migration

### Step 1: Check Current Status

1. Read the tracking files to understand what has been completed:
   - `04-php-tracking.md` - Check PHP migration progress
   - `05-typescript-tracking.md` - Check TypeScript migration progress
   - `06-react-tracking.md` - Check React migration progress
   - `07-symfony-tracking.md` - Check Symfony migration progress
   - `08-tools-tracking.md` - Check tools migration progress

2. Identify the last completed step in each tracking file

3. Check which phase is currently in progress:
   - Phase 1: Preparation
   - Phase 2: PHP Migration (8.1 → 8.4) - **MUST complete before Symfony 8.0**
   - Phase 3: TypeScript Migration (4.0 → 5.6) - Can be done in parallel
   - Phase 4: React Migration (17 → 19) - Can be done in parallel
   - Phase 5: Symfony Migration (5.4 → 8.0) - **Requires PHP 8.4+**
   - Phase 6: PHP Migration (8.4 → 8.5) - **MUST be done after Symfony 8.0**
   - Phase 7: Development Tools Migration
   - Phase 8: Final Tests and Validation

### Step 2: Verify Prerequisites

Before continuing, verify:
- [ ] Git branch is created: `git checkout -b upgrade-2026-01` (or switch to existing branch)
- [ ] Docker stack is running (versions are managed via Docker, not system)
- [ ] All dependencies are installed: `composer install && yarn install` (via Docker if needed)
- [ ] Tests pass on current state: `vendor/bin/phpunit && yarn unit` (via Docker if needed)
- [ ] PHP version in `composer.json` is appropriate for the next migration step

### Step 3: Continue Migration

1. **Identify the next step** from `03-action-plan.md` based on current progress
2. **Follow the methodology**:
   - For PHP/Symfony: Use Rector with dry-run first, then apply, then test
   - For React/TypeScript: Use codemod tools or manual migration, then test
3. **Document progress** in the appropriate tracking file after each step
4. **Run all tests** after each rule/transformation before proceeding

### Step 4: Update Tracking Files

After completing each step:
- [ ] **Get current system date** and use it for all date fields
- [ ] Update the relevant tracking file (04-09)
- [ ] Mark completed checkboxes
- [ ] **Fill in all date fields** using current system date (replace `[To be completed]` with actual dates)
- [ ] Document any issues encountered with timestamps
- [ ] Document solutions applied with timestamps
- [ ] Record test results with execution dates

## Important Rules

1. **CRITICAL: Respect the phased migration order**:
   - **Phase 2**: PHP 8.1 → 8.4 MUST be completed before Symfony 8.0
   - **Phase 5**: Symfony 8.0 requires PHP 8.4.0+ (verify in composer.json)
   - **Phase 6**: PHP 8.4 → 8.5 MUST be done AFTER Symfony 8.0 is stable
   - **DO NOT skip phases or change order**
2. **Always do a dry-run before applying changes**
3. **Always run tests after each rule/transformation**
4. **Document each step** in tracking files
5. **One rule/transformation at a time** - don't skip steps
6. **Commit frequently** after successful steps
7. **Use Docker for execution** - system PHP/Node.js versions are irrelevant, all commands should run via Docker stack
8. **Verify version dependencies** before each phase:
   - Check `composer.json` for PHP version requirements
   - Verify Symfony version requirements match PHP version
   - Do not proceed if prerequisites are not met

## Quick Commands Reference

See `COMMANDS.md` for detailed command reference. Key commands:

**Note**: All commands should be executed via Docker stack. System PHP/Node.js versions are not used.

### PHP/Symfony (via Docker)
```bash
# Rector dry-run (execute via Docker)
vendor/bin/rector process --set=PHP_83 --dry-run

# Rector apply (execute via Docker)
vendor/bin/rector process --set=PHP_83

# Tests (execute via Docker)
vendor/bin/phpunit && vendor/bin/behat
```

### React/TypeScript (via Docker)
```bash
# React migration (execute via Docker)
npx react-codemod react-18-upgrade src/ --dry

# TypeScript check (execute via Docker)
yarn tsc --noEmit

# Tests (execute via Docker)
yarn unit && yarn integration
```

## Time Tracking and Date Management

**IMPORTANT**: Always use the system date to track time and progress.

1. **Get current system date** at the start of each session using system date/time
2. **Use system date** when filling in date fields in tracking files (replace `[To be completed]` with actual dates)
3. **Calculate elapsed time** between steps using system dates
4. **Update date fields** in tracking files:
   - Application dates
   - Test execution dates
   - Issue resolution dates
   - Start/end dates for each phase

When documenting progress, always use the format: `YYYY-MM-DD` or `YYYY-MM-DD HH:MM:SS` based on system date.

## Current Session Instructions

When resuming this migration session:

1. **Get current system date** and note it for this session
2. **Read `00-version-dependencies.md`** to understand version requirements
3. **Read all tracking files** (04-09) to understand current progress
4. **Check dates** in tracking files to understand timeline and identify recent activity
5. **Identify current phase**:
   - Check PHP version in `composer.json`
   - If PHP < 8.4: You are in Phase 2 (PHP 8.1 → 8.4)
   - If PHP 8.4+ and Symfony < 8.0: You are in Phase 5 (Symfony migration)
   - If Symfony 8.0 complete and PHP < 8.5: You are in Phase 6 (PHP 8.4 → 8.5)
6. **Verify prerequisites** for current phase:
   - **Phase 2**: Check PHP version in composer.json
   - **Phase 5**: Verify PHP 8.4.0+ in composer.json before starting
   - **Phase 6**: Verify Symfony 8.0 is stable before starting
7. **Identify the next incomplete step** from `03-action-plan.md` for current phase
8. **Execute the step** following the methodology
9. **Update tracking files** with progress, using current system date for all date fields
10. **Run tests** to verify success
11. **Continue to next step** or pause if issues are encountered
12. **DO NOT skip phases** - respect the migration order strictly

## Notes

- All documentation is in English
- Tracking files use checkboxes `[ ]` to mark progress
- **Always use system date** when filling in date fields - never leave dates as `[To be completed]`
- Fill in `[To be completed]` fields as you progress, using current system date for dates
- Document any issues or deviations from the plan with timestamps
- The migration is complex but achievable with methodical approach
- **Time tracking is essential** - use system dates consistently throughout the migration
- **Docker stack is used** - system PHP and Node.js versions are irrelevant, only check versions in `composer.json` and `package.json`
- All commands should be executed via Docker containers, not directly on the system

## Questions to Answer Before Resuming

1. **What is the current system date?** (Use system date/time - this is critical for tracking)
2. **What is the current PHP version requirement?** (Check `composer.json` - system PHP version is irrelevant, Docker is used)
3. **What phase are you in?**
   - If PHP < 8.4 in composer.json: Phase 2 (PHP 8.1 → 8.4)
   - If PHP 8.4+ and Symfony < 8.0: Phase 5 (Symfony migration)
   - If Symfony 8.0 complete and PHP < 8.5: Phase 6 (PHP 8.4 → 8.5)
4. **Are prerequisites met for current phase?**
   - Phase 2: No prerequisites
   - Phase 5: PHP 8.4.0+ must be verified
   - Phase 6: Symfony 8.0 must be stable
5. What is the current Symfony version? (Check `composer.json`)
6. What is the current React version? (Check `package.json`)
7. What is the current TypeScript version? (Check `package.json`)
8. Which tracking file shows the most recent progress?
9. What was the last completed step and when was it completed? (Check dates in tracking files)
10. Are there any unresolved issues documented in tracking files?
11. How much time has elapsed since the last migration activity? (Calculate using system date vs dates in tracking files)
12. Is the Docker stack running and ready? (Versions are managed via Docker, not system)
13. **Have you read `00-version-dependencies.md`?** (Critical for understanding version requirements)

---

**Use this prompt to resume the migration session. Read the tracking files first to understand where you left off, then continue with the next step following the methodology described above.**
