# Migration Tools - Rector Equivalents for JS/TS

## PHP Tools (already available)

### Rector
- **Version**: ^0.15.0
- **Usage**: Automated PHP and Symfony migration
- **Commands**:
  ```bash
  vendor/bin/rector process --set=PHP_83 --dry-run
  vendor/bin/rector process --set=SYMFONY_60
  ```

### PHP-CS-Fixer
- **Version**: ^3.13.0
- **Usage**: PHP code formatting and correction
- **Commands**:
  ```bash
  vendor/bin/php-cs-fixer fix --dry-run
  vendor/bin/php-cs-fixer fix
  ```

### PHPStan
- **Version**: ^1.9.3
- **Usage**: PHP static code analysis
- **Commands**:
  ```bash
  vendor/bin/phpstan analyse
  ```

## JavaScript/TypeScript Tools (Rector equivalents)

### 1. react-codemod (React Migration)

**Description**: Official Facebook tool for migrating React code to new versions.

**Installation**:
```bash
npx react-codemod --help
```

**Available Transformations**:
- `react-18-upgrade`: Migration to React 18
- `react-19-upgrade`: Migration to React 19 (if available)
- `rename-unsafe-lifecycles`: Rename lifecycle methods
- `update-react-imports`: Update React imports
- `remove-unused-react-imports`: Remove unused React imports

**Usage**:
```bash
# Dry-run
npx react-codemod react-18-upgrade src/ --dry

# Application
npx react-codemod react-18-upgrade src/
```

**Rector Equivalent**: Yes, this is the direct equivalent for React

### 2. ts-migrate (TypeScript Migration)

**Description**: Tool developed by Airbnb to migrate JavaScript to TypeScript or update TypeScript.

**Installation**:
```bash
npm install -g ts-migrate
# or
npx ts-migrate-full .
```

**Features**:
- JavaScript → TypeScript conversion
- Add `any` types to start
- Progressive migration to stricter types
- Automatic correction of some errors

**Usage**:
```bash
# Complete migration
npx ts-migrate-full .

# Migration of specific directory
npx ts-migrate-full src/
```

**Rector Equivalent**: Partial, more oriented JS→TS conversion than TypeScript migration

### 3. jscodeshift (Code Transformations)

**Description**: Powerful tool for transforming JavaScript/TypeScript code, used by react-codemod.

**Installation**:
```bash
npm install -g jscodeshift
```

**Usage**:
```bash
# Apply transformation
jscodeshift -t transformation.js src/

# Dry-run
jscodeshift -t transformation.js src/ --dry
```

**Rector Equivalent**: Yes, this is the base tool for transformations

### 4. @typescript-eslint/typescript-estree (TypeScript Analysis)

**Description**: TypeScript parser for analysis and transformation.

**Usage**: Used internally by other tools, but can be used directly.

**Rector Equivalent**: Partial, more for analysis than transformation

### 5. Babel (Transpilation)

**Description**: JavaScript transpiler, can be used for some migrations.

**Usage**: Mainly for browser compatibility, but can help in some migrations.

**Rector Equivalent**: No, more for transpilation than migration

### 6. ESLint with auto-fix (Automatic Corrections)

**Description**: ESLint can automatically fix some errors.

**Usage**:
```bash
# Dry-run
yarn eslint src/ --fix --dry-run

# Application
yarn eslint src/ --fix
```

**Rector Equivalent**: Partial, for style corrections and some rules

### 7. Prettier (Formatting)

**Description**: Code formatter, similar to PHP-CS-Fixer.

**Usage**:
```bash
# Dry-run
yarn prettier --check src/

# Application
yarn prettier --write src/
```

**PHP-CS-Fixer Equivalent**: Yes, direct equivalent

## Recommended Strategy for JS/TS

### For React
1. **react-codemod** for major migrations (17→18, 18→19)
2. **ESLint** for automatic corrections
3. **Manual migration** for complex cases

### For TypeScript
1. **TypeScript update** directly
2. **Manual correction** of type errors
3. **ts-migrate** if necessary for some complex transformations
4. **ESLint** for automatic corrections

### Application Order
1. Update dependencies in `package.json`
2. Install: `yarn install`
3. Check errors: `yarn tsc --noEmit`
4. Apply react-codemod if React migration
5. Manually fix remaining errors
6. Run tests after each step

## Comparison with Rector

| Feature | Rector (PHP) | JS/TS Equivalent |
|---------|--------------|------------------|
| Major framework migration | ✅ Symfony sets | ✅ react-codemod |
| Language version migration | ✅ PHP sets | ⚠️ Partial (ts-migrate) |
| Configurable rules | ✅ Yes | ✅ jscodeshift |
| Dry-run | ✅ Yes | ✅ All |
| Progressive application | ✅ Yes | ⚠️ Partial |
| Tests after each rule | ✅ Recommended | ✅ Recommended |

## Recommended Migration Commands

### React 17 → 18
```bash
# 1. Dry-run
npx react-codemod react-18-upgrade src/ --dry

# 2. Application
npx react-codemod react-18-upgrade src/

# 3. Tests
yarn unit && yarn integration
```

### React 18 → 19
```bash
# 1. Check availability
npx react-codemod --help

# 2. If available, apply
npx react-codemod react-19-upgrade src/

# 3. Tests
yarn unit && yarn integration
```

### TypeScript 4.0 → 5.6
```bash
# 1. Update
yarn add -D typescript@^5.6.0

# 2. Verification
yarn tsc --noEmit > ts-errors.log

# 3. Progressive manual correction
# 4. Tests after each correction
yarn unit
```

## Important Notes

1. **No exact equivalent**: There is no exact equivalent to Rector for JS/TS
2. **Tool combination**: Need to combine several tools
3. **Manual migration**: A significant part must be done manually
4. **Tests essential**: Tests are even more important on JS/TS side
5. **Documentation**: Less documentation than Rector for JS/TS migrations
