# Technology Audit

Date: 2026-01-XX
Project: Akeneo PIM Community Dev

## 1. PHP

### Current Version
- **PHP**: 8.1.* (required in composer.json)
- **PHP installed**: 8.2.30 (detected on system)
- **Target**: PHP 8.5.0 (latest stable version, published November 20, 2025)
- **Source**: https://www.php.net/releases/8.5/en

### Available PHP Tools
- ✅ **Rector**: ^0.15.0 (installed)
- ✅ **PHP-CS-Fixer**: ^3.13.0 (installed)
- ✅ **PHPStan**: ^1.9.3 (installed)
- ✅ **PHPUnit**: ^9.5 (installed)
- ✅ **Behat**: Available via friends-of-behat

### PHP Configuration
- `phpunit.xml.dist`: PHPUnit configuration present
- `phpstan.neon`: PHPStan configuration present
- `rector.php`: Rector configuration files present in several modules

## 2. Symfony

### Current Version
- **Symfony**: 5.4.* (defined in composer.json)
- **Symfony Components**: All in version ^5.4.0
- **Target**: Symfony 8.0.3 (latest stable version, published December 31, 2025)
- **Note**: Symfony 8.0 requires PHP 8.4.0 or higher
- **Source**: https://symfony.com/releases/8.0

### Symfony Components Used
- symfony/asset: ^5.4.0
- symfony/cache: ^5.4
- symfony/config: ^5.4.0
- symfony/dependency-injection: ^5.4.0
- symfony/doctrine-messenger: ^5.4.0
- symfony/event-dispatcher: ^5.4.0
- symfony/form: ^5.4.0
- symfony/http-foundation: ^5.4.0
- symfony/http-kernel: ^5.4.0
- symfony/messenger: ^5.4.0
- symfony/routing: ^5.4.0
- symfony/security-bundle: ^5.4.0
- symfony/translation: ^5.4.0
- symfony/twig-bundle: ^5.4.0
- symfony/validator: ^5.4.0
- And other components...

### Third-Party Symfony Bundles
- doctrine/doctrine-bundle: ^2.5.5
- doctrine/doctrine-migrations-bundle: ^3.2.0
- friendsofsymfony/jsrouting-bundle: 3.2.1
- friendsofsymfony/rest-bundle: ^3.4.0
- liip/imagine-bundle: 2.10.0
- symfony/monolog-bundle: ^3.8.0

## 3. React

### Current Version
- **React**: ^17.0.2
- **React-DOM**: ^17.0.2
- **Target**: React 19.x (latest major stable version)
- **Note**: Verify exact minor version on https://react.dev/versions
- **Source**: https://react.dev/versions

### Associated React Libraries
- react-redux: ^7.1.0
- react-router-dom: ^5.1.0
- react-query: ^3.39.1
- react-hook-form: ^5.2.0
- react-markdown: ^5.0.3
- formik: ^2.0.6
- styled-components: ^5.1.1
- reakit: ^1.0.0-rc.0

### React Types
- @types/react: ^17.0.2
- @types/react-dom: ^17.0.2
- @types/react-redux: ^7.1.0
- @types/react-router-dom: ^5.1.0

## 4. JavaScript/TypeScript

### Current Version
- **TypeScript**: ^4.0.3
- **Node.js**: v24.11.1 (installed)
- **Target**: TypeScript 5.6.x (latest confirmed stable version)
- **Note**: Verify on https://www.typescriptlang.org/ if a newer version exists
- **Source**: https://www.typescriptlang.org/

### Available JavaScript/TypeScript Tools
- ✅ **ESLint**: ^6.5.1
- ✅ **Prettier**: ^2.1.1
- ✅ **Jest**: ^26.4.2
- ✅ **ts-jest**: ^26.4.0
- ✅ **ts-loader**: 5.3.3
- ✅ **Cypress**: ^6.6.0 (E2E tests)

### TypeScript Configuration
- `tsconfig.json`: Configuration present
  - Target: ES5
  - Module: commonjs
  - Lib: ["dom", "es2015", "es2016", "es2017", "es2018", "es2019"]
  - JSX: react
  - Strict mode enabled

### ESLint Configuration
- `.eslintrc`: Configuration present
- Parser: babel-eslint
- Plugins: react, jest, testing-library, etc.

### Prettier Configuration
- `.prettierrc.json`: Configuration present
- Print width: 120
- Single quote: true
- Trailing comma: es5

## 5. Build Tools

### Webpack
- **Version**: ^5.75.0
- **webpack-cli**: ^5.0.0
- **Target**: Webpack 5.x (already up to date)

### Babel
- @babel/core: ^7.3.4
- @babel/preset-env: ^7.3.4
- @babel/preset-react: ^7.10.4
- babel-loader: ^8.1.0

## 6. Tests

### PHP Tests
- **PHPUnit**: ^9.5
- **Behat**: Available
- **PhpSpec**: ^7.1.0

### JavaScript/TypeScript Tests
- **Jest**: ^26.4.2
- **@testing-library/react**: ^11.2.6
- **@testing-library/jest-dom**: ^5.11.10
- **@testing-library/user-event**: ^12.8.3
- **Cypress**: ^6.6.0

## 7. Other Important Dependencies

### Backend
- Doctrine ORM: ^2.9.0
- Doctrine DBAL: ^2.13.4
- Twig: ^3.3.3
- Monolog: ^2.8.0
- Guzzle: ^7.5.0
- Elasticsearch: 7.11.0

### Frontend
- jQuery: ^3.6.0
- Backbone: 0.9.10
- Lodash: ^4.17.0
- Luxon: ^1.26.0
- Victory: ^33.1.6

## 8. Yarn Workspaces

The project uses Yarn workspaces with several packages:
- akeneo-design-system
- @akeneo-pim-community/shared
- Several workspaces in src/ and components/

## 9. Points of Attention

1. **PHP 8.1 → 8.5**: Migration required with breaking changes verification
2. **Symfony 5.4 → 8.0**: Major migration with significant changes
3. **React 17 → 19**: Major migration with changes in hooks and rendering
4. **TypeScript 4.0 → 5.6**: Migration with new features and breaking changes
5. **PHPUnit 9 → 10**: Migration required for PHP 8.5 compatibility
6. **Jest 26 → 29+**: Migration required for React 19 compatibility
7. **ESLint 6 → 9**: Migration required for TypeScript 5.6 compatibility
