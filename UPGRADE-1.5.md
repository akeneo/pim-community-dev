# UPGRADE FROM 1.4 to 1.5

> Please perform a backup of your database before proceeding to the migration. You can use tools like  [mysqldump](http://dev.mysql.com/doc/refman/5.1/en/mysqldump.html) and [mongodump](http://docs.mongodb.org/manual/reference/program/mongodump/).

> Please perform a backup of your codebase if you don't use any VCS.

## Oro Platform Bundles

Akeneo PIM is based on a fork of a very old beta5 version of oro/platform (2013/10).

We started the development of Akeneo PIM by actively contributing to oro/platform and by upgrading the platform each week.

The use of the platform drastically speed up our first stages to create Akeneo PIM, especially to manage users, security and to provide UI elements.

During the end of 2013, it appeared that our first stable release ETA (2014/01) was incompatible with the stable platform ETA (2014/04).

So we've created a fork to be able to stabilize, tweak and fix this version to make our application production ready.

Then we strongly focused on the Akeneo PIM development to build our first stable versions.

The gap between our forked version and the stable platform became bigger and bigger (the migration cost too).

We had customers in production and the migration would imply a lot of impacts so we never upgraded the platform.

The overall strategy was to reduce dependencies to our very old beta platform to be able at some point to migrate to a stable and recent platform.

So, in Akeneo PIM 1.x versions, to reduce the dependencies, ease the maintenance and enhance performance, we dropped several old oro bundles we don't even use.

In the v1.5, we move these bundles from our fork to our main repository to ease the cleanup and make our technical stack more understandable.

## Architecture [WIP still in discussion, can evolve and change]

Once Oro bundles moved, there is the re-work strategy for v1.5.

The idea is to provide a cleaner and more understandable stack by removing override and "twin bundles".

There is the v1.4 version with Oro bundles moved in src,

```
src/
├── Acme
│   └── Bundle
│       └── AppBundle
├── Akeneo
│   ├── Bundle
│   │   ├── ClassificationBundle
│   │   ├── FileStorageBundle
│   │   └── StorageUtilsBundle
│   └── Component
│       ├── Analytics
│       ├── Classification
│       ├── Console
│       ├── FileStorage
│       └── StorageUtils
├── Oro
│   └── Bundle
│       ├── AsseticBundle           -> -
│       ├── ConfigBundle            -> -
│       ├── DataGridBundle          -> move generic classes from Pim/DataGridBundle to this namespace (or from Oro to Pim?), move specific product classes to Pim/EnrichBundle
│       ├── EntityBundle            -> [Done] - removed (DoctrineOrmMappingsPass has been extracted in Akeneo/StorageUtilsBundle)
│       ├── EntityConfigBundle      -> [Done] - removed (ServiceLinkPass has been extracted in Oro/SecurityBundle)
│       ├── DistributionBundle      -> [Done] - removed (automatic routing has been dropped and routes are explicitly declared in routing.yml)
│       ├── FilterBundle            -> ?merge to Oro/DataGridBundle?
│       ├── FormBundle              -> merge useful parts to Oro/ConfigBundle and Pim/EnrichBundle
│       ├── LocaleBundle            -> merge useful parts to Pim/LocalizationBundle
│       ├── NavigationBundle        -> merge with Pim/NavigationBundle during navigation re-work project
│       ├── RequireJSBundle         -> ?try to use last version? do PRs to backport fixes?
│       ├── SecurityBundle          -> -
│       ├── TranslationBundle       -> merge useful parts to Pim/LocalizationBundle
│       ├── UIBundle                -> merge useful parts to Pim/UIBundle
│       └── UserBundle              -> merge useful parts to Pim/UserBundle
└── Pim
    ├── Bundle
    │   ├── AnalyticsBundle         -> -
    │   ├── BaseConnectorBundle     -> could be totally deprecated (but kept with tests) once exports re-worked in ConnectorBundle
    │   ├── CatalogBundle           -> we continue to extract business code to relevant components
    │   ├── CommentBundle           -> could be splitted in a Akeneo component + bundle (does not rely on PIM domain)
    │   ├── ConnectorBundle         -> could welcome new classes if we re-work export
    │   ├── DashboardBundle         -> -
    │   ├── DataGridBundle          -> move generic classes to Oro/DataGridBundle, move specific related to product to Pim/EnrichBundle
    │   ├── EnrichBundle            -> could contain all Akeneo PIM UI (except independent bundles as workflow, pam)
    │   ├── FilterBundle            -> merge in Oro/DataGridBundle or Pim/DataGridBundle
    │   ├── ImportExportBundle      -> could be merged to EnrichBundle it mainly contain UI related classes
    │   ├── InstallerBundle         -> -
    │   ├── JsFormValidationBundle  -> -
    │   ├── NavigationBundle        -> merge from Oro/NavigationBundle during navigation re-work project
    │   ├── NotificationBundle      -> bit re-worked during the collaborative workflow epic
    │   ├── PdfGeneratorBundle      -> -
    │   ├── ReferenceDataBundle     -> -
    │   ├── TransformBundle         -> re-work normalizer/denormalizer part and deprecate all other parts (related to deprecated import system)
    │   ├── TranslationBundle       -> could be deprecated after copying useful classes in new Localization component + bundle (in a BC way)
    │   ├── UIBundle                -> mainly used for js/css third party libraries, we should load them via a dedicated package manager
    │   ├── UserBundle              -> merge used parts of Oro/UserBundle to Pim/UserBundle
    │   ├── VersioningBundle        -> -
    │   └── WebServiceBundle        -> -
    └── Component
        ├── Catalog
        ├── Connector
        └── ReferenceData
```

Ideally, the 1.5 version could be the following, (depending on the amount of tech cleaning we manage to do),

```
src/
├── Acme
│   └── Bundle
│       └── AppBundle               Dev examples for product value override and specific reference data
├── Akeneo
│   ├── Bundle
│   │   ├── ClassificationBundle    Doctrine generic implementations for classification trees and related DI
│   │   ├── FileStorageBundle       Doctrine and Symfony implementations for files storage
│   │   └── StorageUtilsBundle      Doctrine implementations for storage access (remover, saver, updater, repositories, etc)
│   └── Component
│       ├── Analytics               Data collector interfaces to aggregate statistics
│       ├── Classification          Generic classes for classification trees (implemented by product categories and asset categories) and tags
│       ├── Console                 Utility classes to execute commands
│       ├── FileStorage             Business interfaces and classes to handle files storage with filesystem abstraction
│       └── StorageUtils            Business interfaces and classes to abstract storage access (remover, saver, updater, repositories, etc)
├── Oro
│   └── Bundle
│       ├── AsseticBundle           CSS assets management, assets can be distributed across several bundles
│       ├── ConfigBundle            Application configuration, other bundles can declare their own configurations
│       ├── DataGridBundle          Generic interfaces and classes to implement Datagrid
│       ├── RequireJSBundle         Generates a require.js config file for a project, minify and merge all JS-file into one resources
│       └── SecurityBundle          Advanced ACL management
└── Pim
    ├── Bundle
    │   ├── AnalyticsBundle         Implementations of data collectors to provide PIM statistics
    │   ├── CatalogBundle           PIM business classes (models, model updaters, storage access, validation, etc)
    │   ├── CommentBundle           Generic comment implementations, used by products
    │   ├── ConnectorBundle         New (introduced in v1.5) classes to integrate import system with Symfony and Doctrine
    │   ├── DashboardBundle         Dashboard and widget system
    │   ├── EnrichBundle            Symfony and Doctrine glue classes to provide User Interface
    │   ├── InstallerBundle         Installation system of the PIM
    │   ├── JsFormValidationBundle  Override of APY/JsFormValidationBundle to provide javascript validation for dynamic models
    │   ├── LocalizationBundle      Symfony implementation of localization features
    │   ├── NotificationBundle      Implementation of a centralized PIM notifications system
    │   ├── PdfGeneratorBundle      Classes to generate a PDF datasheet for a product
    │   ├── ReferenceDataBundle     Classes to provide reference data support for PIM features
    │   ├── TransformBundle         Handles normalization and denormalization of PIM models
    │   ├── TranslationBundle       Doctrine and Symfony classes to manage localizable models
    │   ├── UserBundle              Interfaces and classes to manage Users, Roles and Groups
    │   ├── VersioningBundle        Versioning implementation for the PIM domain models
    │   └── WebServiceBundle        Very light Web Rest API (json format)
    └── Component
        ├── Catalog                 New (introduced v1.4) PIM domain interfaces and classes, most of them still remain in CatalogBundle for legacy and backward compatibility reasons
        ├── Connector               New (introduced v1.4) PIM business interfaces and classes to handle data import
        ├── Localization            New (introduced v1.5) business interfaces and classes to handle data localization
        └── ReferenceData           New (introduced v1.4) Interfaces and classes related to collection of reference models and the product integration
```

## ConnectorBundle & BaseConnectorBundle

In 1.4, we re-worked the PIM import system and we've depreciated the old import system.

The new system has been implemented in Connector component and ConnectorBundle and we kept the old system in BaseConnectorBundle (deprecated for imports, still in use for exports).

In 1.5, for performance reason, we re-worked the export writer part, we introduced new classes and services in Connector component and ConnectorBundle.

Old export writer classes and services are still in BaseConnectorBundle and are marked as deprecated.

The strategy is to be able to depreciate entirely the BaseConnectorBundle once we'll have re-worked remaining export parts (mainly reader and processor).

## Catalog Bundle & Component

We've extracted model interfaces as ProductInterface from the Catalog bundle to the Catalog component.

We keep the old interfaces as deprecated to avoid a large BC Break.

This allow us to continue to split our classes in components and bundles to separate business code and framework glue.

To upgrade run following 'sed' commands in your project and change you app/config.yml if you do mapping overrides:

v1.4
```
akeneo_storage_utils:
    mapping_overrides:
        -
            original: Pim\Bundle\CatalogBundle\Model\ProductValue
            override: Acme\Bundle\AppBundle\Model\ProductValue
```

v1.5
```
akeneo_storage_utils:
    mapping_overrides:
        -
            original: Pim\Component\Catalog\Model\ProductValue
            override: Acme\Bundle\AppBundle\Model\ProductValue
```

## Partially fix BC breaks

If you have a standard installation with some custom code inside, the following command allows to update changed services or use statements.

**It does not cover all possible BC breaks, as the changes of arguments of a service, consider using this script on versioned files to be able to check the changes with a `git diff` for instance.**

Based on a PIM standard installation, execute the following command in your project folder:

```
    find ./src/ -type f -print0 | xargs -0 sed -i 's/EntityBundle\\DependencyInjection\\Compiler\\DoctrineOrmMappingsPass/StorageUtilsBundle\\DependencyInjection\\Compiler\\DoctrineOrmMappingsPass/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\BaseConnectorBundle\\Writer\\File\\ArchivableWriterInterface/Pim\\Component\\Connector\\Writer\\File\\ArchivableWriterInterface/g'

    find ./src/ -type f -print0 | xargs -0 sed -i 's/Bundle\\CatalogBundle\\Model\\AbstractAssociation/Component\\Catalog\\Model\\AbstractAssociation/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Bundle\\CatalogBundle\\Model\\AbstractAttribute/Component\\Catalog\\Model\\AbstractAttribute/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Bundle\\CatalogBundle\\Model\\AbstractCompleteness/Component\\Catalog\\Model\\AbstractCompleteness/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Bundle\\CatalogBundle\\Model\\AbstractMetric/Component\\Catalog\\Model\\AbstractMetric/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Bundle\\CatalogBundle\\Model\\AbstractProduct/Component\\Catalog\\Model\\AbstractProduct/g'
    # TODO: deprecated, should be removed
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Bundle\\CatalogBundle\\Model\\AbstractProductMedia/Component\\Catalog\\Model\\AbstractProductMedia/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Bundle\\CatalogBundle\\Model\\AbstractProductPrice/Component\\Catalog\\Model\\AbstractProductPrice/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Bundle\\CatalogBundle\\Model\\AbstractProductValue/Component\\Catalog\\Model\\AbstractProductValue/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Bundle\\CatalogBundle\\Model\\Association/Component\\Catalog\\Model\\Association/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Bundle\\CatalogBundle\\Model\\AssociationInterface/Component\\Catalog\\Model\\AssociationInterface/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Bundle\\CatalogBundle\\Model\\AssociationTypeInterface/Component\\Catalog\\Model\\AssociationTypeInterface/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Bundle\\CatalogBundle\\Model\\AttributeGroupInterface/Component\\Catalog\\Model\\AttributeGroupInterface/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Bundle\\CatalogBundle\\Model\\AttributeInterface/Component\\Catalog\\Model\\AttributeInterface/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Bundle\\CatalogBundle\\Model\\AttributeOptionInterface/Component\\Catalog\\Model\\AttributeOptionInterface/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Bundle\\CatalogBundle\\Model\\AttributeOptionValueInterface/Component\\Catalog\\Model\\AttributeOptionValueInterface/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Bundle\\CatalogBundle\\Model\\AttributeRequirementInterface/Component\\Catalog\\Model\\AttributeRequirementInterface/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Bundle\\CatalogBundle\\Model\\CategoryInterface/Component\\Catalog\\Model\\CategoryInterface/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Bundle\\CatalogBundle\\Model\\ChannelInterface/Component\\Catalog\\Model\\ChannelInterface/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Bundle\\CatalogBundle\\Model\\Completeness/Component\\Catalog\\Model\\Completeness/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Bundle\\CatalogBundle\\Model\\CompletenessInterface/Component\\Catalog\\Model\\CompletenessInterface/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Bundle\\CatalogBundle\\Model\\CurrencyInterface/Component\\Catalog\\Model\\CurrencyInterface/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Bundle\\CatalogBundle\\Model\\FamilyInterface/Component\\Catalog\\Model\\FamilyInterface/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Bundle\\CatalogBundle\\Model\\GroupInterface/Component\\Catalog\\Model\\GroupInterface/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Bundle\\CatalogBundle\\Model\\GroupTypeInterface/Component\\Catalog\\Model\\GroupTypeInterface/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Bundle\\CatalogBundle\\Model\\LocaleInterface/Component\\Catalog\\Model\\LocaleInterface/g'
    # TODO should not be moved here?
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Bundle\\CatalogBundle\\Model\\LocalizableInterface/Component\\Catalog\\Model\\LocalizableInterface/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Bundle\\CatalogBundle\\Model\\Metric/Component\\Catalog\\Model\\Metric/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Bundle\\CatalogBundle\\Model\\MetricInterface/Component\\Catalog\\Model\\MetricInterface/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Bundle\\CatalogBundle\\Model\\Product/Component\\Catalog\\Model\\Product/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Bundle\\CatalogBundle\\Model\\ProductInterface/Component\\Catalog\\Model\\ProductInterface/g'
    # TODO: deprecated, should be removed
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Bundle\\CatalogBundle\\Model\\ProductMedia/Component\\Catalog\\Model\\ProductMedia/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Bundle\\CatalogBundle\\Model\\ProductMediaInterface/Component\\Catalog\\Model\\ProductMediaInterface/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Bundle\\CatalogBundle\\Model\\ProductPrice/Component\\Catalog\\Model\\ProductPrice/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Bundle\\CatalogBundle\\Model\\ProductPriceInterface/Component\\Catalog\\Model\\ProductPriceInterface/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Bundle\\CatalogBundle\\Model\\ProductTemplateInterface/Component\\Catalog\\Model\\ProductTemplateInterface/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Bundle\\CatalogBundle\\Model\\ProductValue/Component\\Catalog\\Model\\ProductValue/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\Model\\ProductValueInterface/Pim\\Component\\Catalog\\Model\\ProductValueInterface/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Bundle\\CatalogBundle\\Model\\ReferableInterface/Component\\Catalog\\Model\\ReferableInterface/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Bundle\\CatalogBundle\\Model\\ScopableInterface/Component\\Catalog\\Model\\ScopableInterface/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Bundle\\CatalogBundle\\Model\\TimestampableInterface/Component\\Catalog\\Model\\TimestampableInterface/g'
    # TODO: should not be moved!?
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Bundle\\CatalogBundle\\Model\\AvailableAttributes/Component\\Catalog\\Model\\AvailableAttributes/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Bundle\\CatalogBundle\\Model\\ChosableInterface/Component\\Catalog\\Model\\ChosableInterface/g'
    # TODO END: should not be moved!?
```
