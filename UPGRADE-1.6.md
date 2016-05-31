# UPGRADE FROM 1.5 to 1.6

> Please perform a backup of your database before proceeding to the migration. You can use tools like  [mysqldump](http://dev.mysql.com/doc/refman/5.1/en/mysqldump.html) and [mongodump](http://docs.mongodb.org/manual/reference/program/mongodump/).

> Please perform a backup of your codebase if you don't use any VCS.

## Catalog Bundle & Component

We've extracted following classes and interfaces from the Catalog bundle to the Catalog component:
 - validation

## Batch Bundle & Component

This component has been re-work to be more focus on a more robust batch processing and to extract UI concerns.

We've extracted the template concern from JobInterface.
In 1.5, you have to declare your custom twig template to use to view or edit a job in the batch_jobs.yml.
In 1.6, you can register them in the JobTemplateProvider through the parameters %pim_import_export.job_template.config%.
Migration, you need to remove your 'show_template' and 'edit_template' configuration from your custom batch_jobs.yml file.

We've extracted the translated labels concern from JobInterface and StepInterface.
In 1.5, you have to declare your titles in batch_jobs.yml for jobs and steps.
In 1.6, you only have to add a translation key following this convention,
 - for a Job, batch_jobs.job_name.label (ex: batch_jobs.csv_product_export.label: "Product export in CSV")
 - for a Step, batch_jobs.job_name.step_name.label (batch_jobs.csv_product_export.export.label: "Product export step")
Migration, you need to remove your 'title' configuration from your custom batch_jobs.yml file.

## Deprecated imports

We've removed `TransformBundle` and `BaseConnectorBundle` because they are deprecated since the new import system has been created.

### TransformBundle

Flat (De)Normalizers have been to moved to `Connector` component and Structured ones have been to moved to `Catalog` component

Based on a PIM standard installation, execute the following command in your project folder:
```
    find ./src/ -type f -print0 | xargs -0 sed -i 's/ Pim\\Bundle\\TransformBundle\\Normalizer\\Flat/Pim\\Component\\Connector\\Normalizer/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/ Pim\\Bundle\\TransformBundle\\Denormalizer\\Flat/Pim\\Component\\Connector\\Denormalizer/g
    find ./src/ -type f -print0 | xargs -0 sed -i 's/ Pim\\Bundle\\TransformBundle\\Normalizer\\Structured/Pim\\Component\\Catalog\\Normalizer/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/ Pim\\Bundle\\TransformBundle\\Denormalizer\\Structured/Pim\\Component\\Catalog\\Denormalizer/g'
```

Extra classes have been moved but the rest of the `TransformBundle` have been removed

Based on a PIM standard installation, execute the following command in your project folder:
```
    find ./src/ -type f -print0 | xargs -0 sed -i 's/ Pim\\Bundle\\TransformBundle\\Encoder/Pim\\Component\\Connector\\Encoder/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/ Pim\\Bundle\\TransformBundle\\DependencyInjection\\Compiler\\SerializerPass/Pim\\Bundle\\CatalogBundle\\DependencyInjection\\Compiler\\RegisterSerializerPass/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/ Pim\\Bundle\\TransformBundle\\Converter/Pim\\Component\\Catalog\\Converter/g'
```

### BaseConnectorBundle

TODO : This bundle will be removed after the export refactoring
```
    find ./src/ -type f -print0 | xargs -0 sed -i 's/ Pim\\Bundle\\TransformBundle\\Cache/Pim\\Bundle\\BaseConnectorBundle\\Cache/g'
```

See the documentation [here](http://docs.akeneo.com/latest/reference/import_export/index.html).


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

## Domain layer extraction

We extracted the business classes into Components.
 
### Catalog

We extracted business related stuff about attribute types by introducing `Pim\Component\Catalog\AttributeTypeInterface`.*
There is no impact for custom attribute types except that backend type constants `BACKEND_TYPE_*` have been moved from `Pim\Bundle\CatalogBundle\AttributeType\AbstractAttributeType` to `Pim\Component\Catalog\AttributeTypes`. 
To detect the files impacted by this change, you can execute the following command in your project folder:
```
    grep -rl 'AbstractAttributeType::BACKEND_TYPE' src/* 
```

## Partially fix BC breaks

If you have a standard installation with some custom code inside, the following command allows to update changed services or use statements.

**It does not cover all possible BC breaks, as the changes of arguments of a service, consider using this script on versioned files to be able to check the changes with a `git diff` for instance.**

Based on a PIM standard installation, execute the following command in your project folder:

```
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\Bundle\CatalogBundle\Factory\AttributeFactory/Pim\Component\Catalog\Factory\AttributeFactory/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\Bundle\CatalogBundle\AttributeType\AttributeTypeRegistry/Pim\Component\Catalog\AttributeTypeRegistry/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\Factory\\AttributeRequirementFactory/Pim\\Component\\Catalog\\Factory\\AttributeRequirementFactory/g'
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
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Connector\\Reader\\File\\CsvProductReader/Pim\\Component\\Connector\\Reader\\File\\Product\\CsvProductReader/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\BaseConnectorBundle\\Processor\\Normalization\\VariantGroupProcessor/Pim\\Component\\Connector\\Processor\\Normalization\\VariantGroupProcessor/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Akeneo\\Bundle\\BatchBundle\\Job\\JobFactory/Akeneo\\Component\\Batch\\Job\\JobFactory/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Akeneo\\Bundle\\BatchBundle\\Step\\StepFactory/Akeneo\\Component\\Batch\\Step\\StepFactory/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Normalizer\\Structured\\ReferenceDataNormalizer/Pim\\Component\\ReferenceData\\Normalizer\\Structured\\ReferenceDataNormalizer/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Connector\\Normalizer\\Flat\\ReferenceDataNormalizer/Pim\\Component\\ReferenceData\\Normalizer\\Flat\\ReferenceDataNormalizer/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Denormalizer\\Structured\\ProductValue\\ReferenceDataDenormalizer/Pim\\Component\\ReferenceData\\Denormalizer\\Structured\\ProductValue\\ReferenceDataDenormalizer/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Denormalizer\\Structured\\ProductValue\\ReferenceDataCollectionDenormalizer/Pim\\Component\\ReferenceData\\Denormalizer\\Structured\\ProductValue\\ReferenceDataCollectionDenormalizer/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Connector\\Denormalizer\\Flat\\ProductValue\\ReferenceDataDenormalizer/Pim\\Component\\ReferenceData\\Denormalizer\\Flat\\ProductValue\\ReferenceDataDenormalizer/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Connector\\Denormalizer\\Flat\\ProductValue\\ReferenceDataCollectionDenormalizer/Pim\\Component\\ReferenceData\\Denormalizer\\Flat\\ProductValue\\ReferenceDataCollectionDenormalizer/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Normalizer\\Structured\\JobInstanceNormalizer/Akeneo\\Component\\Batch\\Normalizer\\Structured\\JobInstanceNormalizer/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Connector\\ArrayConverter\\StandardArrayConverterInterface/Pim\\Component\\Connector\\ArrayConverter\\ArrayConverterInterface/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/implements StandardArrayConverterInterface/implements ArrayConverterInterface/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Connector\\ArrayConverter\\Flat/Pim\\Component\\Connector\\ArrayConverter\\FlatToStandard/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/pim_connector\.array_converter\.flat\./pim_connector\.array_converter\.flat_to_standard\./g'
```
