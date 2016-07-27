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

## Definition of Batch Jobs services

Since the v1.0, the definition of Jobs is done through a dedicated batch_jobs.yml configuration file.
This file is automatically found and parsed by a dedicated compiler pass scanning all bundles.
The v1.6 allows to use standard symfony services for batch jobs services configuration.
This changes allows to,
- Avoid to instanciate all jobs and steps on each http/cli call (use tagged services for job and JobRegistry and no systematic instanciation in the ConnectorRegistry causing circular dep issue)
- Allow to easily create a new kind of Job (no need of JobFactory) http://docs.spring.io/spring-batch/apidocs/org/springframework/batch/core/Job.html
- Allow to easily create a new kind of Step (no need of StepFactory) http://docs.spring.io/spring-batch/apidocs/org/springframework/batch/core/Step.html
- Allow to make item step immutable (using constructor and no setters for reader, processor, writer, etc)
- No more need of systematic Step + StepElement, for instance the ValidatorStep could contains the CharsetValidator logic
- Less magic by using a standard Symfony way to declare services (taggued services for jobs, steps are standard services)

Before the v1.6 the batch_jobs.yml contains,
```
connector:
    name: Akeneo CSV Connector
    jobs:
        csv_attribute_import:
            type:  import
            steps:
                validation:
                    class: '%pim_connector.step.validator.class%'
                    services:
                        charsetValidator: pim_connector.validator.item.charset_validator
                import:
                    services:
                        reader:    pim_connector.reader.file.csv_attribute
                        processor: pim_connector.processor.denormalization.attribute.flat
                        writer:    pim_connector.writer.doctrine.attribute
```

Since the v1.6, in a standard "jobs.yml" services file,
```
parameters:
    pim_connector.connector_name.csv: 'Akeneo CSV Connector'
    pim_connector.job_name.csv_attribute_import: 'csv_attribute_import'
    pim_connector.job.simple_job.class: Akeneo\Component\Batch\Job\Job
    pim_connector.step.item_step.class: Akeneo\Component\Batch\Step\ItemStep

services:
    pim_connector.job.csv_attribute_import:
        class: %pim_connector.job.simple_job.class%
        arguments:
            - '%pim_connector.job_name.csv_attribute_import%'
            - '@event_dispatcher'
            - '@akeneo_batch.job_repository'
            -
                - '@pim_connector.step.charset_validator'
                - '@pim_connector.step.csv_attribute_import.import'
        tags:
            - { name: akeneo_batch.job, connector: %pim_connector.connector_name.csv%, type: %akeneo_batch.job.import_type% }
```

After, in a standard "steps.yml" services file,
```
parameters:
    pim_connector.step.validator.class: Pim\Component\Connector\Step\ValidatorStep
    pim_connector.step.tasklet.class:   Pim\Component\Connector\Step\TaskletStep

services:
    pim_connector.step.charset_validator:
        class: %pim_connector.step.validator.class%
        arguments:
            - 'validation'
            - '@event_dispatcher'
            - '@akeneo_batch.job_repository'
            - '@pim_connector.validator.item.charset_validator'

    pim_connector.step.csv_attribute_import.import:
        class: %pim_connector.step.item_step.class%
        arguments:
            - 'import'
            - '@event_dispatcher'
            - '@akeneo_batch.job_repository'
            - '@pim_connector.reader.file.csv_attribute'
            - '@pim_connector.processor.denormalization.attribute.flat'
            - '@pim_connector.writer.doctrine.attribute'
```

These files have to be declared in the the bundle extension, for instance,
```
namespace Pim\Bundle\ConnectorBundle\DependencyInjection;
// ...
class PimConnectorExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('jobs.yml');
        $loader->load('steps.yml');
    }
}
```

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

### Reader and Processor services

The converter services allowed to convert from flat data (e.g. CSV) to standard format. Conversion has moved from processors to readers.
Now the processors only accept standard format, and the naming has changed from `pim_connector.processor.normalization.<class>.flat` to `pim_connector.processor.normalization.<class>`.
The YAML Reader moved from BatchBundle to ConnectorBundle; The CSV Readers moved in specific folders, and the naming changed too.

If you use in your import standard Akeneo PIM processor and reader services, please execute the following command in your project folder:
```
    find ./src/ -type f -print0 | xargs -0 sed -i 's/pim_connector\.processor\.normalization\.\(.*\)\.flat/pim_connector\.processor\.normalization\.\1/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/pim_base_connector\.reader\.file\.yaml/pim_connector\.reader\.file\.yaml/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Connector\\Reader\\File\\CsvReader/Pim\\Component\\Connector\\Reader\\File\\Csv\\Reader/g
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Connector\\Reader\\File\\CsvProductReader/Pim\\Component\\Connector\\Reader\\File\\Csv\\ProductReader/g
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\BaseConnectorBundle\\Reader\\File\\YamlReader/Pim\\Component\\Connector\\Reader\\File\\Yaml\\Reader/g
    find ./src/ -type f -print0 | xargs -0 sed -i 's/pim_base_connector\.reader\.orm\.family/pim_connector\.reader\.database\.family/g
    find ./src/ -type f -print0 | xargs -0 sed -i 's/pim_base_connector\.reader\.orm\.attribute/pim_connector\.reader\.database\.attribute/g
    find ./src/ -type f -print0 | xargs -0 sed -i 's/pim_base_connector\.reader\.orm\.association_type/pim_connector\.reader\.database\.association_type/g
    find ./src/ -type f -print0 | xargs -0 sed -i 's/pim_base_connector\.reader\.orm\.attribute_option/pim_connector\.reader\.database\.attribute_option/g
    find ./src/ -type f -print0 | xargs -0 sed -i 's/pim_base_connector\.reader\.orm\.category/pim_connector\.reader\.database\.category/g
    find ./src/ -type f -print0 | xargs -0 sed -i 's/pim_base_connector\.reader\.repository\.group/pim_connector\.reader\.database\.group/g
    find ./src/ -type f -print0 | xargs -0 sed -i 's/pim_base_connector\.reader\.repository\.variant_group/pim_connector\.reader\.database\.variant_group/g
```

### BaseConnectorBundle

TODO : This bundle will be removed after the export refactoring
```
    find ./src/ -type f -print0 | xargs -0 sed -i 's/ Pim\\Bundle\\BaseConnectorBundle\\DependencyInjection\\Compiler\\RegisterArchiversPass/Pim\\Bundle\\ConnectorBundle\\DependencyInjection\\Compiler\\RegisterArchiversPass/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/ Pim\\Bundle\\BaseConnectorBundle\\EventListener\\/Pim\\Bundle\\ConnectorBundle\\EventListener\\/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/ Pim\\Bundle\\BaseConnectorBundle\\Archiver\\/Pim\\Component\\Connector\\Archiver\\/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/ Pim\\Bundle\\BaseConnectorBundle\\Validator\\Constraints\\/Pim\\Component\\Connector\\Validator\\Constraints\/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/pim_base_connector\.archiver\.file_writer_archiver/pim_connector\.archiver\.file_writer_archiver/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/pim_base_connector\.event_listener\.archivist/pim_connector\.event_listener\.archivist/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/pim_base_connector\.archiver/pim_connector\.archiver/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/pim_base_connector\.archiver\.invalid_item_csv_archiver/pim_connector\.archiver\.invalid_item_csv_archiver/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/pim_base_connector\.archiver\.file_reader_archiver/pim_connector\.archiver\.file_reader_archiver/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/pim_base_connector\.factory\.zip_filesystem/pim_connector\.factory\.zip_filesystem/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/pim_base_connector\.archiver\.archivable_file_writer_archiver/pim_connector\.archiver\.archivable_file_writer_archiver/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/pim_base_connector\.validator\.constraints\.channel_validator/pim_connector\.validator\.constraints\.channel_validator/g'
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

## Mass edit actions declaration

In 1.5 and before mass edit actions could be declared like this:
```
my_bundle.mass_edit_action.my_action:
        public: false
        class: '%my_bundle.mass_edit_action.my_action.class%'
        tags:
            -
                name: pim_enrich.mass_edit_action
                alias: my_action
                acl: pim_enrich_product_edit_attributes
```
As of 1.6, the `datagrid` entry of the tag is mandatory because a mass edit action linked to no datagrid makes no sense.

Also, a new `operation_group` entry is introduced and is mandatory too. Several mass edit actions with the same operation group will appear on the same "Choose operation" page (the first step in the mass edit process).
There are two operation groups for now: "mass-edit" and "category-edit", "mass-edit" being the default one.

Now your custom mass action declaration should look like this:
```
my_bundle.mass_edit_action.my_action:
        public: false
        class: '%my_bundle.mass_edit_action.my_action.class%'
        tags:
            -
                name: pim_enrich.mass_edit_action
                alias: my_action
                datagrid: product-grid
                operation_group: mass-edit
                acl: pim_enrich_product_edit_attributes
```
In this example it is an action for the products grid and it must appear in the default group.

## Product exports configuration format update

As of 1.6, product export job configurations (raw parameters) have a different format.

**Before 1.6:**
```
csv_product_export:
    connector: Akeneo CSV Connector
    alias:     csv_product_export
    label:     Demo product export
    type:      export
    configuration:
        channel:    mobile
        delimiter:  ;
        enclosure:  '"'
        withHeader: true
        filePath:   /tmp/product.csv
        decimalSeparator: .
```

**Since 1.6:**
```
csv_product_export:
    connector: Akeneo CSV Connector
    alias:     csv_product_export
    label:     Demo CSV product export
    type:      export
    configuration:
        delimiter:  ;
        enclosure:  '"'
        withHeader: true
        filePath:   /tmp/product.csv
        decimalSeparator: .
        filters:
            data: []
            structure:
                scope: mobile
                locales:
                    - fr_FR
                    - en_US
                    - de_DE
```

You can now notice a `filters` key containing `data` and `structure`.
- `data`: Restricts rows to export
- `structure`: Restricts columns to export

We provide doctrine migration to handle this change in your existing data.

TODO: Add links to the docs.akeneo.com regarding this format update.

##  PHP7

We continued our effort regarding Akeneo PIM PHP7 support.
We're happy to announce that PHP7 is now usable in experimental mode for both CLI and Web.
Experimental means that we manage to install and use the PIM but due to missing tests in our functional matrix we can't commit to support it.

MongoDB 2.6 enforce several limitations, for instance, the Index Key Limit, https://docs.mongodb.com/v2.6/reference/limits/#Index-Key-Limit.

This limitation impacts indexes, when you use PHP7, please dump your database and run the command app/console 'pim:product:ensure-mongodb-indexes'.

This command will remove existing indexes from product collection and will add new indexes with relevant types.

Your attributes using `text` backend type will now use `hashed` index type supporting more than 1024 chars.

Before the 1.6, the command creates only ascendant indexes, even for `text` backend type causing the non indexing of the too long value without any notice.

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
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Connector\\Reader\\Doctrine/Pim\\Component\\Connector\\Reader\\Database/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Connector\\Reader\\ProductReader/Pim\\Component\\Connector\\Reader\\Database\\ProductReader/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/pim_connector\.reader\.product/pim_connector\.reader\.database\.product/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/pim_connector\.reader\.doctrine/pim_connector\.reader\.database/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Connector\\Writer\\Doctrine/Pim\\Component\\Connector\\Writer\\Database/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/pim_connector\.writer\.doctrine/pim_connector\.writer\.database/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Connector\\Writer\\CsvWriter/Pim\\Component\\Connector\\Writer\\Csv\\Writer/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Connector\\Writer\\CsvProductWriter/Pim\\Component\\Connector\\Writer\\Csv\\ProductWriter/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Connector\\Writer\\CsvVariantGroupWriter/Pim\\Component\\Connector\\Writer\\Csv\\VariantGroupWriter/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Connector\\Writer\\YamlWriter/Pim\\Component\\Connector\\Writer\\Yaml\\Writer/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\ImportExportBundle\\Form\\Type\\JobInstanceType/Pim\\Bundle\\ImportExportBundle\\Form\\Type\\JobInstanceFormType/g'
```
