# UPGRADE FROM 1.7 to 1.8

<!-- START doctoc generated TOC please keep comment here to allow auto update -->
<!-- DON'T EDIT THIS SECTION, INSTEAD RE-RUN doctoc TO UPDATE -->
**Table of Contents:**

- [Disclaimer](#disclaimer)
- [Migrate your standard project](#migrate-your-standard-project)
- [Migrate your custom code](#migrate-your-custom-code)

<!-- END doctoc generated TOC please keep comment here to allow auto update -->
<!-- To update this content, execute `doctoc UPGRADE-1.7.md --title '**Table of Contents:**' --maxlevel 3` -->

## Disclaimer

> Please check that you're using Akeneo PIM v1.7

> We're assuming that you created your project from the standard distribution

> This documentation helps to migrate projects based on the Community Edition and the Enterprise Edition

> Please perform a backup of your database before proceeding to the migration. You can use tools like [mysqldump](http://dev.mysql.com/doc/refman/5.1/en/mysqldump.html) and [mongodump](http://docs.mongodb.org/manual/reference/program/mongodump/).

> Please perform a backup of your codebase if you don't use a VCS (Version Control System).


## Migrate your standard project

1. Download it from the website [PIM community standard](http://www.akeneo.com/download/) and extract:

```bash
TODO
```

2. Copy the following files to your PIM installation:

```bash
export PIM_DIR=/path/to/your/pim/installation
cp app/SymfonyRequirements.php $PIM_DIR/app
cp app/PimRequirements.php $PIM_DIR/app

mv $PIM_DIR/app/config/pim_parameters.yml $PIM_DIR/app/config/pim_parameters.yml.bak
cp app/config/pim_parameters.yml $PIM_DIR/app/config

mv $PIM_DIR/composer.json $PIM_DIR/composer.json.bak
cp composer.json $PIM_DIR/
```

3. Update the configuration of your application `$PIM_DIR/app/config/config.yml` to add these new lines:

```YAML
TODO
```

3. Update your application Kernel `$PIM_DIR/app/AppKernel.php`:

* Remove the following bundles:

```PHP
Oro\Bundle\NavigationBundle\OroNavigationBundle
Pim\Bundle\JsFormValidationBundle\PimJsFormValidationBundle
APY\JsFormValidationBundle\APYJsFormValidationBundle
Oro\Bundle\RequireJSBundle\OroRequireJSBundle(),
```

4. Then remove your old upgrades folder:

```bash
rm -rf $PIM_DIR/upgrades/schema
```

5. Now update your dependencies:

* [Optional] If you had added dependencies to your project, you will need to do it again in your `composer.json`.
  You can display the differences of your previous composer.json in `$PIM_DIR/composer.json.bak`.

```JSON
    "require": {
            "your/dependency": "version",
            "your/other-dependency": "version",
    }
```

* Then run the command to update your dependencies:

```bash
php -d memory_limit=3G composer update
```

This step will copy the upgrades folder from `pim-community-dev/` to your Pim project root in order to migrate.
If you have custom code in your project, this step may raise errors in the "post-script" command.
In this case, go to the chapter "Migrate your custom code" before running the database migration.

6. Then you can migrate your database using:

```bash
rm -rf app/cache
php app/console doctrine:migration:migrate --env=prod
```

7. Then, generate JS translations and re-generate the PIM assets:

```bash
TODO
```

## Migrate your custom code

TODO

## Building the front-end with webpack

1. Install the latest npm and nodejs https://nodejs.org/en/download/package-manager/
2. Run `php app/console pim:installer:dump-require-paths`
3. Run `php app/console assets:install --symlink`
4. Create a file in your project root called `package.json` with the following contents:

```js
{
  "name": "your-project-name",
  "version": "1.0.0",
  "description": "your-project-description",
  "scripts": {
    "install": "npm install vendor/akeneo/pim-community-dev",
    "webpack": "webpack --display-error-details --config vendor/akeneo/pim-community-dev/webpack.config.js --progress --display-modules",
    "webpack-watch": "webpack --progress --config vendor/akeneo/pim-community-dev/webpack.config.js --watch"
  }
}
```

5. Run `npm install` inside your project root
6. Run `npm run webpack`
7. Run `php app/console cache:clear`
8. Add to your .gitignore the following lines:

```
  node_modules
  web/dist/*
  web/cache/*
  npm-debug.log
```

If you have any custom Javascript you will need to make the following changes:

When you require a html template, you no longer have to use the `text!` prefix.

Before:

```javascript
  define([
     'text!oro/template/system/tab/system'
  ],
```

After:
```javascript
  define([
     'oro/template/system/tab/system'
  ],
```

Instead of using `module.config()` to access module configuration, you must instead use `__moduleConfig`.

Before:
```javascript
   Routing.generate(module.config().url, {identifier: datagridView.id});
```

After:
  ```javascript
    Routing.generate(__moduleConfig.url, {identifier: datagridView.id});
  ```

You can now write custom Javascript using es2017 (es2015 and above) syntax. We use babel to transpile the JS during the webpack build step. Check out the full guide here - https://babeljs.io/learn-es2015/

While you are developing custom Javscript, you can run `npm run webpack-watch` to automatically compile and refresh your changes. Otherwise, you can run `npm run webpack` without refresh to see your changes.
