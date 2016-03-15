# UPGRADE FROM 1.5 to 1.6

> Please perform a backup of your database before proceeding to the migration. You can use tools like  [mysqldump](http://dev.mysql.com/doc/refman/5.1/en/mysqldump.html) and [mongodump](http://docs.mongodb.org/manual/reference/program/mongodump/).

> Please perform a backup of your codebase if you don't use any VCS.

## Catalog Bundle & Component

We've extracted following classes and interfaces from the Catalog bundle to the Catalog component:
 - validation
 
## Update dependencies and configuration

Download the latest [PIM community standard](http://www.akeneo.com/download/) and extract it:

```
 wget http://www.akeneo.com/pim-community-standard-v1.6-latest.tar.gz
 tar -zxf pim-community-standard-v1.6-latest.tar.gz
 cd pim-community-standard-v1.6.*/
```

Copy the following files to your PIM installation:

```
 export PIM_DIR=/path/to/your/pim/installation
 cp app/SymfonyRequirements.php $PIM_DIR/app
 cp app/config/config.yml $PIM_DIR/app/config/
 cp composer.json $PIM_DIR/
```

**In case your products are stored in Mongo**, don't forget to re-add the mongo dependencies to your *composer.json*:

```
 "doctrine/mongodb-odm-bundle": "3.0.1"
```

And don't forget to add your own dependencies to your *composer.json* in case you have some.

Merge the following files into your PIM installation:
 - *app/AppKernel.php*: TODO
 - *app/config/routing.yml*: TODO
 - *app/config/config.yml*: TODO

Then remove your old upgrades folder:
```
 rm upgrades/ -rf
```

Now you're ready to update your dependencies:

```
 cd $PIM_DIR
 composer update
```

This step will also copy the upgrades folder from `vendor/akeneo/pim-community-dev/` to your Pim project root to allow you to migrate.

Then you can migrate your database using:

```
 php app/console doctrine:migration:migrate
```

## Partially fix BC breaks

If you have a standard installation with some custom code inside, the following command allows to update changed services or use statements.

**It does not cover all possible BC breaks, as the changes of arguments of a service, consider using this script on versioned files to be able to check the changes with a `git diff` for instance.**

Based on a PIM standard installation, execute the following command in your project folder:

```
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\Factory\\GroupFactory/Pim\\Component\\Catalog\\Factory\\GroupFactory/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\Factory\\FamilyFactory/Pim\\Component\\Catalog\\Factory\\FamilyFactory/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\Factory\\MetricFactory/Pim\\Component\\Catalog\\Factory\\MetricFactory/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\Manager\\CompletenessManager/Pim\\Component\\Catalog\\Manager\\CompletenessManager/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\Manager\\AttributeGroupManager/Pim\\Component\\Catalog\\Manager\\AttributeGroupManager/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\Manager\\VariantGroupAttributesResolver/Pim\\Component\\Catalog\\Manager\\VariantGroupAttributesResolver/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\Manager\\ProductTemplateApplier/Pim\\Component\\Catalog\\Manager\\ProductTemplateApplier/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\Builder\\ProductTemplateBuilder/Pim\\Component\\Catalog\\Builder\\ProductTemplateBuilder/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\Builder\\ProductBuilder/Pim\\Component\\Catalog\\Builder\\ProductBuilder/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\Manager\\AttributeValuesResolver/Pim\\Component\\Catalog\\Manager\\AttributeValuesResolver/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\Query\\Filter\\DumperInterface/Pim\\Bundle\\CatalogBundle\\Command\\DumperInterface/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\Query\\Filter\\AttributeFilterDumper/Pim\\Bundle\\CatalogBundle\\Command\\ProductQueryHelp\\AttributeFilterDumper/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\Query\\Filter\\FieldFilterDumper/Pim\\Bundle\\CatalogBundle\\Command\\ProductQueryHelp\\FieldFilterDumper/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\Query/Pim\\Component\\Catalog\\Query/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\Query/Pim\\Component\\Catalog\\Exception/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\Exception/Pim\\Component\\Catalog\\Exception/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\Event\\ProductEvents/Pim\\Component\\Catalog\\ProductEvents/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\Repository/Pim\\Component\\Catalog\\Repository/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\Validator/Pim\\Component\\Catalog\\Validator/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\AttributeType\\AttributeTypes/Pim\\Component\\Catalog\\AttributeTypes/g'
```
