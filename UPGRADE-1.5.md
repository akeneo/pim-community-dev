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

Once Oro bundles moved, there is the re-work strategy proposal for v1.5.

The idea is to provide a cleaner and more understandable stack by removing override and "twin bundles".

There is the v1.4 version with Oro bundles moved in src,

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
│       ├── AsseticBundle
│       ├── ConfigBundle
│       ├── DataGridBundle          -> move generic classes from Pim/DataGridBundle to this namespace, move specific related to product to Pim/EnrichBundle
│       ├── EntityBundle            -> removed (DoctrineOrmMappingsPass has been extracted in Akeneo/StorageUtilsBundle)
│       ├── EntityConfigBundle      -> removed (ServiceLinkPass has been extracted in Oro/SecurityBundle)
│       ├── DistributionBundle      -> should be dropped by removing automatic routing
│       ├── FilterBundle            -> should be merged in Oro/DataGridBundle
│       ├── FormBundle              -> should be merged in Pim/EnrichBundle
│       ├── LocaleBundle            -> ?could be moved/merged in a new Pim or Akeneo Bundle?
│       ├── NavigationBundle        -> should be merged with Pim/NavigationBundle during navigation rework project
│       ├── RequireJSBundle
│       ├── SecurityBundle
│       ├── TranslationBundle       -> ?could be merged to Pim/TranslationBundle?
│       ├── UIBundle                -> should be merged to EnrichBundle
│       └── UserBundle              -> ?what to do with Pim/UserBundle?
└── Pim
    ├── Bundle
    │   ├── AnalyticsBundle
    │   ├── BaseConnectorBundle     -> ?could be deprecated (dropped?) once export re-worked in Connector?
    │   ├── CatalogBundle
    │   ├── CommentBundle
    │   ├── ConnectorBundle
    │   ├── DashboardBundle
    │   ├── DataGridBundle          -> move generic classes to Oro/DataGridBundle, move specific related to product to Pim/EnrichBundle
    │   ├── EnrichBundle            -> ?could contain all Akeneo PIM UI (except independent bundles as workflow, pam)?
    │   ├── FilterBundle            -> should be merged in Oro/DataGridBundle for generic classes and move to EnrichBundle for specific related to product
    │   ├── ImportExportBundle      -> should be merged to EnrichBundle
    │   ├── InstallerBundle
    │   ├── JsFormValidationBundle
    │   ├── NavigationBundle        -> should be merged to EnrichBundle?
    │   ├── NotificationBundle
    │   ├── PdfGeneratorBundle
    │   ├── ReferenceDataBundle
    │   ├── TransformBundle         -> ?could re-work normalizer/denormalizer part and drop other parts?
    │   ├── TranslationBundle
    │   ├── UIBundle                -> should be merged to EnrichBundle
    │   ├── UserBundle              -> ?what to do with Oro/UserBundle?
    │   ├── VersioningBundle
    │   └── WebServiceBundle
    └── Component
        ├── Catalog
        ├── Connector
        └── ReferenceData

The 1.5 version could be,

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
│       ├── AsseticBundle
│       ├── ConfigBundle
│       ├── DataGridBundle
│       ├── RequireJSBundle
│       └── SecurityBundle
└── Pim
    ├── Bundle
    │   ├── AnalyticsBundle
    │   ├── CatalogBundle
    │   ├── CommentBundle
    │   ├── ConnectorBundle
    │   ├── DashboardBundle
    │   ├── EnrichBundle
    │   ├── InstallerBundle
    │   ├── JsFormValidationBundle
    │   ├── LocalizationBundle
    │   ├── NotificationBundle
    │   ├── PdfGeneratorBundle
    │   ├── ReferenceDataBundle
    │   ├── TransformBundle
    │   ├── TranslationBundle
    │   ├── UserBundle
    │   ├── VersioningBundle
    │   └── WebServiceBundle
    └── Component
        ├── Catalog
        ├── Connector
        ├── User
        └── ReferenceData

## Partially fix BC breaks

If you have a standard installation with some custom code inside, the following command allows to update changed services or use statements.

**It does not cover all possible BC breaks, as the changes of arguments of a service, consider using this script on versioned files to be able to check the changes with a `git diff` for instance.**

Based on a PIM standard installation, execute the following command in your project folder:

```
    find ./src/ -type f -print0 | xargs -0 sed -i 's/EntityBundle\\DependencyInjection\\Compiler\\DoctrineOrmMappingsPass/StorageUtilsBundle\\DependencyInjection\\Compiler\\DoctrineOrmMappingsPass/g'
```
