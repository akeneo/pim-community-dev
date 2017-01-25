# UPGRADE FROM 1.5 to 1.6

<!-- START doctoc generated TOC please keep comment here to allow auto update -->
<!-- DON'T EDIT THIS SECTION, INSTEAD RE-RUN doctoc TO UPDATE -->
**Table of Contents:**

- [Disclaimer](#disclaimer)
- [Migrate your system requirements](#migrate-your-system-requirements)
  - [PHP 5.6 as minimum version and PHP 7 in experimental mode](#php-56-as-minimum-version-and-php-7-in-experimental-mode)
- [Migrate your standard project](#migrate-your-standard-project)
- [Migrate your custom code](#migrate-your-custom-code)
  - [Global updates for any project](#global-updates-for-any-project)
    - [Update references to moved `Pim\Bundle\CatalogBundle\AttributeType\AbstractAttributeType` constants](#update-references-to-moved-pim%5Cbundle%5Ccatalogbundle%5Cattributetype%5Cabstractattributetype-constants)
    - [Update references to moved `Pim\Component\Catalog` business classes](#update-references-to-moved-pim%5Ccomponent%5Ccatalog-business-classes)
    - [Update references to moved `PimEnterprise\Component\Catalog` business classes](#update-references-to-moved-pimenterprise%5Ccomponent%5Ccatalog-business-classes)
    - [Update references to moved `PimEnterprise\Component\Security` business classes](#update-references-to-moved-pimenterprise%5Ccomponent%5Csecurity-business-classes)
    - [Update references to moved `PimEnterprise\Component\Workflow` business classes](#update-references-to-moved-pimenterprise%5Ccomponent%5Cworkflow-business-classes)
    - [Update references to moved `PimEnterprise\Component\ProductAsset` business classes](#update-references-to-moved-pimenterprise%5Ccomponent%5Cproductasset-business-classes)
    - [Update references to renamed `PimEnterprise\Component\CatalogRule` services](#update-references-to-renamed-pimenterprise%5Ccomponent%5Ccatalogrule-services)
  - [Updates for projects customizing Import / Export](#updates-for-projects-customizing-import--export)
    - [Remove the reference to the removed `Akeneo\Bundle\BatchBundle\Connector\Connector` class](#remove-the-reference-to-the-removed-akeneo%5Cbundle%5Cbatchbundle%5Cconnector%5Cconnector-class)
    - [Update references to the deprecated and removed `TransformBundle` classes](#update-references-to-the-deprecated-and-removed-transformbundle-classes)
    - [Update references to the deprecated and removed `BaseConnectorBundle` classes](#update-references-to-the-deprecated-and-removed-baseconnectorbundle-classes)
    - [Update references to the standardized `Pim/Component/Connector` classes](#update-references-to-the-standardized-pimcomponentconnector-classes)
    - [Change the definition of Batch Jobs services to replace `batch_jobs.yml`](#change-the-definition-of-batch-jobs-services-to-replace-batch_jobsyml)
    - [Remove the reference to `Akeneo\Component\Batch\Item\AbstractConfigurableStepElement`](#remove-the-reference-to-akeneo%5Ccomponent%5Cbatch%5Citem%5Cabstractconfigurablestepelement)
    - [Add JobParameters providers to configure your Job execution](#add-jobparameters-providers-to-configure-your-job-execution)
    - [Add JobParameters providers to configure your Job edition](#add-jobparameters-providers-to-configure-your-job-edition)
    - [Update access to the configuration in your custom Reader, Processor, Writer by using the JobParameters](#update-access-to-the-configuration-in-your-custom-reader-processor-writer-by-using-the-jobparameters)
    - [Update your custom translation keys for Job and Step labels](#update-your-custom-translation-keys-for-job-and-step-labels)
    - [Update the configuration of your custom Product Exports](#update-the-configuration-of-your-custom-product-exports)
  - [Updates for projects adding custom Mass Edit Action](#updates-for-projects-adding-custom-mass-edit-action)
    - [Update the mass edit actions services](#update-the-mass-edit-actions-services)
    - [Update the mass edit backend processes](#update-the-mass-edit-backend-processes)
- [Known issues](#known-issues)

<!-- END doctoc generated TOC please keep comment here to allow auto update -->
<!-- To update doctoc UPGRADE-1.6.md --title '**Table of Contents' -->

## Disclaimer

> Please check that you're using Akeneo PIM v1.5

> We're assuming that you created your project from the standard distribution

> This documentation helps to migrate projects based on the Community Edition and the Enterprise Edition

> Please perform a backup of your database before proceeding to the migration. You can use tools like [mysqldump](http://dev.mysql.com/doc/refman/5.1/en/mysqldump.html) and [mongodump](http://docs.mongodb.org/manual/reference/program/mongodump/).

> Please perform a backup of your codebase if you don't use a VCS (Version Control System).

## Migrate your system requirements

### PHP 5.6 as minimum version and PHP 7 in experimental mode

In the Akeneo PIM 1.6 version, we continued our effort regarding Akeneo PIM PHP7 support.

We're happy to announce that PHP7 is now usable in experimental mode for both CLI and Web, for both ORM and MongoDB storages.

Experimental means that we manage to install and use the PIM but due to missing tests in our functional matrix we can't commit to officially supporting it (for now).

This modification introduces a new constraint, the minimum version of PHP for Akeneo PIM is now PHP 5.6 (due to our dependencies, we had to choose between <= PHP 5.6 or >= PHP 5.6).

## Migrate your standard project

1. Download and extract the latest standard archive,

    * For the **Community Edition**, download it from the website [PIM community standard](http://www.akeneo.com/download/) and extract:

    ```
     wget http://download.akeneo.com/pim-community-standard-v1.6-latest.tar.gz
     tar -zxf pim-community-standard-v1.6-latest.tar.gz
     cd pim-community-standard/
    ```

    * For the **Enterprise Edition**, download the archive from the Partner Portal and extract:

    ```
     tar -zxf pim-enterprise-standard.tar.gz
     cd pim-enterprise-standard/
    ```


2. Copy the following files to your PIM installation:

    ```
     export PIM_DIR=/path/to/your/pim/installation
     cp app/SymfonyRequirements.php $PIM_DIR/app
     cp app/PimRequirements.php $PIM_DIR/app
     cp app/config/pim_parameters.yml $PIM_DIR/app/config
     cp composer.json $PIM_DIR/
    ```

3. Update your **app/config/config.yml**

    * Remove the configuration of `CatalogBundle` from this file (config tree :`pim_catalog`).
    * Update the default locale from `en_US` to `en`
    * In the **Enterprise Edition**, the ProductValue model has been moved into the catalog component and requires to change the following configuration:

        v1.5
        ```
        akeneo_storage_utils:
            mapping_overrides:
                -
                    original: Pim\Component\Catalog\Model\ProductValue
                    override: PimEnterprise\Bundle\CatalogBundle\Model\ProductValue
        ```

        v1.6
        ```
        akeneo_storage_utils:
            mapping_overrides:
                -
                    original: Pim\Component\Catalog\Model\ProductValue
                    override: PimEnterprise\Component\Catalog\Model\ProductValue
        ```

4. Update your **app/AppKernel.php**:

    * Remove the following bundles:
        - `Pim\Bundle\BaseConnectorBundle\PimBaseConnectorBundle`
        - `Pim\Bundle\TransformBundle\PimTransformBundle`
        - `Nelmio\ApiDocBundle\NelmioApiDocBundle`

    * In Enterprise Edition, you need to add the following bundle:
        - `PimEnterprise\Bundle\ConnectorBundle\PimEnterpriseConnectorBundle` to `getPimEnterpriseBundles`

5. Update your **app/config/routing.yml**:

    * Remove the route: `nelmio_api_doc`

6. Then remove your old upgrades folder:
    ```
     rm -rf $PIM_DIR/upgrades/schema
    ```

7. Now you're ready to update your dependencies:

    * **Caution**, don't forget to add your own dependencies back to your *composer.json* if you have some:

        ```
        "require": {
            "your/dependencies": "version",
            "your/other-dependencies": "version",
        }
        ```

        If your project uses the https://github.com/akeneo-labs/EnhancedConnectorBundle, please use the following version,

        ```
        "require": {
            "akeneo-labs/pim-enhanced-connector": "1.3.*"
        }
        ```

        If your project uses the https://github.com/akeneo-labs/CustomEntityBundle, please use the following version,

        ```
        "require": {
            "akeneo-labs/custom-entity-bundle": "1.8.*"
        }
        ```

        If your project uses the https://github.com/akeneo-labs/ExcelConnectorBundle, this bundle is not compatible anymore and you must replace it by the https://github.com/akeneo/ExcelInitBundle,

        ```
        "require": {
            "akeneo-labs/excel-init-bundle": "1.0.*"
        }
        ```

        If your project uses the Akeneo/ElasticSearchBundle, please use the following version,

        ```
        "require": {
            "akeneo/elasticsearch-bundle": "1.3.*"
        }
        ```

        If your project uses the Akeneo/InnerVariationBundle, please use the following version,

        ```
        "require": {
            "akeneo/inner-variation-bundle": "1.3.*"
        }
        ```

        Especially if you store your products in Mongo, don't forget to add `doctrine/mongodb-odm-bundle`:

        ```
        "require": {
            "doctrine/mongodb-odm-bundle": "3.2.0"
        }
        ```

    * Then run the command to update your dependencies:

        ```
         cd $PIM_DIR
         composer update
        ```

        This step will also copy the upgrades folder from `vendor/akeneo/pim-community-dev/` to your Pim project root in order to migrate.

        If you have custom code in your project, this step may raise errors in the "post-script" command.

        In this case, go to the chapter "Migrate your custom code" before running the database migration.

8. Then you can migrate your database using:

    ```
     php app/console doctrine:migration:migrate --env=prod
    ```

9. Then, generate JS translations and re-generate the PIM assets:

    ```
     rm -rf $PIM_DIR/web/js/translation/
     php app/console pim:installer:assets
    ```

10. If you use MongoDB 2.6, you can benefit from these new index types using:

    ```
     php app/console 'pim:product:ensure-mongodb-indexes'
    ```

    MongoDB 2.6 enforces several limitations, for instance, the Index Key Limit, https://docs.mongodb.com/v2.6/reference/limits/#Index-Key-Limit.

    This limitation impacts indexes, and in 1.6 we introduced a change: attributes using `text` backend type will now use `hashed` index type which supports more than 1024 chars.

    In 1.5 we only created ascendant indexes, even for `text` backend type, causing the non indexing of a too long value without any notice.

    This command will remove existing indexes from product collection and will add new indexes with relevant types.

## Migrate your custom code

Assuming you've added custom code in your standard project, the following sections will help you to migrate your code.

In the context of our Developer eXperience strategy, the v1.6 version brings a standardization of the whole Import/Export stack.

Please note that we removed deprecated classes and services. The following sections will help you to migrate your custom code.

You can find further details by visiting our technical documentation [here](http://docs.akeneo.com/latest/index.html).

### Global updates for any project

#### Update references to moved `Pim\Bundle\CatalogBundle\AttributeType\AbstractAttributeType` constants

We've extracted business properties of attribute types by introducing a `Pim\Component\Catalog\AttributeTypes`.

There is no impact for custom attribute types, except that constants have been moved from `Pim\Bundle\CatalogBundle\AttributeType\AbstractAttributeType` to `Pim\Component\Catalog\AttributeTypes`.

To detect the files impacted by this change, you can execute the following command in your project folder:
```
    grep -rl 'AbstractAttributeType::' src/*
```
Then you can replace occurrences of `Pim\Bundle\CatalogBundle\AttributeType\AbstractAttributeType` by `Pim\Component\Catalog\AttributeTypes`.

#### Update references to moved `Pim\Component\Catalog` business classes

To clean the code API, we continued our effort to extract PIM business classes from the Catalog Bundle to the Catalog component.

Please execute the following commands in your project folder to update the references you may have to these classes:
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
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Normalizer\\Structured\\ReferenceDataNormalizer/Pim\\Component\\ReferenceData\\Normalizer\\Structured\\ReferenceDataNormalizer/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Denormalizer\\Structured\\ProductValue\\ReferenceDataDenormalizer/Pim\\Component\\ReferenceData\\Denormalizer\\Structured\\ProductValue\\ReferenceDataDenormalizer/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Denormalizer\\Structured\\ProductValue\\ReferenceDataCollectionDenormalizer/Pim\\Component\\ReferenceData\\Denormalizer\\Structured\\ProductValue\\ReferenceDataCollectionDenormalizer/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Normalizer\\Structured\\JobInstanceNormalizer/Akeneo\\Component\\Batch\\Normalizer\\Structured\\JobInstanceNormalizer/g'
    find ./src -type f -print0 | xargs -0 sed -i 's/pim_catalog\.event_subscriber\.resolve_target_repository/akeneo_storage_utils\.event_subscriber\.resolve_target_repository/g'
    find ./src -type f -print0 | xargs -0 sed -i 's/pim_catalog\.doctrine\.smart_manager_registry/akeneo_storage_utils\.doctrine\.smart_manager_registry/g'
    find ./src -type f -print0 | xargs -0 sed -i 's/pim_catalog\.doctrine\.table_name_builder/akeneo_storage_utils\.doctrine\.table_name_builder/g'
    find ./src -type f -print0 | xargs -0 sed -i 's/pim_catalog\.factory\.referenced_collection/akeneo_storage_utils\.factory\.referenced_collection/g'
    find ./src -type f -print0 | xargs -0 sed -i 's/pim_catalog\.saver\.base_options_resolver/akeneo_storage_utils\.saver\.base_options_resolver/g'
    find ./src -type f -print0 | xargs -0 sed -i 's/pim_import_export\.factory\.job_instance/akeneo_batch\.job_instance_factory/g'
    find ./src -type f -print0 | xargs -0 sed -i 's/pim_datagrid\.datagrid\.product\.context_configurator/pim_datagrid\.datagrid\.configuration\.product\.context_configurator/g'
    find ./src -type f -print0 | xargs -0 sed -i 's/pim_datagrid\.datagrid\.product\.columns_configurator/pim_datagrid\.datagrid\.configuration\.product\.columns_configurator/g'
    find ./src -type f -print0 | xargs -0 sed -i 's/pim_datagrid\.datagrid\.product\.filters_configurator/pim_datagrid\.datagrid\.configuration\.product\.filters_configurator/g'
    find ./src -type f -print0 | xargs -0 sed -i 's/pim_datagrid\.datagrid\.product\.sorters_configurator/pim_datagrid\.datagrid\.configuration\.product\.sorters_configurator/g'
    find ./src -type f -print0 | xargs -0 sed -i 's/pim_datagrid\.datagrid\.product\.group_columns_configurator/pim_datagrid\.datagrid\.configuration\.product\.group_columns_configurator/g'
    find ./src -type f -print0 | xargs -0 sed -i 's/pim_datagrid\.datagrid\.request_parameters_extractor/pim_datagrid\.datagrid\.request\.parameters_extractor/g'
    find ./src -type f -print0 | xargs -0 sed -i 's/pim_datagrid\.datagrid\.product\.configuration_registry/pim_datagrid\.datagrid\.configuration\.product\.configuration_registry/g'
```

#### Update references to moved `PimEnterprise\Component\Catalog` business classes

In the **Enterprise Edition**, several Catalog classes have been extracted in a component.

Please execute the following command in your project folder to update the references you may have to these classes:

```
find ./src -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\CatalogBundle\\Model/PimEnterprise\\Component\\Catalog\\Model/g'
```

#### Update references to moved `PimEnterprise\Component\Security` business classes

In the **Enterprise Edition**, several Security classes have been extracted in a component.

Please execute the following commands in your project folder to update the references you may have to these classes:

```
    find ./src -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\SecurityBundle\\Model/PimEnterprise\\Component\\Security\\Model/g'
    find ./src -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\SecurityBundle\\Entity\\Repository\\AccessRepositoryInterface/PimEnterprise\\Component\\Security\\Repository\\AccessRepositoryInterface/g'
    find ./src -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\SecurityBundle\\Attributes/PimEnterprise\\Component\\Security\\Attributes/g'
```

#### Update references to moved `PimEnterprise\Component\Workflow` business classes

In the **Enterprise Edition**, several Workflow classes have been extracted in a component.

Please execute the following commands in your project folder to update the references you may have to these classes:

```
    find ./src -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\WorkflowBundle\\Model/PimEnterprise\\Component\\Workflow\\Model/g'
    find ./src -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\WorkflowBundle\\Builder\\ProductDraftBuilderInterface/PimEnterprise\\Component\\Workflow\\Builder\\ProductDraftBuilderInterface/g'
    find ./src -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\WorkflowBundle\\Event\\ProductDraftEvents/PimEnterprise\\Component\\Workflow\\Event\\ProductDraftEvents/g'
    find ./src -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\WorkflowBundle\\Event\\PublishedProductEvent/PimEnterprise\\Component\\Workflow\\Event\\PublishedProductEvent/g'
    find ./src -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\WorkflowBundle\\Event\\PublishedProductEvents/PimEnterprise\\Component\\Workflow\\Event\\PublishedProductEvents/g'
    find ./src -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\WorkflowBundle\\Factory/PimEnterprise\\Component\\Workflow\\Factory/g'
    find ./src -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\WorkflowBundle\\Normalizer/PimEnterprise\\Component\\Workflow\\Normalizer/g'
    find ./src -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\WorkflowBundle\\Applier/PimEnterprise\\Component\\Workflow\\Applier/g'
    find ./src -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\WorkflowBundle\\Repository/PimEnterprise\\Component\\Workflow\\Repository/g'
    find ./src -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\WorkflowBundle\\Helper\\SortProductValuesHelper/PimEnterprise\\Bundle\\WorkflowBundle\\Twig\\SortProductValuesHelper/g'
    find ./src -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\WorkflowBundle\\Publisher/PimEnterprise\\Component\\Workflow\\Publisher/g'
    find ./src -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\WorkflowBundle\\Connector\\Tasklet/PimEnterprise\\Component\\Workflow\\Connector\\Tasklet/g'
    find ./src -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Component\\Workflow\\Connector\\ArrayConverter\\FlatToStandard\\ProductDraft/PimEnterprise\\Component\\Workflow\\Connector\\ArrayConverter\\FlatToStandard\\ProductDraftChanges/g'
```

#### Update references to moved `PimEnterprise\Component\ProductAsset` business classes

In the **Enterprise Edition**, several ProductAsset classes have been extracted in a component.

Please execute the following commands in your project folder to update the references you may have to these classes:

```
    find ./src -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Component\\ProductAsset\\Connector\\ArrayConverter\\FlatToStandard\\Tag/PimEnterprise\\Component\\ProductAsset\\Connector\\ArrayConverter\\FlatToStandard\\Tags/g'
    find ./src -type f -print0 | xargs -0 sed -i 's/pimee_product_asset\.array_converter.flat_to_standard.tag/pimee_product_asset.array_converter.flat_to_standard.tags/g'
    find ./src -type f -print0 | xargs -0 sed -i 's/pimee_product_asset\.reader\.orm\.asset/pimee_product_asset\.reader\.database\.asset/g'
    find ./src -type f -print0 | xargs -0 sed -i 's/pimee_product_asset\.reader\.orm\.csv_channel_configuration/pimee_product_asset\.reader\.database\.channel_configuration/g'
    find ./src -type f -print0 | xargs -0 sed -i 's/pimee_product_asset\.reader\.orm\.variation/pimee_product_asset\.reader\.database\.variation/g'
    find ./src -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Component\\ProductAsset\\Connector\\Reader\\Doctrine\\AssetCategoryReader/PimEnterprise\\Component\\ProductAsset\\Connector\\Reader\\Database\\AssetCategoryReader/g'
    find ./src -type f -print0 | xargs -0 sed -i 's/pimee_connector\.reader\.file\.yaml_channel_configuration/pimee_product_asset\.reader\.file\.yaml_channel_configuration/g'
```

#### Update references to renamed `PimEnterprise\Component\CatalogRule` services

In the **Enterprise Edition**, a CatalogRule service have been renamed in a component.

Please execute the following command in your project folder to update the references you may have to these services:

```
    find ./src -type f -print0 | xargs -0 sed -i 's/pimee_catalog_rule\.reader\.doctrine\.rule_definition/pimee_catalog_rule\.reader\.database\.rule_definition/g'
```

### Updates for projects customizing Import / Export

#### Remove the reference to the removed `Akeneo\Bundle\BatchBundle\Connector\Connector` class

In v1.0, your bundle containing a custom Connector had to extend the class `Akeneo\Bundle\BatchBundle\Connector\Connector`.

It's not required anymore, and this deprecated class has been removed.

To detect the files impacted by this change, you can execute the following command in your project folder:
```
    grep -rl 'Akeneo\\Bundle\\BatchBundle\\Connector\\Connector' src/*
```

v1.5

```
namespace Acme\Bundle\MyBundle;

use Akeneo\Bundle\BatchBundle\Connector\Connector;

class AcmeMyBundle extends Connector
```

v1.6

```
namespace Acme\Bundle\MyBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class AcmeMyBundle extends Bundle
```

#### Update references to the deprecated and removed `TransformBundle` classes

We've removed the deprecated `TransformBundle`.

Some classes and services have been kept, but moved to others bundles or components. The following command helps to migrate references to these classes or services.

Flat (De)Normalizers have been moved to `VersioningBundle` and Structured ones have been to moved to `Catalog` component.

Based on a PIM standard installation, execute the following commands in your project folder:
```
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\TransformBundle\\Normalizer\\Flat/Pim\\Bundle\\VersioningBundle\\Normalizer\\Flat/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\TransformBundle\\Denormalizer\\Flat/Pim\\Bundle\\VersioningBundle\\Denormalizer\\Flat/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\TransformBundle\\Normalizer\\Structured/Pim\\Component\\Catalog\\Normalizer\\Structured/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\TransformBundle\\Denormalizer\\Structured/Pim\\Component\\Catalog\\Denormalizer\\Structured/g'
```

Extra classes have been moved, but the rest of the `TransformBundle` has been removed.

Based on a PIM standard installation, execute the following commands in your project folder:
```
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\TransformBundle\\Encoder/Pim\\Component\\Connector\\Encoder/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\TransformBundle\\DependencyInjection\\Compiler\\SerializerPass/Pim\\Bundle\\CatalogBundle\\DependencyInjection\\Compiler\\RegisterSerializerPass/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\TransformBundle\\Converter/Pim\\Component\\Catalog\\Converter/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/pim_transform.converter.metric/pim_catalog.converter.metric/g'
```

Now you can try to detect if you are still using removed classes,
```
    grep -rl 'TransformBundle' src/*
```

And if you are still using removed services,
```
    grep -rl 'pim_transform' src/*
```

If any line is raised by these commands, you'll need to replace the use by a more recent class or service.

#### Update references to the deprecated and removed `BaseConnectorBundle` classes

We've removed the deprecated `BaseConnectorBundle`.

Some classes and services have been kept, but moved to others bundles or components. The following commands help to migrate references to these classes or services.

```
    find ./src/ -type f -print0 | xargs -0 sed -i 's/pim_base_connector\.reader\.file\.yaml/pim_connector\.reader\.file\.yaml/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\BaseConnectorBundle\\Reader\\File\\YamlReader/Pim\\Component\\Connector\\Reader\\File\\Yaml\\Reader/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/pim_base_connector\.reader\.orm\.family/pim_connector\.reader\.database\.family/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/pim_base_connector\.reader\.orm\.attribute/pim_connector\.reader\.database\.attribute/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/pim_base_connector\.reader\.orm\.association_type/pim_connector\.reader\.database\.association_type/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/pim_base_connector\.reader\.orm\.attribute_option/pim_connector\.reader\.database\.attribute_option/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/pim_base_connector\.reader\.orm\.category/pim_connector\.reader\.database\.category/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/pim_base_connector\.reader\.repository\.group/pim_connector\.reader\.database\.group/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/pim_base_connector\.reader\.repository\.variant_group/pim_connector\.reader\.database\.variant_group/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\BaseConnectorBundle\\Processor\\Normalization\\VariantGroupProcessor/Pim\\Component\\Connector\\Processor\\Normalization\\VariantGroupProcessor/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\BaseConnectorBundle\\DependencyInjection\\Compiler\\RegisterArchiversPass/Pim\\Bundle\\ConnectorBundle\\DependencyInjection\\Compiler\\RegisterArchiversPass/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\BaseConnectorBundle\\EventListener\\/Pim\\Bundle\\ConnectorBundle\\EventListener\\/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\BaseConnectorBundle\\Archiver\\/Pim\\Component\\Connector\\Archiver\\/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\BaseConnectorBundle\\Validator\\Constraints\\/Pim\\Component\\Connector\\Validator\\Constraints\/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/pim_base_connector\.archiver\.file_writer_archiver/pim_connector\.archiver\.file_writer_archiver/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/pim_base_connector\.event_listener\.archivist/pim_connector\.event_listener\.archivist/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/pim_base_connector\.archiver/pim_connector\.archiver/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/pim_base_connector\.archiver\.invalid_item_csv_archiver/pim_connector\.archiver\.invalid_item_csv_archiver/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/pim_base_connector\.archiver\.file_reader_archiver/pim_connector\.archiver\.file_reader_archiver/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/pim_base_connector\.factory\.zip_filesystem/pim_connector\.factory\.zip_filesystem/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/pim_base_connector\.archiver\.archivable_file_writer_archiver/pim_connector\.archiver\.archivable_file_writer_archiver/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/pim_base_connector\.validator\.constraints\.channel_validator/pim_connector\.validator\.constraints\.channel_validator/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\ImportExportBundle\\Form\\Type\\JobInstanceType/Pim\\Bundle\\ImportExportBundle\\Form\\Type\\JobInstanceFormType/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\BaseConnectorBundle\\Processor\\ProductToFlatArrayProcessor/Pim\\Component\\Connector\\Processor\\Normalization\\ProductProcessor/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/pim_base_connector\.processor\.product_to_flat_array/pim_connector\.processor\.normalization\.product/g'
```

Then you can try to detect if you are still using removed classes,
```
    grep -rl 'BaseConnectorBundle' src/*
```

And if you are still using removed services,
```
    grep -rl 'pim_base_connector' src/*
```

If any line is raised by these commands, you need to replace the use statement by a more recent class or service.

#### Update references to the standardized `Pim/Component/Connector` classes

In v1.6, we standardized the naming and behavior of our Readers, Processors and Writers.

These classes were previously spread in TransformBundle, BaseConnectorBundle and Connector component. We grouped them in the Connector component.

The array converter services allow to convert from flat data (e.g. CSV) to a standard format.

The call to this conversion has been moved from processors to readers, in order to allow you to implement only readers when customizing imports.

Now the processors only accept the standard format, and the naming has changed from `pim_connector.processor.normalization.<class>.flat` to `pim_connector.processor.normalization.<class>`.

The YAML Reader has also been moved from BatchBundle to ConnectorBundle, the CSV Readers have been moved to specific folders, and the naming changed too.

If you use standard Akeneo PIM processor and reader services in your custom imports or exports, please execute the following commands in your project folder:
```
    find ./src/ -type f -print0 | xargs -0 sed -i 's/pim_connector\.processor\.normalization\.\(.*\)\.flat/pim_connector\.processor\.normalization\.\1/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Connector\\Reader\\File\\CsvReader/Pim\\Component\\Connector\\Reader\\File\\Csv\\Reader/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Connector\\Reader\\File\\CsvProductReader/Pim\\Component\\Connector\\Reader\\File\\Csv\\ProductReader/g'
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
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Connector\\Normalizer\\Flat\\ReferenceDataNormalizer/Pim\\Component\\ReferenceData\\Normalizer\\Flat\\ReferenceDataNormalizer/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Connector\\Denormalizer\\Flat\\ProductValue\\ReferenceDataDenormalizer/Pim\\Component\\ReferenceData\\Denormalizer\\Flat\\ProductValue\\ReferenceDataDenormalizer/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Connector\\Denormalizer\\Flat\\ProductValue\\ReferenceDataCollectionDenormalizer/Pim\\Component\\ReferenceData\\Denormalizer\\Flat\\ProductValue\\ReferenceDataCollectionDenormalizer/g'
```

#### Change the definition of Batch Jobs services to replace `batch_jobs.yml`

Since the v1.0, the definition of Jobs was done through a dedicated batch_jobs.yml configuration file.

This file was automatically found and parsed by a dedicated compiler pass scanning all bundles.

The file content was very strict, was less standard and upgradeable than it is now.

Indeed, the v1.6 allows to use standard Symfony services for batch jobs services configuration.

This change,
- Avoids to instantiate all jobs and steps on each HTTP / CLI call
- Allows to easily create a new kind of Job (no need of JobFactory)
- Allows to easily create a new kind of Step (no need of StepFactory)
- Allows to make item step immutable (using constructor and no setters for reader, processor, writer, etc)
- Removes the need of systematic Step + StepElement, a custom Step can now embed its own logic
- Removes magic by using a standard Symfony way to declare services

In v1.5, the batch_jobs.yml contains,
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

In v1.6, we declare a standard "jobs.yml" services file,
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
            - '%pim_connector.job_name.csv_attribute_import%' # job name
            - '@event_dispatcher'
            - '@akeneo_batch.job_repository'
            -
                - '@pim_connector.step.charset_validator'
                - '@pim_connector.step.csv_attribute_import.import'
        tags:
            - { name: akeneo_batch.job, connector: %pim_connector.connector_name.csv%, type: %akeneo_batch.job.import_type% }
```

In v1.6, we also declare a standard "steps.yml" services file,
```
parameters:
    pim_connector.step.validator.class: Pim\Component\Connector\Step\ValidatorStep
    pim_connector.step.tasklet.class:   Pim\Component\Connector\Step\TaskletStep

services:
    pim_connector.step.charset_validator:
        class: %pim_connector.step.validator.class%
        arguments:
            - 'validation' # step name
            - '@event_dispatcher'
            - '@akeneo_batch.job_repository'
            - '@pim_connector.validator.item.charset_validator'

    pim_connector.step.csv_attribute_import.import:
        class: %pim_connector.step.item_step.class%
        arguments:
            - 'import' # step name
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

You can list the batch_jobs.yml files you need to migrate by using the following command,
```
    find ./src/ -name batch_jobs.yml
```

Once your `batch_jobs.yml` files replaced by standard services files, you may encounter other issues that we'll describe in upcoming sections.

#### Remove the reference to `Akeneo\Component\Batch\Item\AbstractConfigurableStepElement`

This legacy abstract class has been removed.

Now our StepElement, for instance, Reader, Processor, Writer only implement the relevant `Akeneo\Component\Batch\Item\ItemReaderInterface`, `Akeneo\Component\Batch\Item\ItemProcessorInterface` or `Akeneo\Component\Batch\Item\ItemWriterInterface`.

To detect your classes impacted by this change, you can execute the following command in your project folder:
```
    grep -rl 'AbstractConfigurableStepElement' src/*
```

v1.5
```
namespace Acme\Bundle\MyBundle\Processor;

use Akeneo\Component\Batch\Item\AbstractConfigurableStepElement;
use Akeneo\Component\Batch\Item\ItemProcessorInterface;

class MyFamilyProcessor extends AbstractConfigurableStepElement implements ItemProcessorInterface
```

v1.6
```
namespace Acme\Bundle\MyBundle\Processor;

use Akeneo\Component\Batch\Item\ItemProcessorInterface;

class MyFamilyProcessor implements ItemProcessorInterface
```

If you declare a `public function initialize()` method in your StepElement, you need to implement `Akeneo\Component\Batch\Item\InitializableInterface`.

If you declare a `public function flush()` method in your StepElement, you need to implement `Akeneo\Component\Batch\Item\FlushableInterface`

Before the 1.6, the call of these `initialize()` and `flush()` methods was ensured by a `method_exists()` call in the `Akeneo\Component\Batch\Step\ItemStep`.

#### Add JobParameters providers to configure your Job execution

The v1.6 introduces the notion of JobParameters to provide runtime parameters for your Job execution.

Before the v1.6, each StepElement, as a Reader, Processor, Writer had to,
 - declare its parameters and related default values
 - declare the validation for its parameters
 - declare the configuration of the form for each parameter

This configuration was extracted from each StepElement and aggregated to configure a whole Job.

For instance, in the deprecated ProductProcessor,
```
namespace Pim\Bundle\BaseConnectorBundle\Processor;

use Akeneo\Component\Batch\Model\StepExecution;

class ProductProcessor extends TransformerProcessor
{
    // define a $enabled runtime parameter and its default value
    protected $enabled = true;
    // [...]
    // allow to update this parameter
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;
    }
    // allow to fetch this parameter
    public function isEnabled()
    {
        return $this->enabled;
    }
    // [...]
    // configure the UI for this parameter
    public function getConfigurationFields()
    {
        return [
            'enabled' => [
                'type'    => 'switch',
                'options' => [
                    'label' => 'pim_base_connector.import.enabled.label',
                    'help'  => 'pim_base_connector.import.enabled.help'
                ]
            ],
            // [...]
        ];
    }
    // [...]
}
```

In the v1.6, these 3 different concerns have been extracted to be able to provide more robust and reusable batch components.

You need to define a `DefaultValuesProviderInterface` and a `ConstraintCollectionProviderInterface` to be able to run your Job.

You'll find an example in the doc  https://docs.akeneo.com/1.6/cookbook/import_export/create-connector.html#configure-our-jobparameters

Case 1 - You wrote a custom import (or export) which uses the same parameters as a default import (or export).

You can re-use existing classes by declaring your own services.

For instance, if you have a custom csv export with a job named "my_custom_csv_export" using the same parameters as our simple csv export:

```
    # declare a default values provider:
    my_connector.job.job_parameters.default_values_provider.my_custom_csv_export:
        class: '%pim_connector.job.job_parameters.default_values_provider.simple_csv_export.class%'
        arguments:
            -
                - 'my_custom_csv_export' # your job name
        tags:
            - { name: akeneo_batch.job.job_parameters.default_values_provider }

    # declare a constraint collection provider:
    my_connector.job.job_parameters.constraint_collection_provider.my_custom_csv_export:
        class: '%pim_connector.job.job_parameters.constraint_collection_provider.simple_csv_export.class%'
        arguments:
            -
                - 'my_custom_csv_export' # your job name
        tags:
            - { name: akeneo_batch.job.job_parameters.constraint_collection_provider }
```

Case 2 - You wrote a custom import (or export) which needs default parameters and a few extra runtime parameters.

You can take inspiration of the native csv product export which uses the simple csv export parameters and add its own parameters.

For instance, for the csv product export `DefaultValuesProviderInterface` :

```
    pim_connector.job.job_parameters.default_values_provider.product_csv_export:
        class: '%pim_connector.job.job_parameters.default_values_provider.product_csv_export.class%'
        arguments:
            - '@pim_connector.job.job_parameters.default_values_provider.simple_csv_export' # injects the simple provider
            -
                - 'csv_product_export' # job name
        tags:
            - { name: akeneo_batch.job.job_parameters.default_values_provider }
```

Then the class calls the simple provider and adds its own parameters :

```
namespace Pim\Component\Connector\Job\JobParameters\DefaultValuesProvider;

use Akeneo\Component\Batch\Job\JobInterface;
use Akeneo\Component\Batch\Job\JobParameters\DefaultValuesProviderInterface;
use Akeneo\Component\Localization\Localizer\LocalizerInterface;

class ProductCsvExport implements DefaultValuesProviderInterface
{
    protected $simpleProvider;
    protected $supportedJobNames;

    public function __construct(DefaultValuesProviderInterface $simpleProvider, array $supportedJobNames)
    {
        $this->simpleProvider    = $simpleProvider;
        $this->supportedJobNames = $supportedJobNames;
    }

    public function getDefaultValues()
    {
        // call the simple provider to get parameters and default values
        $parameters = $this->simpleProvider->getDefaultValues();
        // add extra parameters required by our csv product export
        $parameters['decimalSeparator'] = LocalizerInterface::DEFAULT_DECIMAL_SEPARATOR;
        $parameters['dateFormat'] = LocalizerInterface::DEFAULT_DATE_FORMAT;
        $parameters['filters'] = ['data' => [], 'structure' => (object) []];
        $parameters['with_media'] = true;

        return $parameters;
    }

    public function supports(JobInterface $job)
    {
        return in_array($job->getName(), $this->supportedJobNames);
    }
}
```

The same strategy can be used for `ConstraintCollectionProviderInterface`.

Case 3 - You wrote a custom import (or export) which needs its very own runtime parameters.

You have to declare your own services and classes implementing `DefaultValuesProviderInterface` and `ConstraintCollectionProviderInterface`.

Finally, you can remove useless getters / setters and related parameters from your StepElements.

#### Add JobParameters providers to configure your Job edition

If your Job needs to be configured through the UI, you also need to define a `FormConfigurationProviderInterface`.

This class will allow to provide the configuration for each form field.

You'll find an example in the doc https://docs.akeneo.com/1.6/cookbook/import_export/create-connector.html#configure-the-ui-for-our-jobparameters

You can follow the strategy described in the previous chapter, for instance, re-use existing classes for the case 1:

```
    my_connector.job_parameters.form_configuration_provider.my_custom_csv_export:
        class: '%pim_import_export.job_parameters.form_configuration_provider.simple_csv_export.class%'
        arguments:
            -
                - 'my_custom_csv_export' # your job name
        tags:
            - { name: pim_import_export.job_parameters.form_configuration_provider }
```

Finally, you can remove useless getConfigurationFields() from your StepElements.

In 1.6, the product export has been improved and a second tab in the UI allows to configure the filters to apply on the product selection and the attributes to export.

If you wrote a custom product export, you can benefit from this new configuration by declaring the following services:

```
    # configure the view mode of your custom product export profile
    my_connector.view_element.job_profile.export.tab.job_content_show:
        parent: pim_enrich.view_element.base
        arguments:
           - 'pim_import_export.job_profile.tab.job_content'
           - '%pim_import_export.view_element.job_profile.tab.job_content.template%'
        calls:
           - [ addVisibilityChecker, ['@pim_import_export.view_element.visibility_checker.job_name', {job_names: ['my_custom_csv_export']}] ]
        tags:
           - { name: pim_enrich.view_element, type: pim_import_export_jobInstance.export, position: 100 }

    # configure the edit mode of your custom product export profile
    my_connector.view_element.job_profile.export.tab.job_content_edit:
        parent: pim_enrich.view_element.base
        arguments:
           - 'pim_import_export.job_profile.tab.job_content'
           - '%pim_import_export.view_element.job_profile.tab.job_content.template%'
        calls:
           - [ addVisibilityChecker, ['@pim_import_export.view_element.visibility_checker.job_name', {job_names: ['my_custom_csv_export']}] ]
        tags:
           - { name: pim_enrich.view_element, type: pim_import_export_jobInstance.export.form_tab, position: 100 }

```

#### Update access to the configuration in your custom Reader, Processor, Writer by using the JobParameters

The JobParameters allows to store the runtime parameters.

When running a job, the JobParameters is configured, validated and injected into the JobExecution.

When a StepElement is StepExecutionAware, the StepExecution is injected and allows to access the JobParameters (though the JobExecution).

v1.5
```
class ProductProcessor implements ItemProcessorInterface, StepExecutionAwareInterface
{
    // this is a runtime parameter
    protected $enabled = true;

    protected function transform($item)
    {
        $this->doSomething($this->enabled); // we get its value directly
    }
}
```

v1.6
```
class ProductProcessor implements ItemProcessorInterface, StepExecutionAwareInterface
{
    protected function transform($item)
    {
        // we get the value from a validated JobParameters
        $parameters = $this->stepExecution->getJobParameters();
        $this->doSomething($parameters->get('enabled'));
    }
}
```

#### Update your custom translation keys for Job and Step labels

We've extracted the translated labels concern from JobInterface and StepInterface.

In 1.5, you have to manually declare your titles in batch_jobs.yml for jobs and steps, for instance,

```
connector:
    name: Akeneo CSV Connector
    jobs:
        csv_product_export:
            title: pim_base_connector.jobs.csv_product_export.title
            type:  export
            steps:
                export:
                    title:     pim_base_connector.jobs.csv_product_export.export.title
                    [...]
```

Then you had to add `pim_base_connector.jobs.csv_product_export.title` and `pim_base_connector.jobs.csv_product_export.export.title` translations in your `Resources/translations/messages.yml`.

In 1.6, the labels are built based on a convention and you only have to add a translation key following this convention,
 - for a Job, batch_jobs.job_name.label
 - for a Step, batch_jobs.job_name.step_name.label

Taking our previous example, you can directly add the following translations in your `Resources/translations/messages.yml`:
```
batch_jobs.csv_product_export.label: "Product export in CSV"
batch_jobs.csv_product_export.export.label: "Product export step"
```

#### Update the configuration of your custom Product Exports

With the introduction of the ExportBuilder feature in the 1.6, a product export runtime parameters have a different format.

v1.5
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

v1.6
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

We provide doctrine migrations to handle this change in your existing data.

In case you have custom products export using the native processor, you may need to update this migration script to update your job instances.

#### Updates to access the "Content" and "General Property" tab in the export job profiles

In order to access the "Content" and "General Property" tabs for already existing user roles. An admin has to set access to those roles.

Go to the role profile and activate the following permissions situated in the "Permission" tab and the "Export Profile" section:

- "Show an export profile general properties"
- "Edit an export profile general properties"
- "Show an export profile content"
- "Edit an export profile content"

If you want to display the "Content" tab for you custom product export job profiles, you can follow the cookbook "Configure the job profile" in the documentation (https://docs.akeneo.com/1.6/cookbook/import_export/create-custom-step.html#configure-the-job-profile).

### Updates for projects adding custom Mass Edit Action

#### Update the mass edit actions services

In v1.5, mass edit actions could be declared like this:
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

In this example, we show an action for the products grid that will appear in the default group.

#### Update the mass edit backend processes

The Mass Edit backend processes are based on Batch and Connector classes.

We also need to apply the updates of the import / export chapter here, since `batch_jobs.yml file has been replaced by several standard services.

The `Pim\Bundle\EnrichBundle\Connector\Processor\AbstractProcessor` has been simplified and does not require to inject a `Pim\Component\Connector\Repository\JobConfigurationRepositoryInterface` anymore.

The `getJobConfiguration()` has been removed from the `AbstractProcessor`, and we can now access runtime parameters through `$this->stepExecution->getJobParameters()`.

To detect the files impacted by this change, you can execute the following command in your project folder:
```
    grep -rl 'Pim\\Bundle\\EnrichBundle\\Connector\\Processor\\AbstractProcessor' src/*
```

v1.5
```
class MyProcessorProcessor extends AbstractProcessor
{
      public function __construct(JobConfigurationRepositoryInterface $jobConfigurationRepo, $myArgument)
      {
          parent::__construct($jobConfigurationRepo);
          $this->myArgument = $myArgument;
      }

      public function process($product)
      {
         $configuration = $this->getJobConfiguration();
         // [...]
      }
 }
```

v1.6
```
class MyProcessorProcessor extends AbstractProcessor
{
      public function __construct($myArgument)
      {
          $this->myArgument = $myArgument;
      }

      public function process($product)
      {
          $jobParameters = $this->stepExecution->getJobParameters();
          $actions = $jobParameters->get('actions');
          // [...]
      }
}
```

## Known issues

We did our best to provide a detailed and exhaustive upgrade guide, however, we can't cover every possible customization.

If you encounter any issues or find missing part in this guide, please open a Pull Request to add your own use case in this section.
