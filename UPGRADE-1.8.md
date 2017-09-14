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
cp var/SymfonyRequirements.php $PIM_DIR/app
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

```json
{
    "require": {
        "your/dependency": "version",
        "your/other-dependency": "version"
    }
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
rm -rf var/cache
php bin/console doctrine:migration:migrate --env=prod
```

7. Then, generate JS translations and re-generate the PIM assets:

```bash
TODO
```

## Migrate your custom code

We extracted the "values" logic outside of products. It means that any entity having the `EntityWithValuesInterface`
would be able to be processed. As a reminder, a value is composed of an attribute, a locale, a scope, and a data. As values are not only
for product anymore, we decided to rename `ProductValueInterface` to `ValueInterface`. This impacts a lot of classes too.

The following commands help to migrate references to these classes or services.

```bash
## Classes
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\VersioningBundle\\Normalizer\\Flat\\AbstractProductValueDataNormalizer/Pim\\Bundle\\VersioningBundle\\Normalizer\\Flat\\AbstractValueDataNormalizer/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\VersioningBundle\\Normalizer\\Flat\\ProductValueNormalizer/Pim\\Bundle\\VersioningBundle\\Normalizer\\Flat\\ValueNormalizer/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Completeness\\Checker\\ProductValueCompleteChecker/Pim\\Component\\Catalog\\Completeness\\Checker\\ValueCompleteChecker/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Completeness\\Checker\\ProductValueCompleteCheckerInterface/Pim\\Component\\Catalog\\Completeness\\Checker\\ValueCompleteCheckerInterface/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Factory\\ProductValue\\DateProductValueFactory/Pim\\Component\\Catalog\\Factory\\Value\\DateValueFactory/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Factory\\ProductValue\\MediaProductValueFactory/Pim\\Component\\Catalog\\Factory\\Value\\MediaValueFactory/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Factory\\ProductValue\\MetricProductValueFactory/Pim\\Component\\Catalog\\Factory\\Value\\MetricValueFactory/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Factory\\ProductValue\\OptionProductValueFactory/Pim\\Component\\Catalog\\Factory\\Value\\OptionValueFactory/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Factory\\ProductValue\\OptionsProductValueFactory/Pim\\Component\\Catalog\\Factory\\Value\\OptionsValueFactory/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Factory\\ProductValue\\PriceCollectionProductValueFactory/Pim\\Component\\Catalog\\Factory\\Value\\PriceCollectionValueFactory/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Factory\\ProductValue\\ProductValueFactoryInterface/Pim\\Component\\Catalog\\Factory\\Value\\ValueFactoryInterface/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Factory\\ProductValue\\ScalarProductValueFactory/Pim\\Component\\Catalog\\Factory\\Value\\ScalarValueFactory/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Factory\\ProductValueCollectionFactory/Pim\\Component\\Catalog\\Factory\\ProductValueCollectionFactory/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Factory\\ProductValueFactory/Pim\\Component\\Catalog\\Factory\\ValueFactory/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\ProductValue\\DateProductValue/Pim\\Component\\Catalog\\Value\\DateValue/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\ProductValue\\MediaProductValue/Pim\\Component\\Catalog\\Value\\MediaValue/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\ProductValue\\MetricProductValue/Pim\\Component\\Catalog\\Value\\MetricValue/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\ProductValue\\OptionProductValue/Pim\\Component\\Catalog\\Value\\OptionValue/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\ProductValue\\OptionsProductValue/Pim\\Component\\Catalog\\Value\\OptionsValue/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Connector\\ArrayConverter\\FlatToStandard\\ProductValue/Pim\\Component\\Connector\\ArrayConverter\\FlatToStandard\\Value/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Enrich\\Converter\\EnrichToStandard\\ProductValueConverter/Pim\\Component\\Enrich\\Converter\\EnrichToStandard\\ValueConverter/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Enrich\\Converter\\StandardToEnrich\\ProductValueConverter/Pim\\Component\\Enrich\\Converter\\StandardToEnrich\\ValueConverter/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\ReferenceData\\Factory\\ProductValue\\ReferenceDataCollectionProductValueFactory/Pim\\Component\\ReferenceData\\Factory\\Value\\ReferenceDataCollectionValueFactory/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\ReferenceData\\Factory\\ProductValue\\ReferenceDataProductValueFactory/Pim\\Component\\ReferenceData\\Factory\\Value\\ReferenceDataValueFactory/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\ReferenceData\\ProductValue\\ReferenceDataCollectionProductValue/Pim\\Component\\ReferenceData\\Value\\ReferenceDataCollectionValue/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\ReferenceData\\ProductValue\\ReferenceDataProductValue/Pim\\Component\\ReferenceData\\Value\\ReferenceDataValue/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\DependencyInjection\\Compiler\\RegisterProductValueValueFactoryPass/Pim\\Bundle\\CatalogBundle\\DependencyInjection\\Compiler\\RegisterValueFactoryPass/g'

## Services
find ./src/ -type f -print0 | xargs -0 sed -i 's/pim_catalog\.factory\.product_value/pim_catalog\.factory\.value/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/pim_catalog\.factory\.product_value_collection/pim_catalog\.factory\.value_collection/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/pim_catalog\.factory\.product_value\.text/pim_catalog\.factory\.value\.text/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/pim_catalog\.factory\.product_value\.textarea/pim_catalog\.factory\.value\.textarea/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/pim_catalog\.factory\.product_value\.number/pim_catalog\.factory\.value\.number/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/pim_catalog\.factory\.product_value\.boolean/pim_catalog\.factory\.value\.boolean/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/pim_catalog\.factory\.product_value\.identifier/pim_catalog\.factory\.value\.identifier/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/pim_catalog\.factory\.product_value\.metric/pim_catalog\.factory\.value\.metric/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/pim_catalog\.factory\.product_value\.price_collection/pim_catalog\.factory\.value\.price_collection/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/pim_catalog\.factory\.product_value\.option/pim_catalog\.factory\.value\.option/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/pim_catalog\.factory\.product_value\.options/pim_catalog\.factory\.value\.options/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/pim_catalog\.factory\.product_value\.file/pim_catalog\.factory\.value\.file/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/pim_catalog\.factory\.product_value\.image/pim_catalog\.factory\.value\.image/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/pim_catalog\.factory\.product_value\.date/pim_catalog\.factory\.value\.date/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/pim_catalog\.model\.product_value\.interface/pim_catalog\.model\.value\.interface/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/pim_versioning\.serializer\.normalizer\.flat\.product_value/pim_versioning\.serializer\.normalizer\.flat\.value/g'

## Parameters
find ./src/ -type f -print0 | xargs -0 sed -i 's/pim_catalog\.factory\.product_value_collection\.class/pim_catalog\.factory\.value_collection\.class/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/pim_catalog\.factory\.product_value\.class/pim_catalog\.factory\.value\.class/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/pim_catalog\.factory\.product_value\.scalar\.class/pim_catalog\.factory\.value\.scalar\.class/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/pim_catalog\.factory\.product_value\.metric\.class/pim_catalog\.factory\.value\.metric\.class/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/pim_catalog\.factory\.product_value\.price_collection\.class/pim_catalog\.factory\.value\.price_collection\.class/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/pim_catalog\.factory\.product_value\.option\.class/pim_catalog\.factory\.value\.option\.class/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/pim_catalog\.factory\.product_value\.options\.class/pim_catalog\.factory\.value\.options\.class/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/pim_catalog\.factory\.product_value\.media\.class/pim_catalog\.factory\.value\.media\.class/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/pim_catalog\.factory\.product_value\.date\.class/pim_catalog\.factory\.value\.date\.class/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/pim_serializer\.normalizer\.flat\.product_value\.class/pim_serializer\.normalizer\.flat\.value\.class/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/pim_catalog\.entity\.product_value\.scalar\.class/pim_catalog\.entity\.value\.scalar\.class/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/pim_catalog\.entity\.product_value\.media\.class/pim_catalog\.entity\.value\.media\.class/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/pim_catalog\.entity\.product_value\.metric\.class/pim_catalog\.entity\.value\.metric\.class/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/pim_catalog\.entity\.product_value\.option\.class/pim_catalog\.entity\.value\.option\.class/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/pim_catalog\.entity\.product_value\.options\.class/pim_catalog\.entity\.value\.options\.class/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/pim_catalog\.entity\.product_value\.date\.class/pim_catalog\.entity\.value\.date\.class/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/pim_catalog\.entity\.product_value\.price_collection\.class/pim_catalog\.entity\.value\.price_collection\.class/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/pim_enrich\.converter\.standard_to_enrich\.product_value\.class/pim_enrich\.converter\.standard_to_enrich\.value\.class/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/pim_enrich\.converter\.enrich_to_standard\.product_value\.class/pim_enrich\.converter\.enrich_to_standard\.value\.class/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/pim_reference_data\.factory\.product_value\.reference_data\.class/pim_reference_data\.factory\.value\.reference_data\.class/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/pim_reference_data\.factory\.product_value\.reference_data_collection\.class/pim_reference_data\.factory\.value\.reference_data_collection\.class/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/pim_reference_data\.product_value\.reference_data\.class/pim_reference_data\.value\.reference_data\.class/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/pim_reference_data\.product_value\.reference_data_collection\.class/pim_reference_data\.value\.reference_data_collection\.class/g'
```

## Building the front-end with webpack

Before running these steps make sure that you run `composer update` and `bin/console pim:install`

1. Install nodejs (>=6.11.0 - 8.2.1 supported)

You can follow the instructions here - https://nodejs.org/en/download/package-manager/

2. Install yarn (0.27.5 supported)

You can follow the instructions here - https://yarnpkg.com/en/docs/install
Or you can use npm `npm install -g yarn@0.27.5`

3. Run `php bin/console assets:install --symlink`

This will make sure that your javascript files are symlinked so that webpack can compile the changes.

4. Copy `package.json` from [PIM community standard](http://www.akeneo.com/download/)
5. Run `yarn install` inside your project root
6. Run `yarn run webpack`
7. Run `php bin/console cache:clear`
8. Add to your .gitignore the following lines:

```
  node_modules
  web/dist/*
  web/cache/*
  npm-debug.log
  yarn.lock
  package-lock.json
  .yarn-metadata.json
  yarn-error.log
```

If you have any custom Javascript you will need to make the following changes:

When you require a html template inside a module, you no longer have to use the `text!` prefix. This is handled in [webpack.config.js](https://github.com/akeneo/pim-community-dev/blob/master/webpack.config.js#L78).

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

While you are developing custom Javscript, you can run `yarn run webpack-watch` to automatically compile and refresh your changes. Otherwise, you can run `yarn run webpack` without refresh to see your changes. This command will also minify your code.
