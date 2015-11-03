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
    │   ├── BaseConnectorBundle     -> could be totally deprecated (but kept with tests) if exports are re-worked in ConnectorBundle
    │   ├── CatalogBundle           -> ?
    │   ├── CommentBundle           -> split in a Akeneo component + bundle (does not rely on PIM domain)
    │   ├── ConnectorBundle         -> could welcome new classes if we re-work export
    │   ├── DashboardBundle         -> -
    │   ├── DataGridBundle          -> move generic classes to Oro/DataGridBundle, move specific related to product to Pim/EnrichBundle
    │   ├── EnrichBundle            -> ?could contain all Akeneo PIM UI (except independent bundles as workflow, pam)?
    │   ├── FilterBundle            -> ?merge in Oro/DataGridBundle?
    │   ├── ImportExportBundle      -> ?should be merged to EnrichBundle?
    │   ├── InstallerBundle         -> -
    │   ├── JsFormValidationBundle  -> -
    │   ├── NavigationBundle        -> merge from Oro/NavigationBundle during navigation re-work project
    │   ├── NotificationBundle      -> bit re-worked during the collaborative workflow epic
    │   ├── PdfGeneratorBundle      -> -
    │   ├── ReferenceDataBundle     -> -
    │   ├── TransformBundle         -> ?re-work normalizer/denormalizer part and drop/deprecate other parts?
    │   ├── TranslationBundle       -> could be deprecated after copying useful classes in new Localization component + bundle (in a BC way)
    │   ├── UIBundle                -> ?to merge to EnrichBundle? keep it only for third party libraries? we should load them via composer (or other system)
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
        ├── User                    ?Don't know yet if we'll directly extract from UserBundle(s)?
        └── ReferenceData           New (introduced v1.4) Interfaces and classes related to collection of reference models and the product integration
```

## Catalog Bundle & Component

We've extracted model interfaces as ProductInterface from the Catalog bundle to the Catalog component.

We keep the old interfaces as deprecated to avoid a large BC Break.

This allow us to continue to split our classes in components and bundles.

## Partially fix BC breaks

If you have a standard installation with some custom code inside, the following command allows to update changed services or use statements.

**It does not cover all possible BC breaks, as the changes of arguments of a service, consider using this script on versioned files to be able to check the changes with a `git diff` for instance.**

Based on a PIM standard installation, execute the following command in your project folder:

```
    find ./src/ -type f -print0 | xargs -0 sed -i 's/EntityBundle\\DependencyInjection\\Compiler\\DoctrineOrmMappingsPass/StorageUtilsBundle\\DependencyInjection\\Compiler\\DoctrineOrmMappingsPass/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Bundle\\CatalogBundle\\Model\\ProductInterface/Component\\Catalog\\Model\\ProductInterface/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Bundle\\CatalogBundle\\Model\\AttributeInterface/Component\\Catalog\\Model\\AttributeInterface/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Bundle\\CatalogBundle\\Model\\ProductValueInterface/Component\\Catalog\\Model\\ProductValueInterface/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Bundle\\CatalogBundle\\Model\\FamilyInterface/Component\\Catalog\\Model\\FamilyInterface/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Bundle\\CatalogBundle\\Model\\AttributeRequirementInterface/Component\\Catalog\\Model\\AttributeRequirementInterface/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Bundle\\CatalogBundle\\Model\\AssociationInterface/Component\\Catalog\\Model\\AssociationInterface/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Bundle\\CatalogBundle\\Model\\LocaleInterface/Component\\Catalog\\Model\\LocaleInterface/g'
```
