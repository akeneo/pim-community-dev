# UPGRADE FROM 2.3 TO 3.0

## Disclaimer

> Please check that you're using Akeneo PIM v2.3.

> We're assuming that you created your project from the standard distribution.

> This documentation helps to migrate projects based on the Community Edition.

> Please perform a backup of your database before proceeding to the migration. You can use tools like [mysqldump](https://dev.mysql.com/doc/refman/5.7/en/mysqldump.html).

> Please perform a backup of your indices before proceeding to the migration. You can use Elastisearch API [_snapshot](hhttps://www.elastic.co/guide/en/elasticsearch/reference/5.6/modules-snapshots.html).

> Please perform a backup of your codebase if you don't use a VCS (Version Control System).

## Requirements

Please, see the complete [list of requirements](https://docs.akeneo.com/3.0/install_pim/manual/system_requirements/system_requirements.html) for PIM v3.0.

Please provide a server with the following requirements before proceeding to the PIM 3.0 migration. To install those requirements, you can follow the official documentations or our installation documentation on [Debian 9](https://docs.akeneo.com/3.0/install_pim/manual/system_requirements/manual_system_installation_debian9.html) or [Ubuntu 16.04](https://docs.akeneo.com/3.0/install_pim/manual/system_requirements/system_install_ubuntu_1604.html).

### PHP Version

Akeneo PIM v3.0 now expects PHP 7.2

### MySQL version

Akeneo PIM v3.0 now expects MySQL 5.7.22

### Elasticsearch version

Akeneo PIM v3.0 now expects Elasticsearch 6.5.4

### Node version

Akeneo PIM v3.O now expects Node 10.15.0

## Database charset migration

MySQL charset for Akeneo is now utf8mb4, instead of the [flawed utf8](https://www.eversql.com/mysql-utf8-vs-utf8mb4-whats-the-difference-between-utf8-and-utf8mb4/). If you have custom table, you can convert them with `ALTER TABLE my_custom_table CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci`. For Akeneo native tables, the migration scripts apply the conversion.

## The main changes of the 3.0 version

Main changes of the 3.0 are related to the code organization. In order to help the product team grow and to deliver more features, we had to reorganize the code structure. Now it is split by functional domain instead of being grouped by technical concerns. 

In a nutshell, we went from

```bash
$ tree src/ -d -L 3

src/
├── Akeneo
│   ├── Bundle
│   │   └── ...
│   ├── Component
│   │   └── ...
└── Pim
    ├── Bundle
    │   ├── AnalyticsBundle
    │   ├── ApiBundle
    │   ├── CatalogBundle
    │   ├── CatalogVolumeMonitoringBundle
    │   ├── CommentBundle
    │   ├── ConnectorBundle
    │   ├── DashboardBundle
    │   ├── DataGridBundle
    │   ├── EnrichBundle
    │   ├── FilterBundle
    │   ├── ImportExportBundle
    │   ├── InstallerBundle
    │   ├── LocalizationBundle
    │   ├── NavigationBundle
    │   ├── NotificationBundle
    │   ├── PdfGeneratorBundle
    │   ├── ReferenceDataBundle
    │   ├── UIBundle
    │   ├── UserBundle
    │   └── VersioningBundle
    └── Component
        ├── Api
        ├── Catalog
        ├── CatalogVolumeMonitoring
        ├── Connector
        ├── Enrich
        ├── ReferenceData
        └── User
```

to something like

```bash
$ tree src/ -d -L 4

src/
└── Akeneo
   ├── Channel
   │   ├── Bundle
   │   └── Component
   ├── Pim
   │   ├── Enrichment
   │   │   ├── Bundle
   │   │   └── Component
   │   └── Structure
   │       ├── Bundle
   │       └── Component
   ├── Platform
   │   ├── Bundle
   │   │   └── ...
   │   ├── Component
   │   │   └── ...
   │   └── config
   ├── Tool
   │   ├── Bundle
   │   │   └── ...
   │   └── Component
   │       └── ...
   └── UserManagement
        ├── Bundle
        └── Component
```

This change lead us to move all the classes of the PIM (`sed` commands are provided in the section _Migrate your custom code_ of this upgrade guide). It has also a small impact on the configuration files as described in the section *Migrate your standard project*.

If you want to know more about this topic, you can read the [blog post](https://medium.com/akeneo-labs/akeneo-pim-3-0-lets-tidy-up-a91d986bf5bb) we have written. You can also refer to [the definitions of each of those new folders](https://github.com/akeneo/pim-community-dev/blob/master/internal_doc/ARCHITECTURE.md#you-said-bounded-contexts).

## Migrate your standard project

1. Stop the job queue consumer daemon consumer

Before starting the migration process, stop the job queue consumer daemon and start it again only when the migration process is finished.

If you use `supervisor`, then stop your daemon as following:

    ```bash
    supervisorctl status
    # the command returns the following daemons
    # pim-queue-daemon:pim-queue-daemon_00 RUNNING    pid 4162, uptime 0:05:44

    supervisorctl stop pim-queue-daemon:pim-queue-daemon_00

    supervisorctl status
    # the daemon has been stopped
    # pim-queue-daemon:pim-queue-daemon_00 STOPPED    Jan 24 11:41 AM
    ```

Otherwise, kill your daemon:

    ```bash
    pkill -f job-queue-consumer-daemon
    ```

To give you a quick overview of the changes made to a standard project, you can check on [Github](https://github.com/akeneo/pim-enterprise-standard/compare/2.3...3.0).

2 Download the latest standard edition from the website [PIM community standard](http://www.akeneo.com/download/) and extract:

    ```bash
    wget http://download.akeneo.com/pim-community-standard-v3.0-latest.tar.gz
    tar -zxf pim-community-standard-v3.0-latest.tar.gz
    cd pim-community-standard/
    ```

3. Update the configuration files:

    An easy way to update them is to copy/paste configuration files from the latest standard edition. Normally you shouldn't have made a single change to them in your project. If it's the case, don't forget to update them with your changes.

    First, we'll consider you have a `$PIM_DIR` variable:

    ```bash
    export PIM_DIR=/path/to/your/current/pim/installation
    ```
    
    Then apply the changes:

    ```bash
    cp .env.dist $PIM_DIR/
    cp .env.dist $PIM_DIR/.env
    cp .gitignore $PIM_DIR/
    cp docker-compose.override.yml.dist $PIM_DIR/
    cp docker-compose.yml $PIM_DIR/

    cp app/PimRequirements.php $PIM_DIR/app/
    cp app/config/pim_parameters.yml $PIM_DIR/app/config/
    cp app/config/security.yml $PIM_DIR/app/config/
    cp app/config/security_test.yml $PIM_DIR/app/config/
    ```

    Or you can follow the detailed list of changes:

    * The `.env.dist`, `docker-compose.override.yml.dist`, `docker/sso_authsources.php`, `docker-compose.yml`, `.gitignore`, `app/PimRequirements.php` have completely changed or didn't exist before, it's better to copy them.


    * The configuration file `app/config/security.yml` had somes changes:

        - The user provider `oro_user` has been replaced by `pim_user`
        - The user provider ID `oro_user.security.provider` has been replaced by `pim_user.provider.user`

        v2.3.x:
        ```yaml
        providers:
            chain_provider:
                chain:
                    providers:                  [oro_user]
            oro_user:
                id:                             oro_user.security.provider
        ```

        v3.0:
        ```yaml
        providers:
            chain_provider:
                chain:
                    providers:                  [pim_user]             # This line has changed
            pim_user:                                                  # This line has changed
                id:                             pim_user.provider.user # This line has changed
        ```

        - The route `oro_user_security_check` has been replaced by `pim_user_security_check`
        - The route `oro_user_security_login` has been replaced by `pim_user_security_login`
        - The route `oro_user_security_logout` has been replaced by `pim_user_security_logout`

        v2.3.x:
        ```yaml
        main:
            pattern:                        ^/
            provider:                       chain_provider
            form_login:
                csrf_token_generator:       security.csrf.token_manager
                check_path:                 oro_user_security_check
                login_path:                 oro_user_security_login
            logout:
                path:                       oro_user_security_logout
            remember_me:
                secret:                     '%secret%'
                name:                       BAPRM
                lifetime:                   1209600   # stay logged for two weeks
            anonymous:                      false
        ```

        v3.0:
        ```yaml
        main:
            pattern:                        ^/
            provider:                       chain_provider
            form_login:
                csrf_token_generator:       security.csrf.token_manager
                check_path:                 pim_user_security_check # This line has changed
                login_path:                 pim_user_security_login # This line has changed
            logout:
                path:                       pim_user_security_logout # This line has changed
            remember_me:
                secret:                     '%secret%'
                name:                       BAPRM
                lifetime:                   1209600   # stay logged for two weeks
            anonymous:                      false
        ```

        - The User `Pim\Bundle\UserBundle\Entity\User` has moved to `Akeneo\UserManagement\Component\Model\User`

        v2.3.x:
        ```yaml
        encoders:
            Pim\Bundle\UserBundle\Entity\User: sha512
            Symfony\Component\Security\Core\User\User: plaintext

        ```

        v3.0:
        ```yaml
        encoders:
            Akeneo\UserManagement\Component\Model\User: sha512 # This line has changed
            Symfony\Component\Security\Core\User\User: plaintext
        ```
    
    * The configuration file `app/config/pim_parameters.yml` had some changes:

        v2.3.x:
        ```yaml
        elasticsearch_index_configuration_files:
            - '%pim_ce_dev_src_folder_location%/src/Pim/Bundle/CatalogBundle/Resources/elasticsearch/index_configuration.yml'
        ```

        v3.0:
        ```yaml
        elasticsearch_index_configuration_files:
            - '%pim_ce_dev_src_folder_location%/src/Akeneo/Pim/Enrichment/Bundle/Resources/elasticsearch/settings.yml'
            - '%pim_ce_dev_src_folder_location%/src/Akeneo/Pim/Enrichment/Bundle/Resources/elasticsearch/product_mapping.yml'
        ```
    
    * The configuration file `app/config/security_test.yml` had some changes:

    - The firewall provider `oro_user` has been replaced by `pim_user`

        v2.3.x:
        ```yaml
        security:
            firewalls:
                main:
                    http_basic:
                        realm: "Secured REST Area"
                    provider: oro_user
                    form_login: false
                    logout: false
                    remember_me: false
                    anonymous: true
        ```

        v3.0:
        ```yaml
        security:
            firewalls:
                main:
                    http_basic:
                        realm: "Secured REST Area"
                    provider: pim_user # This line has changed
                    form_login: false
                    logout: false
                    remember_me: false
                    anonymous: true

        ```

4. Update your **app/config/config.yml**

    An easy way to update it is to copy/paste from the latest standard edition and add your custom changes.

    ```bash
    cp app/config/config.yml $PIM_DIR/app/config
    # then add your own changes
    ```

    Or you can follow the detailed list of changes:
    
        
    * The configuration file `pim.yml` is not located in the *PimEnrichBundle* anymore:

        v2.3.x:
        ```yaml
        imports:
            - { resource: '@PimEnrichBundle/Resources/config/pim.yml' }
            - { resource: pim_parameters.yml }
            - { resource: parameters.yml }
            - { resource: security.yml }
        ```

        v3.0:
        ```yaml
        imports:
            - { resource: '../../vendor/akeneo/pim-community-dev/src/Akeneo/Platform/config/pim.yml' }
            - { resource: pim_parameters.yml }
            - { resource: parameters.yml }
            - { resource: security.yml }
        ```    
    
    * The translator now expects the language `en_US`:

        v2.3.x:
        ```yaml
        framework:
            translator:      { fallback: en }
        ```

        v3.0:
        ```yaml
        framework:
            translator:      { fallback: en_US } # This line has changed
        ```
             
    * The reference data configuration has been moved in the Pim Structure. Therefore, you must update your reference data configuration. 
    The key `pim_reference_data` is replaced by `akeneo_pim_structure.reference_data`:

        v2.3.x:
        ```yaml
        pim_reference_data:
            foo:
                class: Acme\Foo
                type: multi
        ```

        v3.0:
        ```yaml
        akeneo_pim_structure: # This line has changed
            reference_data: # This line has changed
                foo:
                    class: Acme\Foo
                    type: multi
        ```

    * The configuration key `pim_enrich.max_products_category_removal` has been removed. Please use the container parameter `max_products_category_removal` instead if needed in your bundles.
    
        
5. Update your **app/config/routing.yml**

    An easy way to update it is to copy/paste from the latest standard edition and add your custom changes.

    ```bash
    cp app/config/routing.yml $PIM_DIR/app/config
    # then add your own changes
    ```

    Or you can follow the detailed list of changes:

    * The following route configurations have been removed:
        - `pim_enrich`
        - `pim_comment`
        - `pim_localization`
        - `pim_pdf_generator`
        - `pim_reference_data`
        - `oro_user`
        
    * The following route configurations have been added:
        
        v3.0:
        ```yaml
        akeneo_channel:
            resource: "@AkeneoChannelBundle/Resources/config/routing.yml"
            prefix:   /

        akeneo_pim_structure:
            resource: "@AkeneoPimStructureBundle/Resources/config/routing.yml"

        akeneo_pim_enrichment:
            resource: "@AkeneoPimEnrichmentBundle/Resources/config/routing.yml"
        ```
        
    * The following route configurations have been updated:
        
        - oro_default
        
        v2.3.x:
        ```yaml
        oro_default:
            path:  /
            defaults:
                template:    PimEnrichBundle::index.html.twig
                _controller: FrameworkBundle:Template:template
        ```
        
        to
        
        v3.0:
        ```yaml
        oro_default:
            path:  /
            defaults:
                template:    PimUIBundle::index.html.twig
                _controller: FrameworkBundle:Template:template
        ```
    
6. Update your **app/AppKernel.php**:

    An easy way to update it is to copy/paste from the latest standard edition and add your own bundles in the `registerProjectBundles` method.

    ```bash
    cp app/AppKernel.php $PIM_DIR/app/
    # then add your own bundles
    ```
    Or you can follow the detailed list of changes:

    * The following bundles have been renamed:
        - `Pim\Bundle\FilterBundle\PimFilterBundle` now is `Oro\Bundle\PimFilterBundle\PimFilterBundle`
        - `Pim\Bundle\DataGridBundle\PimDataGridBundle` now is `Oro\Bundle\PimDataGridBundle\PimDataGridBundle`
        - `Pim\Bundle\UserBundle\PimUserBundle` now is `Akeneo\UserManagement\Bundle\PimUserBundle`
        - `Pim\Bundle\AnalyticsBundle\PimAnalyticsBundle` now is `Akeneo\Platform\Bundle\AnalyticsBundle\PimAnalyticsBundle`
        - `Pim\Bundle\DashboardBundle\PimDashboardBundle` now is `Akeneo\Platform\Bundle\DashboardBundle\PimDashboardBundle`
        - `Pim\Bundle\ImportExportBundle\PimImportExportBundle` now is `Akeneo\Platform\Bundle\ImportExportBundle\PimImportExportBundle`
        - `Pim\Bundle\InstallerBundle\PimInstallerBundle` now is `Akeneo\Platform\Bundle\InstallerBundle\PimInstallerBundle`
        - `Pim\Bundle\NotificationBundle\PimNotificationBundle` now is `Akeneo\Platform\Bundle\NotificationBundle\PimNotificationBundle`
        - `Pim\Bundle\UIBundle\PimUIBundle` now is `Akeneo\Platform\Bundle\UIBundle\PimUIBundle`
        - `Pim\Bundle\CatalogVolumeMonitoringBundle\PimCatalogVolumeMonitoringBundle` now is `Akeneo\Platform\Bundle\CatalogVolumeMonitoringBundle\PimCatalogVolumeMonitoringBundle`
        - `Pim\Bundle\VersioningBundle\PimVersioningBundle` now is `Akeneo\Tool\Bundle\VersioningBundle\AkeneoVersioningBundle`
        - `Pim\Bundle\ApiBundle\PimApiBundle` now is `Akeneo\Tool\Bundle\ApiBundle\PimApiBundle`
        - `Pim\Bundle\ConnectorBundle\PimConnectorBundle` now is `Akeneo\Tool\Bundle\ConnectorBundle\PimConnectorBundle`
        - `Akeneo\Bundle\BatchBundle\AkeneoBatchBundle` now is `Akeneo\Tool\Bundle\BatchBundle\AkeneoBatchBundle`
        - `Akeneo\Bundle\BatchQueueBundle\AkeneoBatchQueueBundle` now is `Akeneo\Tool\Bundle\BatchQueueBundle\AkeneoBatchQueueBundle`
        - `Akeneo\Bundle\BufferBundle\AkeneoBufferBundle` now is `Akeneo\Tool\Bundle\BufferBundle\AkeneoBufferBundle`
        - `Akeneo\Bundle\ClassificationBundle\AkeneoClassificationBundle` now is `Akeneo\Tool\Bundle\ClassificationBundle\AkeneoClassificationBundle`
        - `Akeneo\Bundle\ElasticsearchBundle\AkeneoElasticsearchBundle` now is `Akeneo\Tool\Bundle\ElasticsearchBundle\AkeneoElasticsearchBundle`
        - `Akeneo\Bundle\FileStorageBundle\AkeneoFileStorageBundle` now is `Akeneo\Tool\Bundle\FileStorageBundle\AkeneoFileStorageBundle`
        - `Akeneo\Bundle\MeasureBundle\AkeneoMeasureBundle` now is `Akeneo\Tool\Bundle\MeasureBundle\AkeneoMeasureBundle`
        - `Akeneo\Bundle\StorageUtilsBundle\AkeneoStorageUtilsBundle` now is `Akeneo\Tool\Bundle\StorageUtilsBundle\AkeneoStorageUtilsBundle`

    * The following bundles have been removed:
        - `Pim\Bundle\NavigationBundle\PimNavigationBundle`
        - `Pim\Bundle\CatalogBundle\PimCatalogBundle`
        - `Pim\Bundle\CommentBundle\PimCommentBundle`
        - `Pim\Bundle\EnrichBundle\PimEnrichBundle`
        - `Pim\Bundle\LocalizationBundle\PimLocalizationBundle`
        - `Pim\Bundle\PdfGeneratorBundle\PimPdfGeneratorBundle`
        - `Pim\Bundle\ReferenceDataBundle\PimReferenceDataBundle`
        - `Oro\Bundle\UserBundle\OroUserBundle`

    * The following bundles have been added (order of declaration is important):
        - `Akeneo\Channel\Bundle\AkeneoChannelBundle`
        - `Akeneo\Pim\Enrichment\Bundle\AkeneoPimEnrichmentBundle`
        - `Akeneo\Pim\Structure\Bundle\AkeneoPimStructureBundle`

7. Add the DotEnv component in all entrypoints of the application

    We introduced the DotEnv component as there are more and more deployments that use environment variables for configuration values.
    The DotEnv Symfony component provides a way to set those environment variables in a file that could be overridden by real environment variables (you should have copied the `.env.dist` to `.env` in the step 2).

    For now, the following environment variable has to be set in the `.env` file:
    
    ```bash
    AKENEO_PIM_URL=http://your.akeneo-pim.url
    ```
    
    To use it, the easiest way to update the entrypoints is to copy/paste their content from the latest standard edition and add your custom code if any.
    
    ```bash
    cp web/app.php $PIM_DIR/web/app.php
    cp web/app_dev.php $PIM_DIR/web/app_dev.php
    cp bin/console $PIM_DIR/bin/console
    ```
    
    Or you can add the following code in those entrypoints just after the `require __DIR__.'/../vendor/autoload.php'``:
    
    ```php
    <?php
    //...
 
    require __DIR__.'/../vendor/autoload.php';
 
    $envFile = __DIR__ . '/../.env';
    if (file_exists($envFile)) {
        (new Symfony\Component\Dotenv\Dotenv())->load($envFile);
    }
    ```

8. Deactivate your custom code

Before updating the dependencies and migrating your data, please deactivate all your custom bundles and configuration. This will considerably ease the migration of your data. You can disable your custom code by commenting out your custom bundles in your `AppKernel.php` file.

9. Update your dependencies:

    The easiest way to update your `composer.json` is to copy/paste from the latest standard edition and add your custom dependencies.
    
    ```bash
    cp composer.json $PIM_DIR/
    # then add your own dependencies
    ```
    
    If you don't, make sure you have updated Akeneo PIM dependencies and also that you have the following `post-update-cmd` task:
    
    ```yaml
        "post-update-cmd": [
            "@symfony-scripts",
            "Akeneo\\Platform\\Bundle\\InstallerBundle\\ComposerScripts::copyUpgradesFiles"
        ]
    ```
    
    The easiest way to update your `package.json` is to copy/paste from the latest standard edition and add your custom dependencies.
    
    ```bash
    cp package.json $PIM_DIR/
    # then add your own dependencies
    ```
    
    Now we are ready to update the backend dependencies:
    
    ```bash
    cd $PIM_DIR
    php -d memory_limit=3G composer update
    ```
    
     **This step will copy the upgrades folder from `pim-community-dev/` to your Pim project root in order to migrate.**
    If you have custom code in your project, this step may raise errors in the "post-script" command.
    In this case, go to the chapter "Migrate your custom code" before running the database migration.

    And we also have to update the frontend dependencies:
    
    ```bash
    yarn install
    ```

10. Migrate your MySQL database: 

    Please, make sure the folder `upgrades/schema/` does not contain former migration files (from PIM 2.2 to 2.3 for instance), 
    otherwise the migration command will surely not work properly.

    ```bash
    rm -rf var/cache
    bin/console doctrine:migration:migrate --env=prod
    ```

11. Migrate your Elasticsearch indices:

    In case you updated the settings of Elasticsearch (like normalizers, filters and analyzers), please make sure you properly loaded your custom settings in the [Elasticsearch configuration](https://github.com/akeneo/pim-enterprise-standard/blob/3.0/app/config/pim_parameters.yml#L58-L68).

    Same in case you have a big catalog and increased the [index.mapping.total_fields.limit](https://www.elastic.co/guide/en/elasticsearch/reference/6.5/mapping.html#mapping-limit-settings). Make sure you properly loaded your custom settings in the [Elasticsearch configuration](https://github.com/akeneo/pim-enterprise-standard/blob/3.0/app/config/pim_parameters.yml#L58-L68).

    ```bash
    php upgrades/schema/es_20190128110000_ce_update_document_type_product_and_product_model.php
    ```

12. Then re-generate the PIM assets:

    ```bash
    bin/console pim:installer:assets --clean --env=prod
    yarn run webpack
    ```

## Migrate your custom code

1. Apply the sed commands

Several classes and services have been moved or renamed. The following commands help to migrate references to them:

```bash
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Connector\\Writer\\File\\AbstractItemMediaWriter/Akeneo\\Tool\\Component\\Connector\\Writer\\File\\AbstractItemMediaWriter/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\Controller\\Rest\\MeasuresController/Akeneo\\Tool\\Bundle\\MeasureBundle\\Controller\\MeasuresController/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\Controller\\Rest\\ApiClientController/Akeneo\\UserManagement\\Bundle\\Controller\\ApiClientController/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\Connector\\Item\\MassEdit\\TemporaryFileCleaner/Akeneo\\Pim\\Enrichment\\Component\\Product\\Connector\\Item\\MassEdit\\TemporaryFileCleaner/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\Connector\\Step\\MassEditStep/Akeneo\\Pim\\Enrichment\\Component\\Product\\Connector\\Step\\MassEditStep/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\Connector\\Job\\JobParameters\\ConstraintCollectionProvider\\ProductAndProductModelQuickExport/Akeneo\\Pim\\Enrichment\\Component\\Product\\Connector\\Job\\JobParameters\\ConstraintCollectionProvider\\ProductAndProductModelQuickExport/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\Connector\\Job\\JobParameters\\ConstraintCollectionProvider\\ProductMassEdit/Akeneo\\Pim\\Enrichment\\Component\\Product\\Connector\\Job\\JobParameters\\ConstraintCollectionProvider\\ProductMassEdit/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\Connector\\Job\\JobParameters\\ConstraintCollectionProvider\\ProductQuickExport/Akeneo\\Pim\\Enrichment\\Component\\Product\\Connector\\Job\\JobParameters\\ConstraintCollectionProvider\\ProductQuickExport/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\Connector\\Job\\JobParameters\\ConstraintCollectionProvider\\SimpleMassEdit/Akeneo\\Pim\\Enrichment\\Component\\Product\\Connector\\Job\\JobParameters\\ConstraintCollectionProvider\\SimpleMassEdit/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\Connector\\Job\\JobParameters\\DefaultValuesProvider\\ProductAndProductModelMassDelete/Akeneo\\Pim\\Enrichment\\Component\\Product\\Connector\\Job\\JobParameters\\DefaultValueProvider\\ProductAndProductModelMassDelete/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\Connector\\Job\\JobParameters\\DefaultValuesProvider\\ProductMassEdit/Akeneo\\Pim\\Enrichment\\Component\\Product\\Connector\\Job\\JobParameters\\DefaultValueProvider\\ProductMassEdit/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\Connector\\Job\\JobParameters\\DefaultValuesProvider\\ProductQuickExport/Akeneo\\Pim\\Enrichment\\Component\\Product\\Connector\\Job\\JobParameters\\DefaultValueProvider\\ProductQuickExport/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\Connector\\Job\\JobParameters\\DefaultValuesProvider\\SimpleMassEdit/Akeneo\\Pim\\Enrichment\\Component\\Product\\Connector\\Job\\JobParameters\\DefaultValueProvider\\SimpleMassEdit/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\Connector\\Processor\\AbstractProcessor/Akeneo\\Pim\\Enrichment\\Component\\Product\\Connector\\Processor\\MassEdit\\AbstractProcessor/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\Connector\\Processor\\QuickExport\\ProductAndProductModelProcessor/Akeneo\\Pim\\Enrichment\\Component\\Product\\Connector\\Processor\\QuickExport\\ProductAndProductModelProcessor/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\Connector\\Processor\\QuickExport\\ProductAndProductModelProcessor/Akeneo\\Pim\\Enrichment\\Component\\Product\\Connector\\Processor\\QuickExport\\ProductAndProductModelProcessor/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\Connector\\Processor\\MassEdit\\Product\\AddAttributeValueProcessor/Akeneo\\Pim\\Enrichment\\Component\\Product\\Connector\\Processor\\MassEdit\\AddAttributeValueProcessor/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\Connector\\Processor\\MassEdit\\Product\\AddProductValueProcessor/Akeneo\\Pim\\Enrichment\\Component\\Product\\Connector\\Processor\\MassEdit\\AddProductValueProcessor/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\Connector\\Processor\\MassEdit\\Product\\AddToExistingProductModelProcessor/Akeneo\\Pim\\Enrichment\\Component\\Product\\Connector\\Processor\\MassEdit\\AddToExistingProductModelProcessor/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\Connector\\Processor\\MassEdit\\Product\\ChangeParentProcessor/Akeneo\\Pim\\Enrichment\\Component\\Product\\Connector\\Processor\\MassEdit\\ChangeParentProcessor/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\Connector\\Processor\\MassEdit\\Product\\EditAttributesProcessor/Akeneo\\Pim\\Enrichment\\Component\\Product\\Connector\\Processor\\MassEdit\\EditAttributesProcessor/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\Connector\\Processor\\MassEdit\\Product\\EditCommonAttributesProcessor/Akeneo\\Pim\\Enrichment\\Component\\Product\\Connector\\Processor\\MassEdit\\EditCommonAttributesProcessor/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\Connector\\Processor\\MassEdit\\Product\\RemoveProductValueProcessor/Akeneo\\Pim\\Enrichment\\Component\\Product\\Connector\\Processor\\MassEdit\\RemoveProductValueProcessor/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\Connector\\Processor\\MassEdit\\Product\\UpdateProductValueProcessor/Akeneo\\Pim\\Enrichment\\Component\\Product\\Connector\\Processor\\MassEdit\\UpdateProductValueProcessor/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\Connector\\Processor\\MassEdit\\Family\\SetAttributeRequirements/Akeneo\\Pim\\Structure\\Component\\Processor\\MassEdit\\SetAttributeRequirements/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\Connector\\Reader\\MassEdit\\FilteredProductAndProductModelReader/Akeneo\\Pim\\Enrichment\\Component\\Product\\Connector\\Reader\\Database\\MassEdit\\FilteredProductAndProductModelReader/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\Connector\\Reader\\MassEdit\\FilteredProductModelReader/Akeneo\\Pim\\Enrichment\\Component\\Product\\Connector\\Reader\\Database\\MassEdit\\FilteredProductModelReader/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\Connector\\Reader\\MassEdit\\FilteredProductReader/Akeneo\\Pim\\Enrichment\\Component\\Product\\Connector\\Reader\\Database\\MassEdit\\FilteredProductReader/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\Connector\\Reader\\MassEdit\\ProductAndProductModelReader/Akeneo\\Pim\\Enrichment\\Component\\Product\\Connector\\Reader\\Database\\MassEdit\\ProductAndProductModelReader/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\Connector\\Reader\\MassEdit\\FilteredFamilyReader/Akeneo\\Pim\\Structure\\Component\\Reader\\Database\\MassEdit\\FilteredFamilyReader/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\Connector\\Writer\\MassEdit\\ProductAndProductModelWriter/Akeneo\\Pim\\Enrichment\\Component\\Product\\Connector\\Writer\\Database\\MassEdit\\ProductAndProductModelWriter/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\LocalizationBundle\\Controller\\FormatController/Oro\\Bundle\\ConfigBundle\\Controller\\Rest\\FormatController/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\LocalizationBundle\\Controller\\LocaleController/Akeneo\\Platform\\Bundle\\UIBundle\\Controller\\LocaleController/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\LocalizationBundle\\Provider\\UiLocaleProvider/Akeneo\\Platform\\Bundle\\UIBundle\\UiLocaleProvider/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\LocalizationBundle\\Form\\Type\\LocaleType/Akeneo\\Platform\\Bundle\\UIBundle\\Form\\Type\\LocaleType/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\LocalizationBundle\\Twig\\AttributeExtension/Akeneo\\Platform\\Bundle\\UIBundle\\Twig\\AttributeExtension/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\LocalizationBundle\\Twig\\LocaleExtension/Akeneo\\Platform\\Bundle\\UIBundle\\Twig\\LocaleExtension/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\ReferenceDataBundle\\DataGrid\\Extension\\Sorter\\ReferenceDataSorter/Oro\\Bundle\\PimDataGridBundle\\Extension\\Sorter\\Produc\\ReferenceDataSorter/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\ReferenceDataBundle\\DataGrid\\Normalizer\\ReferenceDataCollectionNormalizer/Oro\\Bundle\\PimDataGridBundle\\Normalizer\\Product\\ReferenceDataCollectionNormalizer/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\ReferenceDataBundle\\DataGrid\\Normalizer\\ReferenceDataNormalizer/Oro\\Bundle\\PimDataGridBundle\\Normalizer\\Product\\ReferenceDataNormalizer/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\ReferenceDataBundle\\DataGrid\\Filter\\ReferenceDataFilter/Oro\\Bundle\\PimFilterBundle\\Filter\\ProductValue\\ReferenceDataFilter/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\ReferenceDataBundle\\DependencyInjection\\Compiler\\RegisterConfigurationsPass/Akeneo\\Pim\\Structure\\Bundle\\DependencyInjection\\Compiler\\RegisterReferenceDataConfigurationsPass/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\ReferenceDataBundle\\Enrich\\Provider\\EmptyValue\\ReferenceDataEmptyValueProvider/Pim\\Bundle\\EnrichBundle\\Provider\\EmptyValue\\ReferenceDataEmptyValueProvider/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\ReferenceDataBundle\\Enrich\\Provider\\Field\\ReferenceDataFieldProvider/Pim\\Bundle\\EnrichBundle\\Provider\\Field\\ReferenceDataFieldProvider/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\ReferenceDataBundle\\Enrich\\Provider\\Filter\\ReferenceDataFilterProvider/Pim\\Bundle\\EnrichBundle\\Provider\\Filter\\ReferenceDataFilterProvider/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\ReferenceDataBundle\\Doctrine\\ReferenceDataRepositoryResolver/Akeneo\\Pim\\Enrichment\\Bundle\\Doctrine\\ReferenceDataRepositoryResolver/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\ReferenceDataBundle\\Doctrine\\ORM\\RequirementChecker\\ReferenceDataUniqueCodeChecker/Akeneo\\Pim\\Structure\\Bundle\\Doctrine\\ORM\\ReferenceDataUniqueCodeChecker/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\ReferenceDataBundle\\Doctrine\\ORM\\Repository\\ReferenceDataRepository/Akeneo\\Pim\\Enrichment\\Bundle\\Doctrine\\ORM\\Repository\\ReferenceDataRepository/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\ReferenceDataBundle\\Elasticsearch\\Filter\\Attribute\\ReferenceDataFilter/Akeneo\\Pim\\Enrichment\\Bundle\\Elasticsearch\\Filter\\Attribute\\ReferenceDataFilter/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\ReferenceDataBundle\\Controller\\ConfigurationRestController/Akeneo\\Pim\\Structure\\Bundle\\Controller\\InternalApi\\ReferenceDataConfigurationRestController/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\ReferenceDataBundle\\RequirementChecker\\AbstractReferenceDataUniqueCodeChecker/Akeneo\\Pim\\Structure\\Bundle\\ReferenceData\\RequirementChecker\\AbstractReferenceDataUniqueCodeChecker/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\ReferenceDataBundle\\RequirementChecker\\CheckerInterface/Akeneo\\Pim\\Structure\\Bundle\\ReferenceData\\RequirementChecker\\CheckerInterface/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\ReferenceDataBundle\\RequirementChecker\\ReferenceDataInterfaceChecker/Akeneo\\Pim\\Structure\\Bundle\\ReferenceData\\RequirementChecker\\ReferenceDataInterfaceChecker/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\ReferenceDataBundle\\RequirementChecker\\ReferenceDataNameChecker/Akeneo\\Pim\\Structure\\Bundle\\ReferenceData\\RequirementChecker\\ReferenceDataNameChecker/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\ReferenceDataBundle\\Normalizer\\ReferenceDataConfigurationNormalizer/Akeneo\\Pim\\Structure\\Component\\Normalizer\\InternalApi\\ReferenceDataConfigurationNormalizer/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\ReferenceDataBundle\\AttributeType\\ReferenceDataSimpleSelectType/Akeneo\\Pim\\Structure\\Component\\AttributeType\\ReferenceDataSimpleSelectType/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\ReferenceDataBundle\\AttributeType\\ReferenceDataMultiSelectType/Akeneo\\Pim\\Structure\\Component\\AttributeType\\ReferenceDataMultiSelectType/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\ReferenceData\\LabelRenderer/Akeneo\\Pim\\Enrichment\\Component\\Product\\ReferenceData\\LabelRenderer/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\ReferenceData\\MethodNameGuesser/Akeneo\\Pim\\Enrichment\\Component\\Product\\ReferenceData\\MethodNameGuesser/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\ReferenceData\\ConfigurationRegistry/Akeneo\\Pim\\Structure\\Component\\ReferenceData\\ConfigurationRegistry/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\ReferenceData\\ConfigurationRegistryInterface/Akeneo\\Pim\\Structure\\Component\\ReferenceData\\ConfigurationRegistryInterface/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\ReferenceData\\Normalizer\\Indexing\\ProductValue\\ReferenceDataNormalizer/Akeneo\\Pim\\Enrichment\\Component\\Product\\Normalizer\\Indexing\\Value\\ReferenceDataNormalizer/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\ReferenceData\\Normalizer\\Indexing\\ProductValue\\ReferenceDataCollectionNormalizer/Akeneo\\Pim\\Enrichment\\Component\\Product\\Normalizer\\Indexing\\Value\\ReferenceDataCollectionNormalizer/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\ReferenceData\\Normalizer\\Flat\\ReferenceDataNormalizer/Akeneo\\Pim\\Enrichment\\Component\\Product\\Normalizer\\Versioning\\Product\\ReferenceDataNormalizer/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\ReferenceData\\Updater\\Copier\\ReferenceDataAttributeCopier/Akeneo\\Pim\\Enrichment\\Component\\Product\\Updater\\Copier\\ReferenceDataAttributeCopier/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\ReferenceData\\Updater\\Copier\\ReferenceDataCollectionAttributeCopier/Akeneo\\Pim\\Enrichment\\Component\\Product\\Updater\\Copier\\ReferenceDataCollectionAttributeCopier/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\ReferenceData\\Model\\ReferenceDataInterface/Akeneo\\Pim\\Enrichment\\Component\\Product\\Model\\ReferenceDataInterface/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\ReferenceData\\Model\\AbstractReferenceData/Akeneo\\Pim\\Enrichment\\Component\\Product\\Model\\AbstractReferenceData/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\ReferenceData\\Model\\Configuration/Akeneo\\Pim\\Structure\\Component\\Model\\ReferenceDataConfiguration/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\ReferenceData\\Model\\ConfigurationInterface/Akeneo\\Pim\\Structure\\Component\\Model\\ReferenceDataConfigurationInterface/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\ReferenceData\\Value\\ReferenceDataCollectionValue/Akeneo\\Pim\\Enrichment\\Component\\Product\\Value\\ReferenceDataCollectionValue/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\ReferenceData\\Value\\ReferenceDataCollectionValueInterface/Akeneo\\Pim\\Enrichment\\Component\\Product\\Value\\ReferenceDataCollectionValueInterface/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\ReferenceData\\Value\\ReferenceDataValue/Akeneo\\Pim\\Enrichment\\Component\\Product\\Value\\ReferenceDataValue/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\ReferenceData\\Value\\ReferenceDataValueInterface/Akeneo\\Pim\\Enrichment\\Component\\Product\\Value\\ReferenceDataValueInterface/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\ReferenceData\\Factory\\Value\\ReferenceDataValueFactory/Akeneo\\Pim\\Enrichment\\Component\\Product\\Factory\\Value\\ReferenceDataValueFactory/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\ReferenceData\\Factory\\Value\\ReferenceDataCollectionValueFactory/Akeneo\\Pim\\Enrichment\\Component\\Product\\Factory\\Value\\ReferenceDataCollectionValueFactory/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\ProductEvents/Akeneo\\Pim\\Enrichment\\Component\\Product\\ProductEvents/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\FileStorage/Akeneo\\Pim\\Enrichment\\Component\\FileStorage/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\AttributeTypes/Akeneo\\Pim\\Structure\\Component\\AttributeTypes/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\AttributeTypeInterface/Akeneo\\Pim\\Structure\\Component\\AttributeTypeInterface/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Exception\\AlreadyExistingAxisValueCombinationException/Akeneo\\Pim\\Enrichment\\Component\\Product\\Exception\\AlreadyExistingAxisValueCombinationException/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Exception\\InvalidArgumentException/Akeneo\\Pim\\Enrichment\\Component\\Product\\Exception\\InvalidArgumentException/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Exception\\InvalidAttributeException/Akeneo\\Pim\\Enrichment\\Component\\Product\\Exception\\InvalidAttributeException/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Exception\\InvalidDirectionException/Akeneo\\Pim\\Enrichment\\Component\\Product\\Exception\\InvalidDirectionException/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Exception\\InvalidOperatorException/Akeneo\\Pim\\Enrichment\\Component\\Product\\Exception\\InvalidOperatorException/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Exception\\InvalidOptionException/Akeneo\\Pim\\Enrichment\\Component\\Product\\Exception\\InvalidOptionException/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Exception\\InvalidOptionsException/Akeneo\\Pim\\Enrichment\\Component\\Product\\Exception\\InvalidOptionsException/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Exception\\MissingIdentifierException/Akeneo\\Pim\\Enrichment\\Component\\Product\\Exception\\MissingIdentifierException/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Exception\\ObjectNotFoundException/Akeneo\\Pim\\Enrichment\\Component\\Product\\Exception\\ObjectNotFoundException/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Exception\\ProductQueryException/Akeneo\\Pim\\Enrichment\\Component\\Product\\Exception\\ProductQueryException/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Exception\\UnsupportedFilterException/Akeneo\\Pim\\Enrichment\\Component\\Product\\Exception\\UnsupportedFilterException/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Localization\\Localizer\\AttributeConverter/Akeneo\\Pim\\Enrichment\\Component\\Product\\Localization\\Localizer\\AttributeConverter/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Localization\\Localizer\\AttributeConverterInterface/Akeneo\\Pim\\Enrichment\\Component\\Product\\Localization\\Localizer\\AttributeConverterInterface/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Localization\\Localizer\\LocalizerRegistry/Akeneo\\Pim\\Enrichment\\Component\\Product\\Localization\\Localizer\\LocalizerRegistry/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Localization\\Localizer\\LocalizerRegistryInterface/Akeneo\\Pim\\Enrichment\\Component\\Product\\Localization\\Localizer\\LocalizerRegistryInterface/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Localization\\Localizer\\MetricLocalizer/Akeneo\\Pim\\Enrichment\\Component\\Product\\Localization\\Localizer\\MetricLocalizer/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Localization\\Localizer\\PriceLocalizer/Akeneo\\Pim\\Enrichment\\Component\\Product\\Localization\\Localizer\\PriceLocalizer/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Localization\\Presenter\\MetricPresenter/Akeneo\\Pim\\Enrichment\\Component\\Product\\Localization\\Presenter\\MetricPresenter/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Localization\\Presenter\\PresenterRegistry/Akeneo\\Pim\\Enrichment\\Component\\Product\\Localization\\Presenter\\PresenterRegistry/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Localization\\Presenter\\PresenterRegistryInterface/Akeneo\\Pim\\Enrichment\\Component\\Product\\Localization\\Presenter\\PresenterRegistryInterface/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Localization\\Presenter\\PricesPresenter/Akeneo\\Pim\\Enrichment\\Component\\Product\\Localization\\Presenter\\PricesPresenter/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Localization\\CategoryUpdater/Akeneo\\Pim\\Enrichment\\Component\\Category\\CategoryUpdater/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Validator\\ConstraintGuesser\\BooleanGuesser/Akeneo\\Pim\\Enrichment\\Component\\Product\\Validator\\ConstraintGuesser\\BooleanGuesser/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Validator\\ConstraintGuesser\\CurrencyGuesser/Akeneo\\Pim\\Enrichment\\Component\\Product\\Validator\\ConstraintGuesser\\CurrencyGuesser/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Validator\\ConstraintGuesser\\DateGuesser/Akeneo\\Pim\\Enrichment\\Component\\Product\\Validator\\ConstraintGuesser\\DateGuesser/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Validator\\ConstraintGuesser\\EmailGuesser/Akeneo\\Pim\\Enrichment\\Component\\Product\\Validator\\ConstraintGuesser\\EmailGuesser/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Validator\\ConstraintGuesser\\FileGuesser/Akeneo\\Pim\\Enrichment\\Component\\Product\\Validator\\ConstraintGuesser\\FileGuesser/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Validator\\ConstraintGuesser\\LengthGuesser/Akeneo\\Pim\\Enrichment\\Component\\Product\\Validator\\ConstraintGuesser\\LengthGuesser/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Validator\\ConstraintGuesser\\MetricGuesser/Akeneo\\Pim\\Enrichment\\Component\\Product\\Validator\\ConstraintGuesser\\MetricGuesser/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Validator\\ConstraintGuesser\\NotBlankGuesser/Akeneo\\Pim\\Enrichment\\Component\\Product\\Validator\\ConstraintGuesser\\NotBlankGuesser/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Validator\\ConstraintGuesser\\NotDecimalGuesser/Akeneo\\Pim\\Enrichment\\Component\\Product\\Validator\\ConstraintGuesser\\NotDecimalGuesser/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Validator\\ConstraintGuesser\\NumericGuesser/Akeneo\\Pim\\Enrichment\\Component\\Product\\Validator\\ConstraintGuesser\\NumericGuesser/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Validator\\ConstraintGuesser\\PriceCollectionGuesser/Akeneo\\Pim\\Enrichment\\Component\\Product\\Validator\\ConstraintGuesser\\PriceCollectionGuesser/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Validator\\ConstraintGuesser\\RangeGuesser/Akeneo\\Pim\\Enrichment\\Component\\Product\\Validator\\ConstraintGuesser\\RangeGuesser/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Validator\\ConstraintGuesser\\RegexGuesser/Akeneo\\Pim\\Enrichment\\Component\\Product\\Validator\\ConstraintGuesser\\RegexGuesser/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Validator\\ConstraintGuesser\\StringGuesser/Akeneo\\Pim\\Enrichment\\Component\\Product\\Validator\\ConstraintGuesser\\StringGuesser/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Validator\\ConstraintGuesser\\UniqueValueGuesser/Akeneo\\Pim\\Enrichment\\Component\\Product\\Validator\\ConstraintGuesser\\UniqueValueGuesser/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Validator\\ConstraintGuesser\\UrlGuesser/Akeneo\\Pim\\Enrichment\\Component\\Product\\Validator\\ConstraintGuesser\\UrlGuesser/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Validator\\Constraints\\Product\\UniqueProductEntity/Akeneo\\Pim\\Enrichment\\Component\\Product\\Validator\\Constraints\\Product\\UniqueProductEntity/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Validator\\Constraints\\Product\\UniqueProductEntityValidator/Akeneo\\Pim\\Enrichment\\Component\\Product\\Validator\\Constraints\\Product\\UniqueProductEntityValidator/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Validator\\Constraints\\Product\\UniqueProductModelEntity/Akeneo\\Pim\\Enrichment\\Component\\Product\\Validator\\Constraints\\Product\\UniqueProductModelEntity/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Validator\\Constraints\\Product\\UniqueProductModelEntityValidator/Akeneo\\Pim\\Enrichment\\Component\\Product\\Validator\\Constraints\\Product\\UniqueProductModelEntityValidator/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Validator\\Constraints\\Boolean/Akeneo\\Pim\\Enrichment\\Component\\Product\\Validator\\Constraints\\Boolean/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Validator\\Constraints\\BooleanValidator/Akeneo\\Pim\\Enrichment\\Component\\Product\\Validator\\Constraints\\BooleanValidator/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Validator\\Constraints\\Channel/Akeneo\\Pim\\Enrichment\\Component\\Product\\Validator\\Constraints\\Channel/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Validator\\Constraints\\ChannelValidator/Akeneo\\Pim\\Enrichment\\Component\\Product\\Validator\\Constraints\\ChannelValidator/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Validator\\Constraints\\Currency/Akeneo\\Pim\\Enrichment\\Component\\Product\\Validator\\Constraints\\Currency/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Validator\\Constraints\\CurrencyValidator/Akeneo\\Pim\\Enrichment\\Component\\Product\\Validator\\Constraints\\CurrencyValidator/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Validator\\Constraints\\File/Akeneo\\Pim\\Enrichment\\Component\\Product\\Validator\\Constraints\\File/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Validator\\Constraints\\FileExtension/Akeneo\\Pim\\Enrichment\\Component\\Product\\Validator\\Constraints\\FileExtension/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Validator\\Constraints\\FileExtensionValidator/Akeneo\\Pim\\Enrichment\\Component\\Product\\Validator\\Constraints\\FileExtensionValidator/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Validator\\Constraints\\FileValidator/Akeneo\\Pim\\Enrichment\\Component\\Product\\Validator\\Constraints\\FileValidator/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Validator\\Constraints\\ImmutableVariantAxesValues/Akeneo\\Pim\\Enrichment\\Component\\Product\\Validator\\Constraints\\ImmutableVariantAxesValues/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Validator\\Constraints\\ImmutableVariantAxesValuesValidator/Akeneo\\Pim\\Enrichment\\Component\\Product\\Validator\\Constraints\\ImmutableVariantAxesValuesValidator/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Validator\\Constraints\\IsNumeric/Akeneo\\Pim\\Enrichment\\Component\\Product\\Validator\\Constraints\\IsNumeric/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Validator\\Constraints\\IsNumericValidator/Akeneo\\Pim\\Enrichment\\Component\\Product\\Validator\\Constraints\\IsNumericValidator/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Validator\\Constraints\\IsString/Akeneo\\Pim\\Enrichment\\Component\\Product\\Validator\\Constraints\\IsString/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Validator\\Constraints\\IsStringValidator/Akeneo\\Pim\\Enrichment\\Component\\Product\\Validator\\Constraints\\IsStringValidator/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Validator\\Constraints\\LocalizableValue/Akeneo\\Pim\\Enrichment\\Component\\Product\\Validator\\Constraints\\LocalizableValue/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Validator\\Constraints\\LocalizableValueValidator/Akeneo\\Pim\\Enrichment\\Component\\Product\\Validator\\Constraints\\LocalizableValueValidator/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Validator\\Constraints\\NotDecimal/Akeneo\\Pim\\Enrichment\\Component\\Product\\Validator\\Constraints\\NotDecimal/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Validator\\Constraints\\NotDecimalValidator/Akeneo\\Pim\\Enrichment\\Component\\Product\\Validator\\Constraints\\NotDecimalValidator/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Validator\\Constraints\\NotEmptyFamily/Akeneo\\Pim\\Enrichment\\Component\\Product\\Validator\\Constraints\\NotEmptyFamily/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Validator\\Constraints\\NotEmptyFamilyValidator/Akeneo\\Pim\\Enrichment\\Component\\Product\\Validator\\Constraints\\NotEmptyFamilyValidator/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Validator\\Constraints\\NotEmptyVariantAxes/Akeneo\\Pim\\Enrichment\\Component\\Product\\Validator\\Constraints\\NotEmptyVariantAxes/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Validator\\Constraints\\NotEmptyVariantAxesValidator/Akeneo\\Pim\\Enrichment\\Component\\Product\\Validator\\Constraints\\NotEmptyVariantAxesValidator/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Validator\\Constraints\\OnlyExpectedAttributes/Akeneo\\Pim\\Enrichment\\Component\\Product\\Validator\\Constraints\\OnlyExpectedAttributes/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Validator\\Constraints\\OnlyExpectedAttributesValidator/Akeneo\\Pim\\Enrichment\\Component\\Product\\Validator\\Constraints\\OnlyExpectedAttributesValidator/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Validator\\Constraints\\ProductModelPositionInTheVariantTree/Akeneo\\Pim\\Enrichment\\Component\\Product\\Validator\\Constraints\\ProductModelPositionInTheVariantTree/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Validator\\Constraints\\ProductModelPositionInTheVariantTreeValidator/Akeneo\\Pim\\Enrichment\\Component\\Product\\Validator\\Constraints\\ProductModelPositionInTheVariantTreeValidator/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Validator\\Constraints\\Range/Akeneo\\Pim\\Enrichment\\Component\\Product\\Validator\\Constraints\\Range/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Validator\\Constraints\\RangeValidator/Akeneo\\Pim\\Enrichment\\Component\\Product\\Validator\\Constraints\\RangeValidator/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Validator\\Constraints\\SameFamilyThanParent/Akeneo\\Pim\\Enrichment\\Component\\Product\\Validator\\Constraints\\SameFamilyThanParent/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Validator\\Constraints\\SameFamilyThanParentValidator/Akeneo\\Pim\\Enrichment\\Component\\Product\\Validator\\Constraints\\SameFamilyThanParentValidator/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Validator\\Constraints\\ScopableValue/Akeneo\\Pim\\Enrichment\\Component\\Product\\Validator\\Constraints\\ScopableValue/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Validator\\Constraints\\ScopableValueValidator/Akeneo\\Pim\\Enrichment\\Component\\Product\\Validator\\Constraints\\ScopableValueValidator/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Validator\\Constraints\\UniqueValue/Akeneo\\Pim\\Enrichment\\Component\\Product\\Validator\\Constraints\\UniqueValue/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Validator\\Constraints\\UniqueValueValidator/Akeneo\\Pim\\Enrichment\\Component\\Product\\Validator\\Constraints\\UniqueValueValidator/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Validator\\Constraints\\UniqueVariantAxis/Akeneo\\Pim\\Enrichment\\Component\\Product\\Validator\\Constraints\\UniqueVariantAxis/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Validator\\Constraints\\UniqueVariantAxisValidator/Akeneo\\Pim\\Enrichment\\Component\\Product\\Validator\\Constraints\\UniqueVariantAxisValidator/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Validator\\Constraints\\VariantProductParent/Akeneo\\Pim\\Enrichment\\Component\\Product\\Validator\\Constraints\\VariantProductParent/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Validator\\Constraints\\VariantProductParentValidator/Akeneo\\Pim\\Enrichment\\Component\\Product\\Validator\\Constraints\\VariantProductParentValidator/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Validator\\Constraints\\WritableDirectory/Akeneo\\Pim\\Enrichment\\Component\\Product\\Validator\\Constraints\\WritableDirectory/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Validator\\Constraints\\WritableDirectoryValidator/Akeneo\\Pim\\Enrichment\\Component\\Product\\Validator\\Constraints\\WritableDirectoryValidator/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Validator\\Mapping\\ClassMetadataFactory/Akeneo\\Pim\\Enrichment\\Component\\Product\\Validator\\Mapping\\ClassMetadataFactory/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Validator\\Mapping\\DelegatingClassMetadataFactory/Akeneo\\Pim\\Enrichment\\Component\\Product\\Validator\\Mapping\\DelegatingClassMetadataFactory/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Validator\\Mapping\\ProductValueMetadataFactory/Akeneo\\Pim\\Enrichment\\Component\\Product\\Validator\\Mapping\\ProductValueMetadataFactory/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Validator\\AttributeConstraintGuesser/Akeneo\\Pim\\Enrichment\\Component\\Product\\Validator\\AttributeConstraintGuesser/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Validator\\AttributeValidatorHelper/Akeneo\\Pim\\Enrichment\\Component\\Product\\Validator\\AttributeValidatorHelper/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Validator\\ChainedAttributeConstraintGuesser/Akeneo\\Pim\\Enrichment\\Component\\Product\\Validator\\ChainedAttributeConstraintGuesser/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Validator\\ConstraintGuesserInterface/Akeneo\\Pim\\Enrichment\\Component\\Product\\Validator\\ConstraintGuesserInterface/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Validator\\UniqueAxesCombinationSet/Akeneo\\Pim\\Enrichment\\Component\\Product\\Validator\\UniqueAxesCombinationSet/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Validator\\UniqueValuesSet/Akeneo\\Pim\\Enrichment\\Component\\Product\\Validator\\UniqueValuesSet/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Validator\\Constraints\\ConversionUnits/Akeneo\\Channel\\Component\\Validator\\Constraint\\ConversionUnits/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Validator\\Constraints\\ConversionUnitsValidator/Akeneo\\Channel\\Component\\Validator\\Constraint\\ConversionUnitsValidator/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Validator\\Constraints\\IsCurrencyActivated/Akeneo\\Channel\\Component\\Validator\\Constraint\\IsCurrencyActivated/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Validator\\Constraints\\IsCurrencyActivatedValidator/Akeneo\\Channel\\Component\\Validator\\Constraint\\IsCurrencyActivatedValidator/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Validator\\Constraints\\IsRootCategory/Akeneo\\Channel\\Component\\Validator\\Constraint\\IsRootCategory/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Validator\\Constraints\\IsRootCategoryValidator/Akeneo\\Channel\\Component\\Validator\\Constraint\\IsRootCategoryValidator/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Validator\\Constraints\\ValidRegex/Akeneo\\Pim\\Structure\\Component\\Validator\\Constraints\\ValidRegex/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Validator\\Constraints\\ValidRegexValidator/Akeneo\\Pim\\Structure\\Component\\Validator\\Constraints\\ValidRegexValidator/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Validator\\Constraints\\ValidNumberRange/Akeneo\\Pim\\Structure\\Component\\Validator\\Constraints\\ValidNumberRange/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Validator\\Constraints\\ValidNumberRangeValidator/Akeneo\\Pim\\Structure\\Component\\Validator\\Constraints\\ValidNumberRangeValidator/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Validator\\Constraints\\ValidMetric/Akeneo\\Pim\\Structure\\Component\\Validator\\Constraints\\ValidMetric/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Validator\\Constraints\\ValidMetricValidator/Akeneo\\Pim\\Structure\\Component\\Validator\\Constraints\\ValidMetricValidator/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Validator\\Constraints\\ValidDateRange/Akeneo\\Pim\\Structure\\Component\\Validator\\Constraints\\ValidDateRange/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Validator\\Constraints\\ValidDateRangeValidator/Akeneo\\Pim\\Structure\\Component\\Validator\\Constraints\\ValidDateRangeValidator/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Validator\\Constraints\\NullProperties/Akeneo\\Pim\\Structure\\Component\\Validator\\Constraints\\NullProperties/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Validator\\Constraints\\NullPropertiesValidator/Akeneo\\Pim\\Structure\\Component\\Validator\\Constraints\\NullPropertiesValidator/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Validator\\Constraints\\NotNullProperties/Akeneo\\Pim\\Structure\\Component\\Validator\\Constraints\\NotNullProperties/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Validator\\Constraints\\NotNullPropertiesValidator/Akeneo\\Pim\\Structure\\Component\\Validator\\Constraints\\NotNullPropertiesValidator/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Validator\\Constraints\\IsReferenceDataConfigured/Akeneo\\Pim\\Structure\\Component\\Validator\\Constraints\\IsReferenceDataConfigured/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Validator\\Constraints\\IsReferenceDataConfiguredValidator/Akeneo\\Pim\\Structure\\Component\\Validator\\Constraints\\IsReferenceDataConfiguredValidator/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Validator\\Constraints\\IsIdentifierUsableAsGridFilter/Akeneo\\Pim\\Structure\\Component\\Validator\\Constraints\\IsIdentifierUsableAsGridFilter/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Validator\\Constraints\\IsIdentifierUsableAsGridFilterValidator/Akeneo\\Pim\\Structure\\Component\\Validator\\Constraints\\IsIdentifierUsableAsGridFilterValidator/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Validator\\Constraints\\FamilyVariant/Akeneo\\Pim\\Structure\\Component\\Validator\\Constraints\\FamilyVariant/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Validator\\Constraints\\FamilyVariantValidator/Akeneo\\Pim\\Structure\\Component\\Validator\\Constraints\\FamilyVariantValidator/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Validator\\Constraints\\AttributeTypeForOption/Akeneo\\Pim\\Structure\\Component\\Validator\\Constraints\\AttributeTypeForOption/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Validator\\Constraints\\AttributeTypeForOptionValidator/Akeneo\\Pim\\Structure\\Component\\Validator\\Constraints\\AttributeTypeForOptionValidator/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Validator\\Constraints\\Immutable/Akeneo\\Tool\\Component\\StorageUtils\\Validator\\Constraints\\Immutable/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Model\\AbstractMetric/Akeneo\\Pim\\Enrichment\\Component\\Product\\Model\\AbstractMetric/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Model\\AbstractProductPrice/Akeneo\\Pim\\Enrichment\\Component\\Product\\Model\\AbstractProductPrice/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Model\\AbstractValue/Akeneo\\Pim\\Enrichment\\Component\\Product\\Model\\AbstractValue/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Model\\CommonAttributeCollection/Akeneo\\Pim\\Structure\\Component\\Model\\CommonAttributeCollection/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Model\\EntityWithAssociationsInterface/Akeneo\\Pim\\Enrichment\\Component\\Product\\Model\\EntityWithAssociationsInterface/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Model\\EntityWithFamilyInterface/Akeneo\\Pim\\Enrichment\\Component\\Product\\Model\\EntityWithFamilyInterface/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Model\\EntityWithFamilyVariantInterface/Akeneo\\Pim\\Enrichment\\Component\\Product\\Model\\EntityWithFamilyVariantInterface/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Model\\EntityWithValuesInterface/Akeneo\\Pim\\Enrichment\\Component\\Product\\Model\\EntityWithValuesInterface/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Model\\Metric/Akeneo\\Pim\\Enrichment\\Component\\Product\\Model\\Metric/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Model\\MetricInterface/Akeneo\\Pim\\Enrichment\\Component\\Product\\Model\\MetricInterface/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Model\\PriceCollection/Akeneo\\Pim\\Enrichment\\Component\\Product\\Model\\PriceCollection/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Model\\PriceCollectionInterface/Akeneo\\Pim\\Enrichment\\Component\\Product\\Model\\PriceCollectionInterface/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Model\\ProductPrice/Akeneo\\Pim\\Enrichment\\Component\\Product\\Model\\ProductPrice/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Model\\ProductPriceInterface/Akeneo\\Pim\\Enrichment\\Component\\Product\\Model\\ProductPriceInterface/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Model\\ProductUniqueValueCollectionInterface/Akeneo\\Pim\\Enrichment\\Component\\Product\\Model\\ProductUniqueValueCollectionInterface/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Model\\ScopableInterface/Akeneo\\Pim\\Enrichment\\Component\\Product\\Model\\ScopableInterface/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Model\\TimestampableInterface/Akeneo\\Tool\\Component\\Versioning\\Model\\TimestampableInterface/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Model\\ValueCollection/Akeneo\\Pim\\Enrichment\\Component\\Product\\Model\\ValueCollection/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Model\\ValueCollectionInterface/Akeneo\\Pim\\Enrichment\\Component\\Product\\Model\\ValueCollectionInterface/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Model\\VariantProductInterface/Akeneo\\Pim\\Enrichment\\Component\\Product\\Model\\VariantProductInterface/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Updater\\Adder\\AbstractAttributeAdder/Akeneo\\Pim\\Enrichment\\Component\\Product\\Updater\\Adder\\AbstractAttributeAdder/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Updater\\Adder\\AbstractFieldAdder/Akeneo\\Pim\\Enrichment\\Component\\Product\\Updater\\Adder\\AbstractFieldAdder/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Updater\\Adder\\AdderInterface/Akeneo\\Pim\\Enrichment\\Component\\Product\\Updater\\Adder\\AdderInterface/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Updater\\Adder\\AdderRegistry/Akeneo\\Pim\\Enrichment\\Component\\Product\\Updater\\Adder\\AdderRegistry/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Updater\\Adder\\AdderRegistryInterface/Akeneo\\Pim\\Enrichment\\Component\\Product\\Updater\\Adder\\AdderRegistryInterface/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Updater\\Adder\\AssociationFieldAdder/Akeneo\\Pim\\Enrichment\\Component\\Product\\Updater\\Adder\\AssociationFieldAdder/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Updater\\Adder\\AttributeAdderInterface/Akeneo\\Pim\\Enrichment\\Component\\Product\\Updater\\Adder\\AttributeAdderInterface/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Updater\\Adder\\CategoryFieldAdder/Akeneo\\Pim\\Enrichment\\Component\\Product\\Updater\\Adder\\CategoryFieldAdder/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Updater\\Adder\\FieldAdderInterface/Akeneo\\Pim\\Enrichment\\Component\\Product\\Updater\\Adder\\FieldAdderInterface/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Updater\\Adder\\GroupFieldAdder/Akeneo\\Pim\\Enrichment\\Component\\Product\\Updater\\Adder\\GroupFieldAdder/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Updater\\Adder\\MultiSelectAttributeAdder/Akeneo\\Pim\\Enrichment\\Component\\Product\\Updater\\Adder\\MultiSelectAttributeAdder/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Updater\\Adder\\PriceCollectionAttributeAdder/Akeneo\\Pim\\Enrichment\\Component\\Product\\Updater\\Adder\\PriceCollectionAttributeAdder/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Updater\\Copier\\AbstractAttributeCopier/Akeneo\\Pim\\Enrichment\\Component\\Product\\Updater\\Copier\\AbstractAttributeCopier/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Updater\\Copier\\AttributeCopier/Akeneo\\Pim\\Enrichment\\Component\\Product\\Updater\\Copier\\AttributeCopier/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Updater\\Copier\\AttributeCopierInterface/Akeneo\\Pim\\Enrichment\\Component\\Product\\Updater\\Copier\\AttributeCopierInterface/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Updater\\Copier\\CopierInterface/Akeneo\\Pim\\Enrichment\\Component\\Product\\Updater\\Copier\\CopierInterface/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Updater\\Copier\\CopierRegistry/Akeneo\\Pim\\Enrichment\\Component\\Product\\Updater\\Copier\\CopierRegistry/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Updater\\Copier\\CopierRegistryInterface/Akeneo\\Pim\\Enrichment\\Component\\Product\\Updater\\Copier\\CopierRegistryInterface/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Updater\\Copier\\FieldCopierInterface/Akeneo\\Pim\\Enrichment\\Component\\Product\\Updater\\Copier\\FieldCopierInterface/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Updater\\Copier\\MediaAttributeCopier/Akeneo\\Pim\\Enrichment\\Component\\Product\\Updater\\Copier\\MediaAttributeCopier/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Updater\\Copier\\MetricAttributeCopier/Akeneo\\Pim\\Enrichment\\Component\\Product\\Updater\\Copier\\MetricAttributeCopier/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Updater\\Remover\\AbstractAttributeRemover/Akeneo\\Pim\\Enrichment\\Component\\Product\\Updater\\Remover\\AbstractAttributeRemover/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Updater\\Remover\\AbstractFieldRemover/Akeneo\\Pim\\Enrichment\\Component\\Product\\Updater\\Remover\\AbstractFieldRemover/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Updater\\Remover\\AttributeRemoverInterface/Akeneo\\Pim\\Enrichment\\Component\\Product\\Updater\\Remover\\AttributeRemoverInterface/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Updater\\Remover\\CategoryFieldRemover/Akeneo\\Pim\\Enrichment\\Component\\Product\\Updater\\Remover\\CategoryFieldRemover/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Updater\\Remover\\FieldRemoverInterface/Akeneo\\Pim\\Enrichment\\Component\\Product\\Updater\\Remover\\FieldRemoverInterface/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Updater\\Remover\\GroupFieldRemover/Akeneo\\Pim\\Enrichment\\Component\\Product\\Updater\\Remover\\GroupFieldRemover/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Updater\\Remover\\MultiSelectAttributeRemover/Akeneo\\Pim\\Enrichment\\Component\\Product\\Updater\\Remover\\MultiSelectAttributeRemover/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Updater\\Remover\\PriceCollectionAttributeRemover/Akeneo\\Pim\\Enrichment\\Component\\Product\\Updater\\Remover\\PriceCollectionAttributeRemover/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Updater\\Remover\\RemoverInterface/Akeneo\\Pim\\Enrichment\\Component\\Product\\Updater\\Remover\\RemoverInterface/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Updater\\Remover\\RemoverRegistry/Akeneo\\Pim\\Enrichment\\Component\\Product\\Updater\\Remover\\RemoverRegistry/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Updater\\Remover\\RemoverRegistryInterface/Akeneo\\Pim\\Enrichment\\Component\\Product\\Updater\\Remover\\RemoverRegistryInterface/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Updater\\Setter\\AbstractAttributeSetter/Akeneo\\Pim\\Enrichment\\Component\\Product\\Updater\\Setter\\AbstractAttributeSetter/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Updater\\Setter\\AbstractFieldSetter/Akeneo\\Pim\\Enrichment\\Component\\Product\\Updater\\Setter\\AbstractFieldSetter/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Updater\\Setter\\AssociationFieldSetter/Akeneo\\Pim\\Enrichment\\Component\\Product\\Updater\\Setter\\AssociationFieldSetter/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Updater\\Setter\\AttributeSetter/Akeneo\\Pim\\Enrichment\\Component\\Product\\Updater\\Setter\\AttributeSetter/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Updater\\Setter\\AttributeSetterInterface/Akeneo\\Pim\\Enrichment\\Component\\Product\\Updater\\Setter\\AttributeSetterInterface/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Updater\\Setter\\CategoryFieldSetter/Akeneo\\Pim\\Enrichment\\Component\\Product\\Updater\\Setter\\CategoryFieldSetter/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Updater\\Setter\\EnabledFieldSetter/Akeneo\\Pim\\Enrichment\\Component\\Product\\Updater\\Setter\\EnabledFieldSetter/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Updater\\Setter\\FamilyFieldSetter/Akeneo\\Pim\\Enrichment\\Component\\Product\\Updater\\Setter\\FamilyFieldSetter/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Updater\\Setter\\FieldSetterInterface/Akeneo\\Pim\\Enrichment\\Component\\Product\\Updater\\Setter\\FieldSetterInterface/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Updater\\Setter\\GroupFieldSetter/Akeneo\\Pim\\Enrichment\\Component\\Product\\Updater\\Setter\\GroupFieldSetter/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Updater\\Setter\\MediaAttributeSetter/Akeneo\\Pim\\Enrichment\\Component\\Product\\Updater\\Setter\\MediaAttributeSetter/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Updater\\Setter\\ParentFieldSetter/Akeneo\\Pim\\Enrichment\\Component\\Product\\Updater\\Setter\\ParentFieldSetter/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Updater\\Setter\\SetterInterface/Akeneo\\Pim\\Enrichment\\Component\\Product\\Updater\\Setter\\SetterInterface/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Updater\\Setter\\SetterRegistry/Akeneo\\Pim\\Enrichment\\Component\\Product\\Updater\\Setter\\SetterRegistry/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Updater\\Setter\\SetterRegistryInterface/Akeneo\\Pim\\Enrichment\\Component\\Product\\Updater\\Setter\\SetterRegistryInterface/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Updater\\EntityWithValuesUpdater/Akeneo\\Pim\\Enrichment\\Component\\Product\\Updater\\EntityWithValuesUpdater/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Updater\\GroupUpdater/Akeneo\\Pim\\Enrichment\\Component\\Product\\Updater\\GroupUpdater/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Updater\\ProductModelUpdater/Akeneo\\Pim\\Enrichment\\Component\\Product\\Updater\\ProductModelUpdater/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Updater\\ProductUpdater/Akeneo\\Pim\\Enrichment\\Component\\Product\\Updater\\ProductUpdater/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Updater\\PropertyAdder/Akeneo\\Pim\\Enrichment\\Component\\Product\\Updater\\PropertyAdder/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Updater\\PropertyCopier/Akeneo\\Pim\\Enrichment\\Component\\Product\\Updater\\PropertyCopier/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Updater\\PropertyRemover/Akeneo\\Pim\\Enrichment\\Component\\Product\\Updater\\PropertyRemover/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Updater\\PropertySetter/Akeneo\\Pim\\Enrichment\\Component\\Product\\Updater\\PropertySetter/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Updater\\Remover\\FamilyVariantRemover/Akeneo\\Pim\\Structure\\Component\\Remover\\FamilyVariantRemover/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Updater\\Remover\\FamilyRemover/Akeneo\\Pim\\Structure\\Component\\Remover\\FamilyRemover/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Query/Akeneo\\Pim\\Enrichment\\Component\\Product\\Query/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Factory\\Value\\DateValueFactory/Akeneo\\Pim\\Enrichment\\Component\\Product\\Factory\\Value\\DateValueFactory/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Factory\\Value\\MediaValueFactory/Akeneo\\Pim\\Enrichment\\Component\\Product\\Factory\\Value\\MediaValueFactory/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Factory\\Value\\MetricValueFactory/Akeneo\\Pim\\Enrichment\\Component\\Product\\Factory\\Value\\MetricValueFactory/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Factory\\Value\\OptionsValueFactory/Akeneo\\Pim\\Enrichment\\Component\\Product\\Factory\\Value\\OptionsValueFactory/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Factory\\Value\\OptionValueFactory/Akeneo\\Pim\\Enrichment\\Component\\Product\\Factory\\Value\\OptionValueFactory/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Factory\\Value\\PriceCollectionValueFactory/Akeneo\\Pim\\Enrichment\\Component\\Product\\Factory\\Value\\PriceCollectionValueFactory/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Factory\\Value\\ScalarValueFactory/Akeneo\\Pim\\Enrichment\\Component\\Product\\Factory\\Value\\ScalarValueFactory/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Factory\\Value\\ValueFactoryInterface/Akeneo\\Pim\\Enrichment\\Component\\Product\\Factory\\Value\\ValueFactoryInterface/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Factory\\GroupFactory/Akeneo\\Pim\\Enrichment\\Component\\Product\\Factory\\GroupFactory/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Factory\\MetricFactory/Akeneo\\Pim\\Enrichment\\Component\\Product\\Factory\\MetricFactory/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Factory\\PriceFactory/Akeneo\\Pim\\Enrichment\\Component\\Product\\Factory\\PriceFactory/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Factory\\ProductUniqueDataFactory/Akeneo\\Pim\\Enrichment\\Component\\Product\\Factory\\ProductUniqueDataFactory/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Factory\\ValueCollectionFactory/Akeneo\\Pim\\Enrichment\\Component\\Product\\Factory\\ValueCollectionFactory/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Factory\\ValueCollectionFactoryInterface/Akeneo\\Pim\\Enrichment\\Component\\Product\\Factory\\ValueCollectionFactoryInterface/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Factory\\ValueFactory/Akeneo\\Pim\\Enrichment\\Component\\Product\\Factory\\ValueFactory/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Manager\\AttributeValuesResolver/Akeneo\\Pim\\Enrichment\\Component\\Product\\Manager\\AttributeValuesResolver/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Manager\\AttributeValuesResolverInterface/Akeneo\\Pim\\Enrichment\\Component\\Product\\Manager\\AttributeValuesResolverInterface/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Manager\\CompletenessManager/Akeneo\\Pim\\Enrichment\\Component\\Product\\Manager\\CompletenessManager/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Job/Akeneo\\Pim\\Enrichment\\Component\\Product\\Job/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Converter/Akeneo\\Pim\\Enrichment\\Component\\Product\\Converter/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Builder/Akeneo\\Pim\\Enrichment\\Component\\Product\\Builder/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Association/Akeneo\\Pim\\Enrichment\\Component\\Product\\Association/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Normalizer\\Indexing\\CompletenessCollectionNormalizer/Akeneo\\Pim\\Enrichment\\Component\\Product\\Normalizer\\Indexing\\CompletenessCollectionNormalizer/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Normalizer\\Indexing\\DateTimeNormalizer/Akeneo\\Pim\\Enrichment\\Component\\Product\\Normalizer\\Indexing\\DateTimeNormalizer/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Normalizer\\Indexing\\Product\\ProductNormalizer/Akeneo\\Pim\\Enrichment\\Component\\Product\\Normalizer\\Indexing\\Product\\ProductNormalizer/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Normalizer\\Indexing\\Product\\PropertiesNormalizer/Akeneo\\Pim\\Enrichment\\Component\\Product\\Normalizer\\Indexing\\Product\\PropertiesNormalizer/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Normalizer\\Indexing\\ProductAndProductModel\\ProductModelNormalizer/Akeneo\\Pim\\Enrichment\\Component\\Product\\Normalizer\\Indexing\\ProductAndProductModel\\ProductModelNormalizer/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Normalizer\\Indexing\\ProductAndProductModel\\ProductModelPropertiesNormalizer/Akeneo\\Pim\\Enrichment\\Component\\Product\\Normalizer\\Indexing\\ProductAndProductModel\\ProductModelPropertiesNormalizer/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Normalizer\\Indexing\\ProductAndProductModel\\ProductNormalizer/Akeneo\\Pim\\Enrichment\\Component\\Product\\Normalizer\\Indexing\\ProductAndProductModel\\ProductNormalizer/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Normalizer\\Indexing\\ProductAndProductModel\\ProductPropertiesNormalizer/Akeneo\\Pim\\Enrichment\\Component\\Product\\Normalizer\\Indexing\\ProductAndProductModel\\ProductPropertiesNormalizer/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Normalizer\\Indexing\\ProductModel\\ProductModelNormalizer/Akeneo\\Pim\\Enrichment\\Component\\Product\\Normalizer\\Indexing\\ProductModel\\ProductModelNormalizer/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Normalizer\\Indexing\\ProductModel\\ProductModelPropertiesNormalizer/Akeneo\\Pim\\Enrichment\\Component\\Product\\Normalizer\\Indexing\\ProductModel\\ProductModelPropertiesNormalizer/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Normalizer\\Indexing\\Value\\AbstractProductValueNormalizer/Akeneo\\Pim\\Enrichment\\Component\\Product\\Normalizer\\Indexing\\Value\\AbstractProductValueNormalizer/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Normalizer\\Indexing\\Value\\BooleanNormalizer/Akeneo\\Pim\\Enrichment\\Component\\Product\\Normalizer\\Indexing\\Value\\BooleanNormalizer/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Normalizer\\Indexing\\Value\\DateNormalizer/Akeneo\\Pim\\Enrichment\\Component\\Product\\Normalizer\\Indexing\\Value\\DateNormalizer/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Normalizer\\Indexing\\Value\\DummyNormalizer/Akeneo\\Pim\\Enrichment\\Component\\Product\\Normalizer\\Indexing\\Value\\DummyNormalizer/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Normalizer\\Indexing\\Value\\MediaNormalizer/Akeneo\\Pim\\Enrichment\\Component\\Product\\Normalizer\\Indexing\\Value\\MediaNormalizer/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Normalizer\\Indexing\\Value\\MetricNormalizer/Akeneo\\Pim\\Enrichment\\Component\\Product\\Normalizer\\Indexing\\Value\\MetricNormalizer/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Normalizer\\Indexing\\Value\\NumberNormalizer/Akeneo\\Pim\\Enrichment\\Component\\Product\\Normalizer\\Indexing\\Value\\NumberNormalizer/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Normalizer\\Indexing\\Value\\OptionNormalizer/Akeneo\\Pim\\Enrichment\\Component\\Product\\Normalizer\\Indexing\\Value\\OptionNormalizer/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Normalizer\\Indexing\\Value\\OptionsNormalizer/Akeneo\\Pim\\Enrichment\\Component\\Product\\Normalizer\\Indexing\\Value\\OptionsNormalizer/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Normalizer\\Indexing\\Value\\PriceCollectionNormalizer/Akeneo\\Pim\\Enrichment\\Component\\Product\\Normalizer\\Indexing\\Value\\PriceCollectionNormalizer/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Normalizer\\Indexing\\Value\\TextAreaNormalizer/Akeneo\\Pim\\Enrichment\\Component\\Product\\Normalizer\\Indexing\\Value\\TextAreaNormalizer/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Normalizer\\Indexing\\Value\\TextNormalizer/Akeneo\\Pim\\Enrichment\\Component\\Product\\Normalizer\\Indexing\\Value\\TextNormalizer/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Normalizer\\Indexing\\Value\\ValueCollectionNormalizer/Akeneo\\Pim\\Enrichment\\Component\\Product\\Normalizer\\Indexing\\Value\\ValueCollectionNormalizer/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Normalizer\\Standard\\CategoryNormalizer/Akeneo\\Pim\\Enrichment\\Component\\Category\\Normalizer\\Standard\\CategoryNormalizer/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Normalizer\\Standard\\DateTimeNormalizer/Akeneo\\Pim\\Enrichment\\Component\\Product\\Normalizer\\Standard\\DateTimeNormalizer/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Normalizer\\Standard\\FileNormalizer/Akeneo\\Pim\\Enrichment\\Component\\Product\\Normalizer\\Standard\\FileNormalizer/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Normalizer\\Standard\\GroupNormalizer/Akeneo\\Pim\\Enrichment\\Component\\Product\\Normalizer\\Standard\\GroupNormalizer/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Normalizer\\Standard\\Product\\AssociationsNormalizer/Akeneo\\Pim\\Enrichment\\Component\\Product\\Normalizer\\Standard\\Product\\AssociationsNormalizer/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Normalizer\\Standard\\Product\\MetricNormalizer/Akeneo\\Pim\\Enrichment\\Component\\Product\\Normalizer\\Standard\\Product\\MetricNormalizer/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Normalizer\\Standard\\Product\\ParentsAssociationsNormalizer/Akeneo\\Pim\\Enrichment\\Component\\Product\\Normalizer\\Standard\\Product\\ParentsAssociationsNormalizer/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Normalizer\\Standard\\Product\\PriceNormalizer/Akeneo\\Pim\\Enrichment\\Component\\Product\\Normalizer\\Standard\\Product\\PriceNormalizer/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Normalizer\\Standard\\Product\\ProductValueNormalizer/Akeneo\\Pim\\Enrichment\\Component\\Product\\Normalizer\\Standard\\Product\\ProductValueNormalizer/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Normalizer\\Standard\\Product\\ProductValuesNormalizer/Akeneo\\Pim\\Enrichment\\Component\\Product\\Normalizer\\Standard\\Product\\ProductValuesNormalizer/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Normalizer\\Standard\\Product\\PropertiesNormalizer/Akeneo\\Pim\\Enrichment\\Component\\Product\\Normalizer\\Standard\\Product\\PropertiesNormalizer/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Normalizer\\Standard\\ProductModelNormalizer/Akeneo\\Pim\\Enrichment\\Component\\Product\\Normalizer\\Standard\\ProductModelNormalizer/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Normalizer\\Standard\\ProductNormalizer/Akeneo\\Pim\\Enrichment\\Component\\Product\\Normalizer\\Standard\\ProductNormalizer/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Normalizer\\Standard\\TranslationNormalizer/Akeneo\\Pim\\Enrichment\\Component\\Product\\Normalizer\\Standard\\TranslationNormalizer/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Normalizer\\Storage\\DateTimeNormalizer/Akeneo\\Pim\\Enrichment\\Component\\Product\\Normalizer\\Storage\\DateTimeNormalizer/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Normalizer\\Storage\\FileNormalizer/Akeneo\\Pim\\Enrichment\\Component\\Product\\Normalizer\\Storage\\FileNormalizer/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Normalizer\\Storage\\Product\\AssociationsNormalizer/Akeneo\\Pim\\Enrichment\\Component\\Product\\Normalizer\\Storage\\Product\\AssociationsNormalizer/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Normalizer\\Storage\\Product\\MetricNormalizer/Akeneo\\Pim\\Enrichment\\Component\\Product\\Normalizer\\Storage\\Product\\MetricNormalizer/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Normalizer\\Storage\\Product\\PriceNormalizer/Akeneo\\Pim\\Enrichment\\Component\\Product\\Normalizer\\Storage\\Product\\PriceNormalizer/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Normalizer\\Storage\\Product\\ProductValueNormalizer/Akeneo\\Pim\\Enrichment\\Component\\Product\\Normalizer\\Storage\\Product\\ProductValueNormalizer/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Comparator/Akeneo\\Pim\\Enrichment\\Component\\Product\\Comparator/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\FamilyVariant\\EntityWithFamilyVariantAttributesProvider/Akeneo\\Pim\\Enrichment\\Component\\Product\\EntityWithFamilyVariant\\EntityWithFamilyVariantAttributesProvider/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\EntityWithFamilyVariant/Akeneo\\Pim\\Enrichment\\Component\\Product\\EntityWithFamilyVariant/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\EntityWithFamily/Akeneo\\Pim\\Enrichment\\Component\\Product\\EntityWithFamily/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\ProductAndProductModel/Akeneo\\Pim\\Enrichment\\Component\\Product\\ProductAndProductModel/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\ProductModel/Akeneo\\Pim\\Enrichment\\Component\\Product\\ProductModel/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Repository\\AssociationRepositoryInterface/Akeneo\\Pim\\Enrichment\\Component\\Product\\Repository\\AssociationRepositoryInterface/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Repository\\CompletenessRepositoryInterface/Akeneo\\Pim\\Enrichment\\Component\\Product\\Repository\\CompletenessRepositoryInterface/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Repository\\EntityWithFamilyVariantRepositoryInterface/Akeneo\\Pim\\Enrichment\\Component\\Product\\Repository\\EntityWithFamilyVariantRepositoryInterface/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Repository\\GroupRepositoryInterface/Akeneo\\Pim\\Enrichment\\Component\\Product\\Repository\\GroupRepositoryInterface/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Repository\\ProductCategoryRepositoryInterface/Akeneo\\Pim\\Enrichment\\Component\\Product\\Repository\\ProductCategoryRepositoryInterface/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Repository\\ProductMassActionRepositoryInterface/Akeneo\\Pim\\Enrichment\\Component\\Product\\Repository\\ProductMassActionRepositoryInterface/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Repository\\ProductModelCategoryRepositoryInterface/Akeneo\\Pim\\Enrichment\\Component\\Product\\Repository\\ProductModelCategoryRepositoryInterface/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Repository\\ProductModelRepositoryInterface/Akeneo\\Pim\\Enrichment\\Component\\Product\\Repository\\ProductModelRepositoryInterface/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Repository\\ProductRepositoryInterface/Akeneo\\Pim\\Enrichment\\Component\\Product\\Repository\\ProductRepositoryInterface/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Repository\\ProductUniqueDataRepositoryInterface/Akeneo\\Pim\\Enrichment\\Component\\Product\\Repository\\ProductUniqueDataRepositoryInterface/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Repository\\VariantProductRepositoryInterface/Akeneo\\Pim\\Enrichment\\Component\\Product\\Repository\\VariantProductRepositoryInterface/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Completeness/Akeneo\\Pim\\Enrichment\\Component\\Product\\Completeness/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\ValuesFiller/Akeneo\\Pim\\Enrichment\\Component\\Product\\ValuesFiller/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\DependencyInjection\\Compiler\\Localization\\RegisterLocalizersPass/Akeneo\\Pim\\Enrichment\\Bundle\\DependencyInjection\\Compiler\\Localization\\RegisterLocalizersPass/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\DependencyInjection\\Compiler\\Localization\\RegisterPresentersPass/Akeneo\\Pim\\Enrichment\\Bundle\\DependencyInjection\\Compiler\\Localization\\RegisterPresentersPass/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\DependencyInjection\\Compiler\\RegisterAttributeConstraintGuessersPass/Akeneo\\Pim\\Enrichment\\Bundle\\DependencyInjection\\Compiler\\RegisterAttributeConstraintGuessersPass/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\DependencyInjection\\Compiler\\RegisterComparatorsPass/Akeneo\\Pim\\Enrichment\\Bundle\\DependencyInjection\\Compiler\\RegisterComparatorsPass/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\DependencyInjection\\Compiler\\RegisterCompleteCheckerPass/Akeneo\\Pim\\Enrichment\\Bundle\\DependencyInjection\\Compiler\\RegisterCompleteCheckerPass/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\DependencyInjection\\Compiler\\RegisterFilterPass/Akeneo\\Pim\\Enrichment\\Bundle\\DependencyInjection\\Compiler\\RegisterFilterPass/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\DependencyInjection\\Compiler\\RegisterProductQueryFilterPass/Akeneo\\Pim\\Enrichment\\Bundle\\DependencyInjection\\Compiler\\RegisterProductQueryFilterPass/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\DependencyInjection\\Compiler\\RegisterProductQuerySorterPass/Akeneo\\Pim\\Enrichment\\Bundle\\DependencyInjection\\Compiler\\RegisterProductQuerySorterPass/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\DependencyInjection\\Compiler\\RegisterProductUpdaterPass/Akeneo\\Pim\\Enrichment\\Bundle\\DependencyInjection\\Compiler\\RegisterProductUpdaterPass/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\DependencyInjection\\Compiler\\RegisterSerializerPass/Akeneo\\Pim\\Enrichment\\Bundle\\DependencyInjection\\Compiler\\RegisterSerializerPass/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\DependencyInjection\\Compiler\\RegisterValueFactoryPass/Akeneo\\Pim\\Enrichment\\Bundle\\DependencyInjection\\Compiler\\RegisterValueFactoryPass/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\EventSubscriber\\Category\\CheckChannelsOnDeletionSubscriber/Akeneo\\Pim\\Enrichment\\Bundle\\EventSubscriber\\Category\\CheckChannelsOnDeletionSubscriber/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\EventSubscriber\\AddBooleanValuesToNewProductSubscriber/Akeneo\\Pim\\Enrichment\\Bundle\\EventSubscriber\\AddBooleanValuesToNewProductSubscriber/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\EventSubscriber\\ComputeCompletenessOnFamilyUpdateSubscriber/Akeneo\\Pim\\Enrichment\\Bundle\\EventSubscriber\\ComputeCompletenessOnFamilyUpdateSubscriber/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\EventSubscriber\\ComputeEntityRawValuesSubscriber/Akeneo\\Pim\\Enrichment\\Bundle\\EventSubscriber\\ComputeEntityRawValuesSubscriber/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\EventSubscriber\\ComputeProductModelDescendantsSubscriber/Akeneo\\Pim\\Enrichment\\Bundle\\EventSubscriber\\ComputeProductModelDescendantsSubscriber/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\EventSubscriber\\IndexProductModelCompleteDataSubscriber/Akeneo\\Pim\\Enrichment\\Bundle\\EventSubscriber\\IndexProductModelCompleteDataSubscriber/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\EventSubscriber\\IndexProductModelsSubscriber/Akeneo\\Pim\\Enrichment\\Bundle\\EventSubscriber\\IndexProductModelsSubscriber/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\EventSubscriber\\IndexProductsSubscriber/Akeneo\\Pim\\Enrichment\\Bundle\\EventSubscriber\\IndexProductsSubscriber/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\EventSubscriber\\LoadEntityWithValuesSubscriber/Akeneo\\Pim\\Enrichment\\Bundle\\EventSubscriber\\LoadEntityWithValuesSubscriber/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\EventSubscriber\\LocalizableSubscriber/Akeneo\\Pim\\Enrichment\\Bundle\\EventSubscriber\\LocalizableSubscriber/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\EventSubscriber\\ResetUniqueValidationSubscriber/Akeneo\\Pim\\Enrichment\\Bundle\\EventSubscriber\\ResetUniqueValidationSubscriber/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\EventSubscriber\\ScopableSubscriber/Akeneo\\Pim\\Enrichment\\Bundle\\EventSubscriber\\ScopableSubscriber/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\EventSubscriber\\TimestampableSubscriber/Akeneo\\Pim\\Enrichment\\Bundle\\EventSubscriber\\TimestampableSubscriber/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\EventSubscriber\\CreateAttributeRequirementSubscriber/Akeneo\\Pim\\Structure\\Bundle\\EventSubscriber\\CreateAttributeRequirementSubscriber/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\Resolver\\FQCNResolver/Akeneo\\Pim\\Enrichment\\Bundle\\Resolver\\FQCNResolver/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\Context\\CatalogContext/Akeneo\\Pim\\Enrichment\\Bundle\\Context\\CatalogContext/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\Filter\\AbstractFilter/Akeneo\\Pim\\Enrichment\\Bundle\\Filter\\AbstractFilter/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\Filter\\ChainedFilter/Akeneo\\Pim\\Enrichment\\Bundle\\Filter\\ChainedFilter/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\Filter\\CollectionFilterInterface/Akeneo\\Pim\\Enrichment\\Bundle\\Filter\\CollectionFilterInterface/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\Filter\\ObjectFilterInterface/Akeneo\\Pim\\Enrichment\\Bundle\\Filter\\ObjectFilterInterface/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\Filter\\ProductValueChannelFilter/Akeneo\\Pim\\Enrichment\\Bundle\\Filter\\ProductValueChannelFilter/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\Filter\\ProductValueLocaleFilter/Akeneo\\Pim\\Enrichment\\Bundle\\Filter\\ProductValueLocaleFilter/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\Doctrine\\Common\\Filter\\ObjectCodeResolver/Akeneo\\Pim\\Enrichment\\Bundle\\Doctrine\\Common\\Filter\\ObjectCodeResolver/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\Doctrine\\Common\\Filter\\ObjectIdResolver/Akeneo\\Pim\\Enrichment\\Bundle\\Doctrine\\Common\\Filter\\ObjectIdResolver/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\Doctrine\\Common\\Filter\\ObjectIdResolverInterface/Akeneo\\Pim\\Enrichment\\Bundle\\Doctrine\\Common\\Filter\\ObjectIdResolverInterface/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\Doctrine\\Common\\Saver\\GroupSaver/Akeneo\\Pim\\Enrichment\\Bundle\\Doctrine\\Common\\Saver\\GroupSaver/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\Doctrine\\Common\\Saver\\GroupSavingOptionsResolver/Akeneo\\Pim\\Enrichment\\Bundle\\Doctrine\\Common\\Saver\\GroupSavingOptionsResolver/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\Doctrine\\Common\\Saver\\ProductModelDescendantsSaver/Akeneo\\Pim\\Enrichment\\Bundle\\Doctrine\\Common\\Saver\\ProductModelDescendantsSaver/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\Doctrine\\Common\\Saver\\ProductSaver/Akeneo\\Pim\\Enrichment\\Bundle\\Doctrine\\Common\\Saver\\ProductSaver/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\Doctrine\\Common\\Saver\\ProductUniqueDataSynchronizer/Akeneo\\Pim\\Enrichment\\Bundle\\Doctrine\\Common\\Saver\\ProductUniqueDataSynchronizer/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\Elasticsearch/Akeneo\\Pim\\Enrichment\\Bundle\\Elasticsearch/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\Command\\Cleaner\\WrongBooleanValuesOnVariantProductCleaner/Akeneo\\Pim\\Enrichment\\Bundle\\Command\\Cleaner\\WrongBooleanValuesOnVariantProductCleaner/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\Command\\ProductQueryHelp\\AttributeFilterDumper/Akeneo\\Pim\\Enrichment\\Bundle\\Command\\ProductQueryHelp\\AttributeFilterDumper/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\Command\\ProductQueryHelp\\FieldFilterDumper/Akeneo\\Pim\\Enrichment\\Bundle\\Command\\ProductQueryHelp\\FieldFilterDumper/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\Command\\CalculateCompletenessCommand/Akeneo\\Pim\\Enrichment\\Bundle\\Command\\CalculateCompletenessCommand/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\Command\\CleanRemovedAttributesFromProductAndProductModelCommand/Akeneo\\Pim\\Enrichment\\Bundle\\Command\\CleanRemovedAttributesFromProductAndProductModelCommand/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\Command\\CreateProductCommand/Akeneo\\Pim\\Enrichment\\Bundle\\Command\\CreateProductCommand/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\Command\\DumperInterface/Akeneo\\Pim\\Enrichment\\Bundle\\Command\\DumperInterface/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\Command\\GetProductCommand/Akeneo\\Pim\\Enrichment\\Bundle\\Command\\GetProductCommand/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\Command\\IndexProductCommand/Akeneo\\Pim\\Enrichment\\Bundle\\Command\\IndexProductCommand/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\Command\\IndexProductModelCommand/Akeneo\\Pim\\Enrichment\\Bundle\\Command\\IndexProductModelCommand/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\Command\\PurgeCompletenessCommand/Akeneo\\Pim\\Enrichment\\Bundle\\Command\\PurgeCompletenessCommand/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\Command\\PurgeProductsCompletenessCommand/Akeneo\\Pim\\Enrichment\\Bundle\\Command\\PurgeProductsCompletenessCommand/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\Command\\QueryHelpProductCommand/Akeneo\\Pim\\Enrichment\\Bundle\\Command\\QueryHelpProductCommand/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\Command\\QueryHelpProductModelCommand/Akeneo\\Pim\\Enrichment\\Bundle\\Command\\QueryHelpProductModelCommand/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\Command\\QueryProductCommand/Akeneo\\Pim\\Enrichment\\Bundle\\Command\\QueryProductCommand/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\Command\\RefreshProductCommand/Akeneo\\Pim\\Enrichment\\Bundle\\Command\\RefreshProductCommand/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\Command\\RemoveCompletenessForChannelAndLocaleCommand/Akeneo\\Pim\\Enrichment\\Bundle\\Command\\RemoveCompletenessForChannelAndLocaleCommand/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\Command\\RemoveProductCommand/Akeneo\\Pim\\Enrichment\\Bundle\\Command\\RemoveProductCommand/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\Command\\RemoveWrongBooleanValuesOnVariantProductsBatchCommand/Akeneo\\Pim\\Enrichment\\Bundle\\Command\\RemoveWrongBooleanValuesOnVariantProductsBatchCommand/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\Command\\RemoveWrongBooleanValuesOnVariantProductsCommand/Akeneo\\Pim\\Enrichment\\Bundle\\Command\\RemoveWrongBooleanValuesOnVariantProductsCommand/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\Command\\UpdateProductCommand/Akeneo\\Pim\\Enrichment\\Bundle\\Command\\UpdateProductCommand/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\Command\\ValidateObjectsCommand/Akeneo\\Pim\\Enrichment\\Bundle\\Command\\ValidateObjectsCommand/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\Command\\ValidateProductCommand/Akeneo\\Pim\\Enrichment\\Bundle\\Command\\ValidateProductCommand/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\EventSubscriber\\AddUniqueAttributesToVariantProductAttributeSetSubscriber/Akeneo\\Pim\\Structure\\Bundle\\EventSubscriber\\AddUniqueAttributesToVariantProductAttributeSetSubscriber/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\EventSubscriber\\ComputeFamilyVariantStructureChangesSubscriber/Akeneo\\Pim\\Structure\\Bundle\\EventSubscriber\\ComputeFamilyVariantStructureChangesSubscriber/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\EventSubscriber\\RemoveAttributesFromFamilyVariantsOnFamilyUpdateSubscriber/Akeneo\\Pim\\Structure\\Bundle\\EventSubscriber\\RemoveAttributesFromFamilyVariantsOnFamilyUpdateSubscriber/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\EventSubscriber\\SaveFamilyVariantOnFamilyUpdateSubscriber/Akeneo\\Pim\\Structure\\Bundle\\EventSubscriber\\SaveFamilyVariantOnFamilyUpdateSubscriber/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Entity\\GroupTranslation/Akeneo\\Pim\\Enrichment\\Component\\Category\\Entity\\GroupTranslation/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Model\\GroupTranslationInterface/Akeneo\\Pim\\Enrichment\\Component\\Category\\Model\\GroupTranslationInterface/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Entity\\Group/Akeneo\\Pim\\Enrichment\\Component\\Category\\Entity\\Group/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Model\\GroupInterface/Akeneo\\Pim\\Enrichment\\Component\\Category\\Model\\GroupInterface/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\Entity\\Category/Akeneo\\Pim\\Enrichment\\Component\\Category\\Model\\Category/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\Entity\\CategoryTranslation/Akeneo\\Pim\\Enrichment\\Component\\Category\\Model\\CategoryTranslation/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Model\\CategoryTranslationInterface/Akeneo\\Pim\\Enrichment\\Component\\Category\\Model\\CategoryTranslationInterface/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Entity\\Category/Akeneo\\Pim\\Enrichment\\Component\\Category\\Model\\Category/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Model\\CategoryInterface/Akeneo\\Pim\\Enrichment\\Component\\Category\\Model\\CategoryInterface/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Model\\AbstractAssociation/Akeneo\\Pim\\Enrichment\\Component\\Product\\Model\\AbstractAssociation/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Model\\AssociationInterface/Akeneo\\Pim\\Enrichment\\Component\\Product\\Model\\AssociationInterface/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Model\\ProductModelAssociation/Akeneo\\Pim\\Enrichment\\Component\\Product\\Model\\ProductModelAssociation/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Model\\ProductModelAssociationInterface/Akeneo\\Pim\\Enrichment\\Component\\Product\\Model\\ProductModelAssociationInterface/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Model\\ProductAssociation/Akeneo\\Pim\\Enrichment\\Component\\Product\\Model\\ProductAssociation/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Model\\ProductAssociationInterface/Akeneo\\Pim\\Enrichment\\Component\\Product\\Model\\ProductAssociationInterface/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Model\\ProductUniqueDataInterface/Akeneo\\Pim\\Enrichment\\Component\\Product\\Model\\ProductUniqueDataInterface/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Model\\AbstractProductUniqueData/Akeneo\\Pim\\Enrichment\\Component\\Product\\Model\\AbstractProductUniqueData/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Model\\ProductUniqueData/Akeneo\\Pim\\Enrichment\\Component\\Product\\Model\\ProductUniqueData/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Model\\CompletenessInterface/Akeneo\\Pim\\Enrichment\\Component\\Product\\Model\\CompletenessInterface/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Model\\AbstractCompleteness/Akeneo\\Pim\\Enrichment\\Component\\Product\\Model\\AbstractCompleteness/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Model\\Completeness/Akeneo\\Pim\\Enrichment\\Component\\Product\\Model\\Completeness/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Model\\ValueInterface/Akeneo\\Pim\\Enrichment\\Component\\Product\\Model\\ValueInterface/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Value/Akeneo\\Pim\\Enrichment\\Component\\Product\\Value/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Model\\ProductModel/Akeneo\\Pim\\Enrichment\\Component\\Product\\Model\\ProductModel/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Model\\ProductModel/Akeneo\\Pim\\Enrichment\\Component\\Product\\Model\\ProductModel/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Model\\ProductModelInterface/Akeneo\\Pim\\Enrichment\\Component\\Product\\Model\\ProductModelInterface/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Model\\Product/Akeneo\\Pim\\Enrichment\\Component\\Product\\Model\\Product/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Model\\AbstractProduct/Akeneo\\Pim\\Enrichment\\Component\\Product\\Model\\AbstractProduct/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Model\\ProductInterface/Akeneo\\Pim\\Enrichment\\Component\\Product\\Model\\ProductInterface/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\Doctrine\\ORM\\Repository\\FamilyVariantRepository/Akeneo\\Pim\\Structure\\Bundle\\Doctrine\\ORM\\Repository\\FamilyVariantRepository/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\Doctrine\\ORM\\Repository\\FamilyRepository/Akeneo\\Pim\\Structure\\Bundle\\Doctrine\\ORM\\Repository\\FamilyRepository/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\Doctrine\\ORM\\Repository\\AttributeRepository/Akeneo\\Pim\\Structure\\Bundle\\Doctrine\\ORM\\Repository\\AttributeRepository/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Repository\\FamilyVariantRepositoryInterface/Akeneo\\Pim\\Structure\\Component\\Repository\\FamilyVariantRepositoryInterface/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Repository\\FamilyRepositoryInterface/Akeneo\\Pim\\Structure\\Component\\Repository\\FamilyRepositoryInterface/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Repository\\AttributeRepositoryInterface/Akeneo\\Pim\\Structure\\Component\\Repository\\AttributeRepositoryInterface/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Repository\\AttributeGroupRepositoryInterface/Akeneo\\Pim\\Structure\\Component\\Repository\\AttributeGroupRepositoryInterface/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\Doctrine\\ORM\\Repository\\AttributeGroupRepository/Akeneo\\Pim\\Structure\\Bundle\\Doctrine\\ORM\\Repository\\AttributeGroupRepository/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\Doctrine\\ORM\\Query\\FamilyVariantsByAttributeAxes/Akeneo\\Pim\\Structure\\Bundle\\Doctrine\\ORM\\Query\\FamilyVariantsByAttributeAxes/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\FamilyVariant\\Query\\FamilyVariantsByAttributeAxesInterface/Akeneo\\Pim\\Structure\\Component\\FamilyVariant\\Query\\FamilyVariantsByAttributeAxesInterface/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\FamilyVariant\\AddUniqueAttributes/Akeneo\\Pim\\Structure\\Component\\FamilyVariant\\AddUniqueAttributes/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Factory\\FamilyFactory/Akeneo\\Pim\\Structure\\Component\\Factory\\FamilyFactory/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Factory\\AttributeRequirementFactory/Akeneo\\Pim\\Structure\\Component\\Factory\\AttributeRequirementFactory/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Factory\\AttributeFactory/Akeneo\\Pim\\Structure\\Component\\Factory\\AttributeFactory/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\Entity\\AttributeGroup/Akeneo\\Pim\\Structure\\Component\\Model\\AttributeGroup/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Model\\AttributeGroupInterface/Akeneo\\Pim\\Structure\\Component\\Model\\AttributeGroupInterface/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\Entity\\AttributeGroupTranslation/Akeneo\\Pim\\Structure\\Component\\Model\\AttributeGroupTranslation/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Model\\AttributeGroupTranslationInterface/Akeneo\\Pim\\Structure\\Component\\Model\\AttributeGroupTranslationInterface/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/pim_catalog.model.currency.interface/akeneo_channel.model.currency.interface/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Validator\\Constraints\\ActivatedLocale/Akeneo\\Channel\\Component\\Validator\\Constraint\\ActivatedLocale/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Validator\\Constraints\\Locale/Akeneo\\Channel\\Component\\Validator\\Constraint\\Locale/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\Doctrine\\ORM\\Repository\\LocaleRepository/Akeneo\\Channel\\Bundle\\Doctrine\\Repository\\LocaleRepository/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Repository\\LocaleRepositoryInterface/Akeneo\\Channel\\Component\\Repository\\LocaleRepositoryInterface/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\Entity\\Locale/Akeneo\\Channel\\Component\\Model\\Locale/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Model\\LocaleInterface/Akeneo\\Channel\\Component\\Model\\LocaleInterface/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/pim_catalog.model.locale.interface/akeneo_channel.model.locale.interface/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Oro\\Bundle\\UserBundle\\Entity\\UserManager/Akeneo\\UserManagement\\Bundle\\Manager\\UserManager/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Oro\\Bundle\\UserBundle\\Form\\Type\\RoleApiType/Akeneo\\UserManagement\\Bundle\\Form\\Type\\RoleApiType/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Oro\\Bundle\\UserBundle\\Form\\Type\\AclRoleType/Akeneo\\UserManagement\\Bundle\\Form\\Type\\AclRoleType/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Oro\\Bundle\\UserBundle\\Form\\Handler\\UserHandler/Akeneo\\UserManagement\\Bundle\\Form\\Handler\\UserHandler/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Oro\\Bundle\\UserBundle\\Form\\Handler\\ResetHandler/Akeneo\\UserManagement\\Bundle\\Form\\Handler\\ResetHandler/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Oro\\Bundle\\UserBundle\\Form\\Handler\\AclRoleHandler/Akeneo\\UserManagement\\Bundle\\Form\\Handler\\AclRoleHandler/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Oro\\Bundle\\UserBundle\\Security\\UserProvider/Akeneo\\UserManagement\\Bundle\\Security\\UserProvider/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Oro\\Bundle\\UserBundle\\Form\\Type\\ResetType/Akeneo\\UserManagement\\Bundle\\Form\\Type\\ResetType/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Oro\\Bundle\\UserBundle\\Form\\Type\\GroupType/Akeneo\\UserManagement\\Bundle\\Form\\Type\\GroupType/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Oro\\Bundle\\UserBundle\\Form\\Type\\GroupApiType/Akeneo\\UserManagement\\Bundle\\Form\\Type\\GroupApiType/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Oro\\Bundle\\UserBundle\\Form\\Type\\ChangePasswordType/Akeneo\\UserManagement\\Bundle\\Form\\Type\\ChangePasswordType/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Oro\\Bundle\\UserBundle\\Form\\Handler\\GroupHandler/Akeneo\\UserManagement\\Bundle\\Form\\Handler\\GroupHandler/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Oro\\Bundle\\UserBundle\\Form\\Handler\\AbstractUserHandler/Akeneo\\UserManagement\\Bundle\\Form\\Handler\\AbstractUserHandler/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Oro\\Bundle\\UserBundle\\Entity\\EventListener\\UploadedImageSubscriber/Akeneo\\UserManagement\\Bundle\\EventSubscriber\\UploadedImageSubscriber/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Oro\\Bundle\\UserBundle\\Entity\\EntityUploadedImageInterface/Akeneo\\UserManagement\\Component\\EntityUploadedImageInterface/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Oro\\Bundle\\UserBundle\\Entity\\Repository/Akeneo\\UserManagement\\Bundle\\Doctrine\\ORM\\Repository/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Oro\\Bundle\\UserBundle\\Form\\EventListener/Akeneo\\UserManagement\\Bundle\\Form\\Subscriber/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Oro\\Bundle\\UserBundle\\EventListener/Akeneo\\UserManagement\\Bundle\\EventListener/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Oro\\Bundle\\UserBundle\\Controller/Akeneo\\UserManagement\\Bundle\\Controller/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\UserBundle\\Controller\\UserRestController/Akeneo\\UserManagement\\Bundle\\Controller\\Rest\\UserController/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\UserBundle\\Controller\\SecurityRestController/Akeneo\\UserManagement\\Bundle\\Controller\\Rest\\SecurityController/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\UserBundle\\Controller\\UserGroupRestController/Akeneo\\UserManagement\\Bundle\\Controller\\Rest\\UserGroupController/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Oro\\Bundle\\UserBundle\\OroUserEvents/Akeneo\\UserManagement\\Component\\UserEvents/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Oro\\Bundle\\UserBundle\\Entity\\UserManager/Akeneo\\UserManagement\\Bundle\\Manager\\UserManager/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Oro\\Bundle\\UserBundle\\Entity\\Role/Akeneo\\UserManagement\\Component\\Model\\Role/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Oro\\Bundle\\UserBundle\\Entity\\Group/Akeneo\\UserManagement\\Component\\Model\\Group/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\UserBundle\\Entity\\User/Akeneo\\UserManagement\\Component\\Model\\User/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\UserBundle\\Entity\\UserInterface/Akeneo\\UserManagement\\Component\\Model\\UserInterface/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\VersioningBundle\\Normalizer\\Flat\\CategoryNormalizer/Akeneo\\Pim\\Enrichment\\Component\\Category\\Normalizer\\Versioning\\CategoryNormalizer/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\VersioningBundle\\Normalizer\\Flat\\ProductNormalizer/Akeneo\\Pim\\Enrichment\\Component\\Product\\Normalizer\\Versioning\\ProductNormalizer/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\VersioningBundle\\Normalizer\\Flat\\ProductModelNormalizer/Akeneo\\Pim\\Enrichment\\Component\\Product\\Normalizer\\Versioning\\ProductModelNormalizer/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\VersioningBundle\\Normalizer\\Flat\\GroupNormalizer/Akeneo\\Pim\\Enrichment\\Component\\Product\\Normalizer\\Versioning\\GroupNormalizer/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\VersioningBundle\\Normalizer\\Flat\\TranslationNormalizer/Akeneo\\Pim\\Enrichment\\Component\\Product\\Normalizer\\Versioning\\TranslationNormalizer/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\VersioningBundle\\Normalizer\\Flat\\ValueNormalizer/Akeneo\\Pim\\Enrichment\\Component\\Product\\Normalizer\\Versioning\\Product\\ValueNormalizer/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\VersioningBundle\\Normalizer\\Flat\\DateTimeNormalizer/Akeneo\\Pim\\Enrichment\\Component\\Product\\Normalizer\\Versioning\\Product\\DateTimeNormalizer/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\VersioningBundle\\Normalizer\\Flat\\FileNormalizer/Akeneo\\Pim\\Enrichment\\Component\\Product\\Normalizer\\Versioning\\Product\\FileNormalizer/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\VersioningBundle\\Normalizer\\Flat\\MetricNormalizer/Akeneo\\Pim\\Enrichment\\Component\\Product\\Normalizer\\Versioning\\Product\\MetricNormalizer/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\VersioningBundle\\Normalizer\\Flat\\PriceNormalizer/Akeneo\\Pim\\Enrichment\\Component\\Product\\Normalizer\\Versioning\\Product\\PriceNormalizer/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Akeneo\\Bundle\\MeasureBundle/Akeneo\\Tool\\Bundle\\MeasureBundle/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Akeneo\\Component\\FileStorage/Akeneo\\Tool\\Component\\FileStorage/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Akeneo\\Bundle\\FileStorageBundle/Akeneo\\Tool\\Bundle\\FileStorageBundle/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Akeneo\\Component\\Classification/Akeneo\\Tool\\Component\\Classification/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Akeneo\\Bundle\\ClassificationBundle/Akeneo\\Tool\\Bundle\\ClassificationBundle/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Akeneo\\Bundle\\BufferBundle/Akeneo\\Tool\\Bundle\\BufferBundle/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Akeneo\\Tool\\Bundle\\ApiBundle\\Controller\\ChannelController/Akeneo\\Channel\\Bundle\\Controller\\ExternalApi\\ChannelController/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\Doctrine\\Common\\Remover\\ChannelRemover/Akeneo\\Channel\\Bundle\\Doctrine\\Remover\\ChannelRemover/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\Doctrine\\ORM\\Repository\\ChannelRepository/Akeneo\\Channel\\Bundle\\Doctrine\\Repository\\ChannelRepository/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\EventListener\\Storage\\ChannelLocaleSubscriber/Akeneo\\Channel\\Bundle\\EventListener\\ChannelLocaleSubscriber/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\Entity\\Channel/Akeneo\\Channel\\Component\\Model\\Channel/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Model\\ChannelInterface/Akeneo\\Channel\\Component\\Model\\ChannelInterface/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\Entity\\ChannelTranslation/Akeneo\\Channel\\Component\\Model\\ChannelTranslation/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Model\\ChannelTranslationInterface/Akeneo\\Channel\\Component\\Model\\ChannelTranslationInterface/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Akeneo\\Tool\\Component\\Api\\Normalizer\\ChannelNormalizer/Akeneo\\Channel\\Component\\Normalizer\\ExternalApi\\ChannelNormalizer/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\Normalizer\\ChannelNormalizer/Akeneo\\Channel\\Component\\Normalizer\\InternalApi\\ChannelNormalizer/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Normalizer\\Standard\\ChannelNormalizer/Akeneo\\Channel\\Component\\Normalizer\\Standard\\ChannelNormalizer/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\VersioningBundle\\Normalizer\\Flat\\ChannelNormalizer/Akeneo\\Channel\\Component\\Normalizer\\Versioning\\ChannelNormalizer/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Repository\\ChannelRepositoryInterface/Akeneo\\Channel\\Component\\Repository\\ChannelRepositoryInterface/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Updater\\ChannelUpdater/Akeneo\\Channel\\Component\\Updater\\ChannelUpdater/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Updater\\LocaleUpdater/Akeneo\\Channel\\Component\\Updater\\LocaleUpdater/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Akeneo\\Tool\\Component\\Api\\Normalizer\\LocaleNormalizer/Akeneo\\Channel\\Component\\Normalizer\\ExternalApi\\LocaleNormalizer/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\Normalizer\\LocaleNormalizer/Akeneo\\Channel\\Component\\Normalizer\\InternalApi\\LocaleNormalizer/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\VersioningBundle\\Normalizer\\Flat/Akeneo\\Channel\\Component\\Normalizer\\Versioning/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Model\\CurrencyInterface/Akeneo\\Channel\\Component\\Model\\CurrencyInterface/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Model\\CurrencyInterface/Akeneo\\Channel\\Component\\Model\\CurrencyInterface/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\Doctrine\\ORM\\Repository\\CurrencyRepository/Akeneo\\Channel\\Bundle\\Doctrine\\Repository\\CurrencyRepository/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\EventSubscriber\\CurrencyDisablingSubscriber/Akeneo\\Channel\\Bundle\\EventListener\\CurrencyDisablingSubscriber/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Akeneo\\Tool\\Component\\Api\\Normalizer\\CurrencyNormalizer/Akeneo\\Tool\\Component\\Api\\Normalizer\\CurrencyNormalizer/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Normalizer\\Standard\\CurrencyNormalizer/Akeneo\\Channel\\Component\\Normalizer\\Standard\\CurrencyNormalizer/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Repository\\CurrencyRepositoryInterface/Akeneo\\Channel\\Component\\Repository\\CurrencyRepositoryInterface/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Updater\\CurrencyUpdater/Akeneo\\Channel\\Component\\Updater\\CurrencyUpdater/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Connector\\ArrayConverter\\FlatToStandard\\Channel/Akeneo\\Channel\\Component\\ArrayConverter\\FlatToStandard\\Channel/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Connector\\ArrayConverter\\FlatToStandard\\Locale/Akeneo\\Channel\\Component\\ArrayConverter\\FlatToStandard\\Locale/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Connector\\ArrayConverter\\FlatToStandard\\Currency/Akeneo\\Channel\\Component\\ArrayConverter\\FlatToStandard\\Currency/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Connector\\ArrayConverter\\StandardToFlat\\Channel/Akeneo\\Channel\\Component\\ArrayConverter\\StandardToFlat\\Channel/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Connector\\ArrayConverter\\StandardToFlat\\Locale/Akeneo\\Channel\\Component\\ArrayConverter\\StandardToFlat\\Locale/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Connector\\ArrayConverter\\StandardToFlat\\Currency/Akeneo\\Channel\\Component\\ArrayConverter\\StandardToFlat\\Currency/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Exception\\LinkedChannelException/Akeneo\\Channel\\Component\\Exception\\LinkedChannelException/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Model\\ReferableInterface/Akeneo\\Tool\\Component\\StorageUtils\\Model\\ReferableInterface/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\Entity\\Attribute/Akeneo\\Pim\\Structure\\Component\\Model\\Attribute/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Model\\AbstractAttribute/Akeneo\\Pim\\Structure\\Component\\Model\\AbstractAttribute/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Model\\AttributeInterface/Akeneo\\Pim\\Structure\\Component\\Model\\AttributeInterface/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\Entity\\AttributeOption/Akeneo\\Pim\\Structure\\Component\\Model\\AttributeOption/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Model\\AttributeOptionInterface/Akeneo\\Pim\\Structure\\Component\\Model\\AttributeOptionInterface/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Model\\FamilyInterface/Akeneo\\Pim\\Structure\\Component\\Model\\FamilyInterface/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\Entity\\Family/Akeneo\\Pim\\Structure\\Component\\Model\\Family/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\Entity\\FamilyTranslation/Akeneo\\Pim\\Structure\\Component\\Model\\FamilyTranslation/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Model\\FamilyTranslationInterface/Akeneo\\Pim\\Structure\\Component\\Model\\FamilyTranslationInterface/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Model\\FamilyVariantInterface/Akeneo\\Pim\\Structure\\Component\\Model\\FamilyVariantInterface/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Model\\FamilyVariant/Akeneo\\Pim\\Structure\\Component\\Model\\FamilyVariant/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Model\\FamilyVariantTranslation/Akeneo\\Pim\\Structure\\Component\\Model\\FamilyVariant/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Model\\FamilyVariantTranslationInterface/Akeneo\\Pim\\Structure\\Component\\Model\\FamilyVariant/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\Entity\\AttributeRequirement/Akeneo\\Pim\\Structure\\Component\\Model\\AttributeRequirement/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Model\\AttributeRequirementInterface/Akeneo\\Pim\\Structure\\Component\\Model\\AttributeRequirementInterface/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\Entity\\AttributeTranslation/Akeneo\\Pim\\Structure\\Component\\Model\\AttributeTranslation/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Model\\AttributeTranslationInterface/Akeneo\\Pim\\Structure\\Component\\Model\\AttributeTranslationInterface/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Model\\VariantAttributeSet/Akeneo\\Pim\\Structure\\Component\\Model\\VariantAttributeSet/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Model\\VariantAttributeSetInterface/Akeneo\\Pim\\Structure\\Component\\Model\\VariantAttributeSetInterface/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Updater\\AttributeUpdater/Akeneo\\Pim\\Structure\\Component\\Updater\\AttributeUpdater/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Updater\\AttributeOptionUpdater/Akeneo\\Pim\\Structure\\Component\\Updater\\AttributeOptionUpdater/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Updater\\FamilyUpdater/Akeneo\\Pim\\Structure\\Component\\Updater\\FamilyUpdater/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Akeneo\\Tool\\Component\\Api\\Updater\\FamilyVariantUpdater/Akeneo\\Pim\\Structure\\Component\\Updater\\ExternalApi\\FamilyVariantUpdater/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Updater\\FamilyVariantUpdater/Akeneo\\Pim\\Structure\\Component\\Updater\\FamilyVariantUpdater/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Akeneo\\Tool\\Component\\Api\\Normalizer\\AttributeGroupNormalizer/Akeneo\\Pim\\Structure\\Component\\Normalizer\\ExternalApi\\AttributeGroupNormalizer/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Akeneo\\Tool\\Component\\Api\\Normalizer\\AttributeNormalizer/Akeneo\\Pim\\Structure\\Component\\Normalizer\\ExternalApi\\AttributeNormalizer/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Akeneo\\Tool\\Component\\Api\\Normalizer\\AttributeOptionNormalizer/Akeneo\\Pim\\Structure\\Component\\Normalizer\\ExternalApi\\AttributeOptionNormalizer/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Akeneo\\Tool\\Component\\Api\\Normalizer\\FamilyNormalizer/Akeneo\\Pim\\Structure\\Component\\Normalizer\\ExternalApi\\FamilyNormalizer/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Akeneo\\Tool\\Component\\Api\\Normalizer\\FamilyVariantNormalizer/Akeneo\\Pim\\Structure\\Component\\Normalizer\\ExternalApi\\FamilyVariantNormalizer/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Normalizer\\Indexing\\FamilyNormalizer/Akeneo\\Pim\\Structure\\Component\\Normalizer\\Indexing\\FamilyNormalizer/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\Normalizer\\AttributeGroupNormalizer/Akeneo\\Pim\\Structure\\Component\\Normalizer\\InternalApi\\AttributeGroupNormalizer/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\Normalizer\\AttributeNormalizer/Akeneo\\Pim\\Structure\\Component\\Normalizer\\InternalApi\\AttributeNormalizer/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\Normalizer\\AttributeOptionNormalizer/Akeneo\\Pim\\Structure\\Component\\Normalizer\\InternalApi\\AttributeOptionNormalizer/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\Normalizer\\FamilyNormalizer/Akeneo\\Pim\\Structure\\Component\\Normalizer\\InternalApi\\FamilyNormalizer/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\Normalizer\\FamilyVariantNormalizer/Akeneo\\Pim\\Structure\\Component\\Normalizer\\InternalApi\\FamilyVariantNormalizer/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Normalizer\\Standard\\AttributeGroupNormalizer/Akeneo\\Pim\\Structure\\Component\\Normalizer\\Standard\\AttributeGroupNormalizer/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Normalizer\\Standard\\AttributeNormalizer/Akeneo\\Pim\\Structure\\Component\\Normalizer\\Standard\\AttributeNormalizer/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Normalizer\\Standard\\AttributeOptionNormalizer/Akeneo\\Pim\\Structure\\Component\\Normalizer\\Standard\\AttributeOptionNormalizer/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Normalizer\\Standard\\FamilyNormalizer/Akeneo\\Pim\\Structure\\Component\\Normalizer\\Standard\\FamilyNormalizer/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Normalizer\\Standard\\FamilyVariantNormalizer/Akeneo\\Pim\\Structure\\Component\\Normalizer\\Standard\\FamilyVariantNormalizer/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Normalizer\\Storage\\AttributeOptionNormalizer/Akeneo\\Pim\\Structure\\Component\\Normalizer\\Storage\\AttributeOptionNormalizer/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\VersioningBundle\\Normalizer\\Flat\\AttributeGroupNormalize/Akeneo\\Pim\\Structure\\Component\\Normalizer\\Versioning\\AttributeGroupNormalizer/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\VersioningBundle\\Normalizer\\Flat\\AttributeNormalizer/Akeneo\\Pim\\Structure\\Component\\Normalizer\\Versioning\\AttributeNormalizer/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\VersioningBundle\\Normalizer\\Flat\\AttributeOptionNormalizer/Akeneo\\Pim\\Structure\\Component\\Normalizer\\Versioning\\AttributeOptionNormalizer/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\VersioningBundle\\Normalizer\\Flat\\FamilyNormalizer/Akeneo\\Pim\\Structure\\Component\\Normalizer\\Versioning\\FamilyNormalizer/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\ApiBundle\\Controller\\AttributeController/Akeneo\\Pim\\Structure\\Bundle\\Controller\\ExternalApi\\AttributeController/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\ApiBundle\\Controller\\AttributeGroupController/Akeneo\\Pim\\Structure\\Bundle\\Controller\\ExternalApi\\AttributeGroupController/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\ApiBundle\\Controller\\AttributeOptionController/Akeneo\\Pim\\Structure\\Bundle\\Controller\\ExternalApi\\AttributeOptionController/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\ApiBundle\\Controller\\FamilyController/Akeneo\\Pim\\Structure\\Bundle\\Controller\\ExternalApi\\FamilyController/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\ApiBundle\\Controller\\FamilyVariantController/Akeneo\\Pim\\Structure\\Bundle\\Controller\\ExternalApi\\FamilyVariantController/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\Controller\\Rest\\AttributeController/Akeneo\\Pim\\Structure\\Bundle\\Controller\\InternalApi\\AttributeController/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\Controller\\Rest\\AttributeGroupController/Akeneo\\Pim\\Structure\\Bundle\\Controller\\InternalApi\\AttributeGroupController/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\Controller\\Rest\\AttributeOptionController/Akeneo\\Pim\\Structure\\Bundle\\Controller\\InternalApi\\AttributeOptionController/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\Controller\\Rest\\FamilyController/Akeneo\\Pim\\Structure\\Bundle\\Controller\\InternalApi\\FamilyController/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\Controller\\Rest\\FamilyVariantController/Akeneo\\Pim\\Structure\\Bundle\\Controller\\InternalApi\\FamilyVariantController/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\Controller\\Rest\\AttributeTypeController/Akeneo\\Pim\\Structure\\Bundle\\Controller\\InternalApi\\AttributeTypeController/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\AttributeTypeRegistry/Akeneo\\Pim\\Structure\\Component\\AttributeTypeRegistry/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\Doctrine\\ORM\\Repository\\AttributeOptionRepository/Akeneo\\Pim\\Structure\\Bundle\\Doctrine\\ORM\\Repository\\AttributeOptionRepository/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\Doctrine\\Common\\Saver\\AttributeSaver/Akeneo\\Pim\\Structure\\Bundle\\Doctrine\\ORM\\Saver\\AttributeSaver/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\Doctrine\\Common\\Saver\\FamilySaver/Akeneo\\Pim\\Structure\\Bundle\\Doctrine\\ORM\\Saver\\FamilySaver/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Repository\\AttributeRequirementRepositoryInterface/Akeneo\\Pim\\Structure\\Component\\Repository\\AttributeRequirementRepositoryInterface/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Repository\\AttributeOptionRepositoryInterface/Akeneo\\Pim\\Structure\\Component\\Repository\\AttributeOptionRepositoryInterface/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Api\\Repository\\AttributeRepositoryInterface/Akeneo\\Pim\\Structure\\Component\\Repository\\ExternalApi\\AttributeRepositoryInterface/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\Doctrine\\ORM\\Repository\\AttributeGroupRepository/Akeneo\\Pim\\Structure\\Bundle\\Doctrine\\ORM\\Repository\\InternalApi\\AttributeGroupRepository/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\Doctrine\\ORM\\Repository\\AttributeOptionSearchableRepository/Akeneo\\Pim\\Structure\\Bundle\\Doctrine\\ORM\\Repository\\InternalApi\\AttributeOptionSearchableRepository/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\Doctrine\\ORM\\Repository\\AttributeRepository/Akeneo\\Pim\\Structure\\Bundle\\Doctrine\\ORM\\Repository\\InternalApi\\AttributeRepository/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\Doctrine\\ORM\\Repository\\AttributeSearchableRepository/Akeneo\\Pim\\Structure\\Bundle\\Doctrine\\ORM\\Repository\\InternalApi\\AttributeSearchableRepository/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\Doctrine\\ORM\\Repository\\FamilyRepository/Akeneo\\Pim\\Structure\\Bundle\\Doctrine\\ORM\\Repository\\InternalApi\\FamilyRepository/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\Doctrine\\ORM\\Repository\\FamilySearchableRepository/Akeneo\\Pim\\Structure\\Bundle\\Doctrine\\ORM\\Repository\\InternalApi\\FamilySearchableRepository/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\Doctrine\\ORM\\Repository\\FamilyVariantRepository/Akeneo\\Pim\\Structure\\Bundle\\Doctrine\\ORM\\Repository\\InternalApi\\FamilyVariantRepository/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\Doctrine\\ORM\\Repository\\FamilyVariantSearchableRepository/Akeneo\\Pim\\Structure\\Bundle\\Doctrine\\ORM\\Repository\\InternalApi\\FamilyVariantSearchableRepository/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\Doctrine\\ORM\\Repository\\AttributeGroupSearchableRepository/Akeneo\\Pim\\Structure\\Bundle\\Doctrine\\ORM\\Repository\\InternalApi\\AttributeGroupSearchableRepository/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\AttributeType/Akeneo\\Pim\\Structure\\Component\\AttributeType/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Connector\\ArrayConverter\\FlatToStandard\\Attribute/Akeneo\\Pim\\Structure\\Component\\ArrayConverter\\FlatToStandard\\Attribute/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Connector\\ArrayConverter\\FlatToStandard\\AttributeGroup/Akeneo\\Pim\\Structure\\Component\\ArrayConverter\\FlatToStandard\\AttributeGroup/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Connector\\ArrayConverter\\FlatToStandard\\AttributeOption/Akeneo\\Pim\\Structure\\Component\\ArrayConverter\\FlatToStandard\\AttributeOption/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Connector\\ArrayConverter\\FlatToStandard\\Family/Akeneo\\Pim\\Structure\\Component\\ArrayConverter\\FlatToStandard\\Family/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Connector\\ArrayConverter\\FlatToStandard\\FamilyVariant/Akeneo\\Pim\\Structure\\Component\\ArrayConverter\\FlatToStandard\\FamilyVariant/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Connector\\ArrayConverter\\StandardToFlat\\FlatToStandard\\Attribute/Akeneo\\Pim\\Structure\\Component\\ArrayConverter\\StandardToFlat\\Attribute/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Connector\\ArrayConverter\\StandardToFlat\\FlatToStandard\\AttributeGroup/Akeneo\\Pim\\Structure\\Component\\ArrayConverter\\StandardToFlat\\AttributeGroup/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Connector\\ArrayConverter\\StandardToFlat\\FlatToStandard\\AttributeOption/Akeneo\\Pim\\Structure\\Component\\ArrayConverter\\StandardToFlat\\AttributeOption/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Connector\\ArrayConverter\\StandardToFlat\\FlatToStandard\\Family/Akeneo\\Pim\\Structure\\Component\\ArrayConverter\\StandardToFlat\\Family/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Connector\\ArrayConverter\\StandardToFlat\\FlatToStandard\\FamilyVariant\\FamilyVariant/Akeneo\\Pim\\Structure\\Component\\ArrayConverter\\StandardToFlat\\FamilyVariant\\FamilyVariant/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Connector\\ArrayConverter\\StandardToFlat\\FlatToStandard\\FamilyVariant\\FieldSplitter/Akeneo\\Pim\\Structure\\Component\\ArrayConverter\\StandardToFlat\\FamilyVariant\\FieldSplitter/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Connector\\Reader\\Database\\AttributeOptionReader/Akeneo\\Pim\\Structure\\Component\\Reader\\Database\\AttributeOptionReader/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Connector\\Writer\\Database\\AttributeGroupWriter/Akeneo\\Pim\\Structure\\Component\\Writer\\Database\\AttributeGroupWriter/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\DependencyInjection\\Compiler\\RegisterAttributeTypePass/Akeneo\\Pim\\Structure\\Bundle\\DependencyInjection\\Compiler\\RegisterAttributeTypePass/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Manager\\AttributeOptionsSorter/Akeneo\\Pim\\Structure\\Component\\Manager\\AttributeOptionsSorter/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Validator\\Constraints\\FamilyAttributeAsImage/Akeneo\\Pim\\Structure\\Component\\Validator\\Constraints\\FamilyAttributeAsImage/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Validator\\Constraints\\FamilyAttributeAsImageValidator/Akeneo\\Pim\\Structure\\Component\\Validator\\Constraints\\FamilyAttributeAsImageValidator/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Validator\\Constraints\\FamilyAttributeAsLabel/Akeneo\\Pim\\Structure\\Component\\Validator\\Constraints\\FamilyAttributeAsLabel/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Validator\\Constraints\\FamilyAttributeAsLabelValidator/Akeneo\\Pim\\Structure\\Component\\Validator\\Constraints\\FamilyAttributeAsLabelValidator/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Validator\\Constraints\\FamilyAttributeUsedAsAxis/Akeneo\\Pim\\Structure\\Component\\Validator\\Constraints\\FamilyAttributeUsedAsAxis/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Validator\\Constraints\\FamilyAttributeUsedAsAxisValidator/Akeneo\\Pim\\Structure\\Component\\Validator\\Constraints\\FamilyAttributeUsedAsAxisValidator/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Validator\\Constraints\\FamilyRequirements/Akeneo\\Pim\\Structure\\Component\\Validator\\Constraints\\FamilyRequirements/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Validator\\Constraints\\FamilyRequirementsValidator/Akeneo\\Pim\\Structure\\Component\\Validator\\Constraints\\FamilyRequirementsValidator/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Validator\\Constraints\\ImmutableVariantAxes/Akeneo\\Pim\\Structure\\Component\\Validator\\Constraints\\ImmutableVariantAxes/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Validator\\Constraints\\ImmutableVariantAxesValidator/Akeneo\\Pim\\Structure\\Component\\Validator\\Constraints\\ImmutableVariantAxesValidator/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Oro\\Bundle\\DataGridBundle\\Common\\Object/Oro\\Bundle\\DataGridBundle\\Common\\IterableObject/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Oro\\Bundle\\DataGridBundle\\Datagrid\\Common\\ResultsObject/Oro\\Bundle\\DataGridBundle\\Datagrid\\Common\\ResultsIterableObject/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Oro\\Bundle\\DataGridBundle\\Datagrid\\Common\\MetadataObject/Oro\\Bundle\\DataGridBundle\\Datagrid\\Common\\MetadataIterableObject/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\Entity\\GroupType/Akeneo\\Pim\\Structure\\Component\\Model\\GroupType/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Model\\GroupTypeInterface/Akeneo\\Pim\\Structure\\Component\\Model\\GroupTypeInterface/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\Entity\\GroupTypeTranslation/Akeneo\\Pim\\Structure\\Component\\Model\\GroupTypeTranslation/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Model\\GroupTypeTranslationInterface/Akeneo\\Pim\\Structure\\Component\\Model\\GroupTypeTranslationInterface/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\Entity\\AssociationType/Akeneo\\Pim\\Structure\\Component\\Model\\AssociationType/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Model\\AssociationTypeInterface/Akeneo\\Pim\\Structure\\Component\\Model\\AssociationTypeInterface/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\Entity\\AssociationTypeTranslation/Akeneo\\Pim\\Structure\\Component\\Model\\AssociationTypeTranslation/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Model\\AssociationTypeTranslationInterface/Akeneo\\Pim\\Structure\\Component\\Model\\AssociationTypeTranslationInterface/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\Entity\\AttributeOptionValue\\AttributeOptionValue/Akeneo\\Pim\\Structure\\Component\\Model\\AttributeOptionValue/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Model\\AttributeOptionValue/Akeneo\\Pim\\Structure\\Component\\Model\\AttributeOptionValueInterface/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\Doctrine\\ORM\\Repository\\GroupTypeRepository/Akeneo\\Pim\\Structure\\Bundle\\Doctrine\\ORM\\Repository\\GroupTypeRepository/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\Doctrine\\ORM\\Repository\\GroupTypeRepository/Akeneo\\Pim\\Structure\\Bundle\\Doctrine\\ORM\\Repository\\InternalApi\\GroupTypeRepository/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\Doctrine\\ORM\\Repository\\GroupTypeRepository/Akeneo\\Pim\\Structure\\Bundle\\Doctrine\\ORM\\Repository\\InternalApi\\GroupTypeRepository/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\Form\\Type\\GroupTypeType/Akeneo\\Pim\\Structure\\Bundle\\Form\\Type\\GroupTypeType/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Factory\\GroupTypeFactory/Akeneo\\Pim\\Structure\\Component\\Factory\\GroupTypeFactory/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\Normalizer\\GroupTypeNormalizer/Akeneo\\Pim\\Structure\\Component\\Normalizer\\InternalApi\\GroupTypeNormalizer/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Normalizer\\Standard\\GroupTypeNormalizer/Akeneo\\Pim\\Structure\\Component\\Normalizer\\Standard\\GroupTypeNormalizer/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Updater\\GroupTypeUpdater/Akeneo\\Pim\\Structure\\Component\\Updater\\GroupTypeUpdater/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Akeneo\\Tool\\Bundle\\ApiBundle\\Controller\\AssociationTypeController/Akeneo\\Pim\\Structure\\Bundle\\Controller\\ExternalApi\\AssociationTypeController/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\Controller\\Rest\\AssociationTypeController/Akeneo\\Pim\\Structure\\Bundle\\Controller\\InternalApi\\AssociationTypeController/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\Controller\\AssociationTypeController/Akeneo\\Pim\\Structure\\Bundle\\Controller\\Ui\\AssociationTypeController/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\Doctrine\\ORM\\Repository\\AssociationTypeRepository/Akeneo\\Pim\\Structure\\Bundle\\Doctrine\\ORM\\Repository\\AssociationTypeRepository/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\Doctrine\\ORM\\Repository\\AssociationTypeRepository/Akeneo\\Pim\\Structure\\Bundle\\Doctrine\\ORM\\Repository\\InternalApi\\AssociationTypeRepository/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Connector\\ArrayConverter\\FlatToStandard\\AssociationType/Akeneo\\Pim\\Structure\\Component\\ArrayConverter\\FlatToStandard\\AssociationType/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Connector\\ArrayConverter\\StandardToFlat\\AssociationType/Akeneo\\Pim\\Structure\\Component\\ArrayConverter\\StandardToFlat\\AssociationType/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Akeneo\\Tool\\Component\\Api\\Normalizer\\AssociationTypeNormalizer/Akeneo\\Pim\\Structure\\Component\\Normalizer\\ExternalApi\\AssociationTypeNormalizer/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\Normalizer\\AssociationTypeNormalizer/Akeneo\\Pim\\Structure\\Component\\Normalizer\\InternalApi\\AssociationTypeNormalizer/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Normalizer\\Standard\\AssociationTypeNormalizer/Akeneo\\Pim\\Structure\\Component\\Normalizer\\Standard\\AssociationTypeNormalizer/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\VersioningBundle\\Normalizer\\Flat\\AssociationTypeNormalizer/Akeneo\\Pim\\Structure\\Component\\Normalizer\\Versioning\\AssociationTypeNormalizer/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Repository\\AssociationTypeRepositoryInterface/Akeneo\\Pim\\Structure\\Component\\Repository\\AssociationTypeRepositoryInterface/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\Controller\\Rest\\GroupTypeController/Akeneo\\Pim\\Structure\\Bundle\\Controller\\InternalApi\\GroupTypeController/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Connector\\ArrayConverter\\FlatToStandard\\GroupType/Akeneo\\Pim\\Structure\\Component\\ArrayConverter\\FlatToStandard\\GroupType/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Connector\\ArrayConverter\\StandardToFlat\\GroupType/Akeneo\\Pim\\Structure\\Component\\ArrayConverter\\StandardToFlat\\GroupType/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Repository\\GroupTypeRepositoryInterface/Akeneo\\Pim\\Structure\\Component\\Repository\\GroupTypeRepositoryInterface/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\EventSubscriber\\AttributeOption\\AttributeOptionRemovalSubscriber/Akeneo\\Pim\\Structure\\Bundle\\EventListener\\AttributeOptionRemovalSubscriber/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\Form\\Type\\AttributeOption\\AttributeOptionCreateType/Akeneo\\Pim\\Structure\\Bundle\\Form\\Type\\AttributeOptionCreateType/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\Form\\Type\\AttributeOptionType/Akeneo\\Pim\\Structure\\Bundle\\Form\\Type\\AttributeOptionType/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\Form\\Type\\FamilyType/Akeneo\\Pim\\Structure\\Bundle\\Form\\Type\\FamilyType/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\Form\\Type\\AttributeOptionValueType/Akeneo\\Pim\\Structure\\Bundle\\Form\\Type\\AttributeOptionValueType/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\VersioningBundle/Akeneo\\Tool\\Bundle\\VersioningBundle/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\PdfGeneratorBundle\\Controller\\ProductController/Akeneo\\Pim\\Enrichment\\Bundle\\Controller\\Ui\\ProductController/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\PdfGeneratorBundle\\DependencyInjection\\Compiler\\RegisterRendererPass/Akeneo\\Pim\\Enrichment\\Bundle\\DependencyInjection\\Compiler\\RegisterRendererPass/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\PdfGeneratorBundle\\Builder\\DompdfBuilder/Akeneo\\Pim\\Enrichment\\Bundle\\PdfGeneration\\Builder\\DompdfBuilder/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\PdfGeneratorBundle\\Builder\\PdfBuilderInterface/Akeneo\\Pim\\Enrichment\\Bundle\\PdfGeneration\\Builder\\PdfBuilderInterface/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\PdfGeneratorBundle\\Exception\\RendererRequiredException/Akeneo\\Pim\\Enrichment\\Bundle\\PdfGeneration\\Exception\\RendererRequiredException/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\PdfGeneratorBundle\\Renderer\\ProductPdfRenderer/Akeneo\\Pim\\Enrichment\\Bundle\\PdfGeneration\\Renderer\\ProductPdfRenderer/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\PdfGeneratorBundle\\Renderer\\RendererInterface/Akeneo\\Pim\\Enrichment\\Bundle\\PdfGeneration\\Renderer\\RendererInterface/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\PdfGeneratorBundle\\Renderer\\RendererRegistry/Akeneo\\Pim\\Enrichment\\Bundle\\PdfGeneration\\Renderer\\RendererRegistry/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CommentBundle\\Controller\\CommentController/Akeneo\\Pim\\Enrichment\\Bundle\\Controller\\InternalApi\\CommentController/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CommentBundle\\Repository\\CommentRepository/Akeneo\\Pim\\Enrichment\\Bundle\\Doctrine\\ORM\\Repository\\CommentRepository/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CommentBundle\\Form\\Type\\CommentType/Akeneo\\Pim\\Enrichment\\Bundle\\Form\\Type\\CommentType/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CommentBundle\\Builder\\CommentBuilder/Akeneo\\Pim\\Enrichment\\Component\\Comment\\Builder\\CommentBuilder/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CommentBundle\\Entity\\Comment/Akeneo\\Pim\\Enrichment\\Component\\Comment\\Model\\Comment/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CommentBundle\\Model\\CommentInterface/Akeneo\\Pim\\Enrichment\\Component\\Comment\\Model\\CommentInterface/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CommentBundle\\Model\\CommentSubjectInterface/Akeneo\\Pim\\Enrichment\\Component\\Comment\\Model\\CommentSubjectInterface/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CommentBundle\\Normalizer\\Standard\\CommentNormalizer/Akeneo\\Pim\\Enrichment\\Component\\Comment\\Normalizer\\Standard\\CommentNormalizer/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CommentBundle\\Repository\\CommentRepositoryInterface/Akeneo\\Pim\\Enrichment\\Component\\Comment\\Repository\\CommentRepositoryInterface/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Akeneo\\Tool\\Bundle\\ApiBundle\\Controller\\CurrencyController/Akeneo\\Channel\\Bundle\\Controller\\ExternalApi\\CurrencyController/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Akeneo\\Tool\\Bundle\\ApiBundle\\Controller\\CategoryController/Akeneo\\Pim\\Enrichment\\Bundle\\Controller\\ExternalApi\\CategoryController/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Akeneo\\Tool\\Bundle\\ApiBundle\\Controller\\ProductController/Akeneo\\Pim\\Enrichment\\Bundle\\Controller\\ExternalApi\\ProductController/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Akeneo\\Tool\\Bundle\\ApiBundle\\Controller\\ProductModelController/Akeneo\\Pim\\Enrichment\\Bundle\\Controller\\ExternalApi\\ProductModelController/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Akeneo\\Tool\\Bundle\\ApiBundle\\Doctrine\\ORM\\Repository\\ProductRepository/Akeneo\\Pim\\Enrichment\\Bundle\\Doctrine\\ORM\\Repository\\ExternalApi\\ProductRepository/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Akeneo\\Tool\\Component\\Api\\Normalizer\\CategoryNormalizer/Akeneo\\Pim\\Enrichment\\Component\\Category\\Normalizer\\ExternalApi\\CategoryNormalizer/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Akeneo\\Tool\\Component\\Api\\Normalizer\\ProductNormalizer/Akeneo\\Pim\\Enrichment\\Component\\Product\\Normalizer\\ExternalApi\\ProductNormalizer/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Akeneo\\Tool\\Component\\Api\\Normalizer\\ProductModelNormalizer/Akeneo\\Pim\\Enrichment\\Component\\Product\\Normalizer\\ExternalApi\\ProductModelNormalizer/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Akeneo\\Tool\\Component\\Api\\Repository\\ProductRepositoryInterface/Akeneo\\Pim\\Enrichment\\Component\\Product\\Repository\\ExternalApi\\ProductRepositoryInterface/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Akeneo\\Tool\\Component\\Api\\Updater\\ProductModelUpdater/Akeneo\\Pim\\Enrichment\\Component\\Product\\ExternalApi\\Updater\\ProductModelUpdater/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Connector\\ArrayConverter\\FlatToStandard\\Category/Akeneo\\Pim\\Enrichment\\Component\\Category\\Connector\\ArrayConverter\\FlatToStandard\\Category/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Connector\\ArrayConverter\\StandardToFlat\\Category/Akeneo\\Pim\\Enrichment\\Component\\Category\\Connector\\ArrayConverter\\StandardToFlat\\Category/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Connector\\Reader\\Database\\StandardToFlat\\CategoryReader/Akeneo\\Pim\\Enrichment\\Component\\Category\\Connector\\Reader\\Database\\CategoryReader/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Connector\\ArrayConverter\\FlatToStandard\\Product\\AssociationColumnsResolver/Akeneo\\Pim\\Enrichment\\Component\\Product\\Connector\\ArrayConverter\\FlatToStandard\\AttributeColumnInfoExtractor/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Connector\\ArrayConverter\\FlatToStandard\\Product\\AttributeColumnInfoExtractor/Akeneo\\Pim\\Enrichment\\Component\\Product\\Connector\\ArrayConverter\\FlatToStandard\\AssociationColumnsResolver/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Connector\\ArrayConverter\\FlatToStandard\\Product\\AttributeColumnsResolver/Akeneo\\Pim\\Enrichment\\Component\\Product\\Connector\\ArrayConverter\\FlatToStandard\\AttributeColumnsResolver/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Connector\\ArrayConverter\\FlatToStandard\\Product\\ColumnsMerger/Akeneo\\Pim\\Enrichment\\Component\\Product\\Connector\\ArrayConverter\\FlatToStandard\\ColumnsMerger/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Connector\\ArrayConverter\\FlatToStandard\\Product\\ColumnsMapper/Akeneo\\Pim\\Enrichment\\Component\\Product\\Connector\\ArrayConverter\\FlatToStandard\\ColumnsMapper/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Connector\\ArrayConverter\\FlatToStandard\\ConvertedField/Akeneo\\Pim\\Enrichment\\Component\\Product\\Connector\\ArrayConverter\\FlatToStandard\\ConvertedField/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Connector\\ArrayConverter\\FlatToStandard\\EntityWithValuesDelocalized/Akeneo\\Pim\\Enrichment\\Component\\Product\\Connector\\ArrayConverter\\FlatToStandard\\EntityWithValuesDelocalized/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Connector\\ArrayConverter\\FlatToStandard\\FieldConverterInterface/Akeneo\\Pim\\Enrichment\\Component\\Product\\Connector\\ArrayConverter\\FlatToStandard\\FieldConverterInterface/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Connector\\ArrayConverter\\FlatToStandard\\Product\\FieldSplitter/Akeneo\\Pim\\Enrichment\\Component\\Product\\Connector\\ArrayConverter\\FlatToStandard\\FieldSplitter/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Connector\\ArrayConverter\\FlatToStandard\\Group/Akeneo\\Pim\\Enrichment\\Component\\Product\\Connector\\ArrayConverter\\FlatToStandard\\Group/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Connector\\ArrayConverter\\FlatToStandard\\Product/Akeneo\\Pim\\Enrichment\\Component\\Product\\Connector\\ArrayConverter\\FlatToStandard\\Product/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Connector\\ArrayConverter\\FlatToStandard\\ProductAssociation/Akeneo\\Pim\\Enrichment\\Component\\Product\\Connector\\ArrayConverter\\FlatToStandard\\ProductAssociation/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Connector\\ArrayConverter\\FlatToStandard\\Product\\FieldConverter/Akeneo\\Pim\\Enrichment\\Component\\Product\\Connector\\ArrayConverter\\FlatToStandard\\FieldConverter/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Connector\\ArrayConverter\\FlatToStandard\\ProductModel\\FieldConverter/Akeneo\\Pim\\Enrichment\\Component\\Product\\Connector\\ArrayConverter\\FlatToStandard\\ProductModel\\FieldConverter/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Connector\\ArrayConverter\\FlatToStandard\\ProductModelAssociation/Akeneo\\Pim\\Enrichment\\Component\\Product\\Connector\\ArrayConverter\\FlatToStandard\\ProductModelAssociation/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Connector\\ArrayConverter\\FlatToStandard\\Value/Akeneo\\Pim\\Enrichment\\Component\\Product\\Connector\\ArrayConverter\\FlatToStandard\\Value/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Connector\\ArrayConverter\\FlatToStandard\\Product\\ValueConverter/Akeneo\\Pim\\Enrichment\\Component\\Product\\Connector\\ArrayConverter\\FlatToStandard\\ValueConverter/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Connector\\ArrayConverter\\StandardToFlat\\Group/Akeneo\\Pim\\Enrichment\\Component\\Product\\Connector\\ArrayConverter\\StandardToFlat\\Group/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Connector\\ArrayConverter\\StandardToFlat\\Product/Akeneo\\Pim\\Enrichment\\Component\\Product\\Connector\\ArrayConverter\\StandardToFlat\\Product/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Connector\\ArrayConverter\\StandardToFlat\\Product\\ValueConverter\\AbstractValueConverter/Akeneo\\Pim\\Enrichment\\Component\\Product\\Connector\\ArrayConverter\\StandardToFlat\\Product\\ValueConverter\\AbstractValueConverter/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Connector\\ArrayConverter\\StandardToFlat\\Product\\ValueConverter\\MediaConverter/Akeneo\\Pim\\Enrichment\\Component\\Product\\Connector\\ArrayConverter\\StandardToFlat\\Product\\ValueConverter\\MediaConverter/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Connector\\ArrayConverter\\StandardToFlat\\Product\\ValueConverter/Akeneo\\Pim\\Enrichment\\Component\\Product\\Connector\\ArrayConverter\\StandardToFlat\\Product\\ValueConverter/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Connector\\ArrayConverter\\StandardToFlat\\ProductLocalized/Akeneo\\Pim\\Enrichment\\Component\\Product\\Connector\\ArrayConverter\\StandardToFlat\\ProductLocalized/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Connector\\ArrayConverter\\StandardToFlat\\ProductModel/Akeneo\\Pim\\Enrichment\\Component\\Product\\Connector\\ArrayConverter\\StandardToFlat\\ProductModel/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Connector\\Job\\JobParameters\\ConstraintCollectionProvider\\ProductCsvExport/Akeneo\\Pim\\Enrichment\\Component\\Product\\Connector\\Job\\JobParameters\\ConstraintCollectionProvider\\ProductCsvExport/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Connector\\Job\\JobParameters\\ConstraintCollectionProvider\\ProductCsvImport/Akeneo\\Pim\\Enrichment\\Component\\Product\\Connector\\Job\\JobParameters\\ConstraintCollectionProvider\\ProductCsvImport/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Connector\\Job\\JobParameters\\ConstraintCollectionProvider\\ProductModelCsvExport/Akeneo\\Pim\\Enrichment\\Component\\Product\\Connector\\Job\\JobParameters\\ConstraintCollectionProvider\\ProductModelCsvExport/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Connector\\Job\\JobParameters\\ConstraintCollectionProvider\\ProductModelCsvImport/Akeneo\\Pim\\Enrichment\\Component\\Product\\Connector\\Job\\JobParameters\\ConstraintCollectionProvider\\ProductModelCsvImport/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Connector\\Job\\JobParameters\\ConstraintCollectionProvider\\ProductXlsxExport/Akeneo\\Pim\\Enrichment\\Component\\Product\\Connector\\Job\\JobParameters\\ConstraintCollectionProvider\\ProductXlsxExport/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Connector\\Job\\JobParameters\\DefaultValuesProvider\\ProductCsvImport/Akeneo\\Pim\\Enrichment\\Component\\Product\\Connector\\Job\\JobParameters\\DefaultValueProvider\\ProductCsvImport/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Connector\\Job\\JobParameters\\DefaultValuesProvider\\ProductModelCsvExport/Akeneo\\Pim\\Enrichment\\Component\\Product\\Connector\\Job\\JobParameters\\DefaultValueProvider\\ProductModelCsvExport/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Connector\\Job\\JobParameters\\DefaultValuesProvider\\ProductModelCsvImport/Akeneo\\Pim\\Enrichment\\Component\\Product\\Connector\\Job\\JobParameters\\DefaultValueProvider\\ProductModelCsvImport/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Connector\\Job\\JobParameters\\DefaultValuesProvider\\ProductXlsxExport/Akeneo\\Pim\\Enrichment\\Component\\Product\\Connector\\Job\\JobParameters\\DefaultValueProvider\\ProductXlsxExport/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Connector\\Processor\\Denormalization\\Product\\FindProductToImport/Akeneo\\Pim\\Enrichment\\Component\\Product\\Connector\\Processor\\Denormalizer\\FindProductToImport/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Connector\\Processor\\Denormalization\\ProductAssociationProcessor/Akeneo\\Pim\\Enrichment\\Component\\Product\\Connector\\Processor\\Denormalizer\\ProductAssociationProcessor/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Connector\\Processor\\Denormalization\\ProductModelAssociationProcessor/Akeneo\\Pim\\Enrichment\\Component\\Product\\Connector\\Processor\\Denormalizer\\ProductModelAssociationProcessor/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Connector\\Processor\\Denormalization\\ProductModelLoaderProcessor/Akeneo\\Pim\\Enrichment\\Component\\Product\\Connector\\Processor\\Denormalizer\\ProductModelLoaderProcessor/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Connector\\Processor\\Denormalization\\ProductModelProcessor/Akeneo\\Pim\\Enrichment\\Component\\Product\\Connector\\Processor\\Denormalizer\\ProductModelProcessor/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Connector\\Processor\\Denormalization\\ProductProcessor/Akeneo\\Pim\\Enrichment\\Component\\Product\\Connector\\Processor\\Denormalizer\\ProductProcessor/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Connector\\Processor\\Normalization\\ProductProcessor/Akeneo\\Pim\\Enrichment\\Component\\Product\\Connector\\Processor\\Normalization\\ProductProcessor/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Connector\\Analyzer/Akeneo\\Pim\\Enrichment\\Component\\Product\\Connector/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Connector\\Writer\\File\\ProductColumnSorter/Akeneo\\Pim\\Enrichment\\Component\\Product\\Connector\\ProductColumnSorter/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Connector\\Reader\\Database\\GroupReader/Akeneo\\Pim\\Enrichment\\Component\\Product\\Connector\\Reader\\Database\\GroupReader/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Connector\\Reader\\Database\\ProductReader/Akeneo\\Pim\\Enrichment\\Component\\Product\\Connector\\Reader\\Database\\ProductReader/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Connector\\Reader\\File\\Csv\\ProductAssociationReader/Akeneo\\Pim\\Enrichment\\Component\\Product\\Connector\\Reader\\File\\Csv\\ProductAssociationReader/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Connector\\Reader\\File\\Csv\\ProductModelReader/Akeneo\\Pim\\Enrichment\\Component\\Product\\Connector\\Reader\\File\\Csv\\ProductModelReader/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Connector\\Reader\\File\\Csv\\ProductReader/Akeneo\\Pim\\Enrichment\\Component\\Product\\Connector\\Reader\\File\\Csv\\ProductReader/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Connector\\Reader\\File\\Xlsx\\ProductAssociationReader/Akeneo\\Pim\\Enrichment\\Component\\Product\\Connector\\Reader\\File\\Xlsx\\ProductAssociationReader/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Connector\\Reader\\File\\Xlsx\\ProductModelReader/Akeneo\\Pim\\Enrichment\\Component\\Product\\Connector\\Reader\\File\\Xlsx\\ProductModelReader/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Connector\\Reader\\File\\Xlsx\\ProductReader/Akeneo\\Pim\\Enrichment\\Component\\Product\\Connector\\Reader\\File\\Xlsx\\ProductReader/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Connector\\Reader\\File\\Xlsx\\ProductReader/Akeneo\\Pim\\Enrichment\\Component\\Product\\Connector\\Reader\\File\\Xlsx\\ProductReader/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Connector\\Writer\\Database\\ProductAssociationWriter/Akeneo\\Pim\\Enrichment\\Component\\Product\\Connector\\Writer\\Database\\ProductAssociationWriter/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Connector\\Writer\\Database\\ProductModelDescendantsWriter/Akeneo\\Pim\\Enrichment\\Component\\Product\\Connector\\Writer\\Database\\ProductModelDescendantsWriter/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Connector\\Writer\\Database\\ProductModelWriter/Akeneo\\Pim\\Enrichment\\Component\\Product\\Connector\\Writer\\Database\\ProductModelWriter/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Connector\\Writer\\Database\\ProductWriter/Akeneo\\Pim\\Enrichment\\Component\\Product\\Connector\\Writer\\Database\\ProductWriter/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Connector\\Writer\\File\\Csv\\ProductModelWriter/Akeneo\\Pim\\Enrichment\\Component\\Product\\Connector\\Writer\\File\\Csv\\ProductModelWriter/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Connector\\Writer\\File\\Csv\\ProductWriter/Akeneo\\Pim\\Enrichment\\Component\\Product\\Connector\\Writer\\File\\Csv\\ProductWriter/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Connector\\Writer\\File\\Xlsx\\ProductModelWriter/Akeneo\\Pim\\Enrichment\\Component\\Product\\Connector\\Writer\\File\\Xlsx\\ProductModelWriter/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Connector\\Writer\\File\\Xlsx\\ProductWriter/Akeneo\\Pim\\Enrichment\\Component\\Product\\Connector\\Writer\\File\\Xlsx\\ProductWriter/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Connector\\Validator\\Constraints/Akeneo\\Pim\\Enrichment\\Component\\Product\\Validator\\Constraints/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Connector\\ArrayConverter\\FlatToStandard\\User/Akeneo\\UserManagement\\Component\\Connector\\ArrayConverter\\FlatToStandard\\User/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Connector\\ArrayConverter\\StandardToFlat\\User/Akeneo\\UserManagement\\Component\\Connector\\ArrayConverter\\StandardToFlat\\User/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\ConnectorBundle\\Command\\AnalyzeProductCsvCommand/Akeneo\\Tool\\Bundle\\ConnectorBundle\\Command\\AnalyzeProductCsvCommand/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\ConnectorBundle\\DependencyInjection\\Compiler\\RegisterArchiversPass/Akeneo\\Tool\\Bundle\\ConnectorBundle\\DependencyInjection\\Compiler\\RegisterArchiversPass/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\ConnectorBundle\\DependencyInjection\\Compiler\\RegisterStandardToFlatConverterPass/Akeneo\\Tool\\Bundle\\ConnectorBundle\\DependencyInjection\\Compiler\\RegisterStandardToFlatConverterPass/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\ConnectorBundle\\DependencyInjection\\Compiler\\RegisterStandardToFlatConverterPass/Akeneo\\Tool\\Bundle\\ConnectorBundle\\DependencyInjection\\Compiler\\RegisterStandardToFlatConverterPass/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\ConnectorBundle\\DependencyInjection\\PimConnectorExtension/Akeneo\\Tool\\Bundle\\ConnectorBundle\\DependencyInjection\\PimConnectorExtension/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\ConnectorBundle\\Doctrine\\UnitOfWorkAndRepositoriesClearer/Akeneo\\Tool\\Bundle\\ConnectorBundle\\Doctrine\\UnitOfWorkAndRepositoriesClearer/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\ConnectorBundle\\EventListener\\ClearBatchCacheSubscriber/Akeneo\\Tool\\Bundle\\ConnectorBundle\\EventListener\\ClearBatchCacheSubscriber/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\ConnectorBundle\\EventListener\\InvalidItemsCollector/Akeneo\\Tool\\Bundle\\ConnectorBundle\\EventListener\\InvalidItemsCollector/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\ConnectorBundle\\EventListener\\JobExecutionAuthenticator/Akeneo\\Tool\\Bundle\\ConnectorBundle\\EventListener\\JobExecutionAuthenticator/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\ConnectorBundle\\EventListener\\ResetProcessedItemsBatchSubscriber/Akeneo\\Tool\\Bundle\\ConnectorBundle\\EventListener\\ResetProcessedItemsBatchSubscriber/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\ConnectorBundle\\PimConnectorBundle/Akeneo\\Tool\\Bundle\\ConnectorBundle\\PimConnectorBundle/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Connector\\Analyzer\\AnalyzerInterface/Akeneo\\Tool\\Component\\Connector\\Analyzer\\AnalyzerInterface/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Connector\\Archiver\\AbstractInvalidItemWriter/Akeneo\\Tool\\Component\\Connector\\Archiver\\AbstractInvalidItemWriter/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Connector\\Archiver\\AbstractFilesystemArchiver/Akeneo\\Tool\\Component\\Connector\\Archiver\\AbstractFilesystemArchiver/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Connector\\Archiver\\ArchivableFileWriterArchiver/Akeneo\\Tool\\Component\\Connector\\Archiver\\ArchivableFileWriterArchiver/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Connector\\Archiver\\ArchiverInterface/Akeneo\\Tool\\Component\\Connector\\Archiver\\ArchiverInterface/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Connector\\Archiver\\CsvInvalidItemWriter/Akeneo\\Tool\\Component\\Connector\\Archiver\\CsvInvalidItemWriter/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Connector\\Archiver\\FileReaderArchiver/Akeneo\\Tool\\Component\\Connector\\Archiver\\FileReaderArchiver/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Connector\\Archiver\\FileWriterArchiver/Akeneo\\Tool\\Component\\Connector\\Archiver\\FileWriterArchiver/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Connector\\Archiver\\XlsxInvalidItemWriter/Akeneo\\Tool\\Component\\Connector\\Archiver\\XlsxInvalidItemWriter/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Connector\\Archiver\\ZipFilesystemFactory/Akeneo\\Tool\\Component\\Connector\\Archiver\\XlsxInvalidItemWriter/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Connector\\ArrayConverter\\ArrayConverterInterface/Akeneo\\Tool\\Component\\Connector\\ArrayConverter\\ArrayConverterInterface/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Connector\\ArrayConverter\\DummyConverter/Akeneo\\Tool\\Component\\Connector\\ArrayConverter\\DummyConverter/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Connector\\ArrayConverter\\FieldSplitter/Akeneo\\Tool\\Component\\Connector\\ArrayConverter\\FieldSplitter/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Connector\\ArrayConverter\\FieldsRequirementChecker/Akeneo\\Tool\\Component\\Connector\\ArrayConverter\\FieldsRequirementChecker/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Connector\\ArrayConverter\\StandardToFlat\\AbstractSimpleArrayConverter/Akeneo\\Tool\\Component\\Connector\\ArrayConverter\\StandardToFlat\\AbstractSimpleArrayConverter/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Connector\\Encoder\\CsvEncoder/Akeneo\\Tool\\Component\\Connector\\Encoder\\CsvEncoder/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Connector\\Exception\\ArrayConversionException/Akeneo\\Tool\\Component\\Connector\\Exception\\ArrayConversionException/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Connector\\Exception\\CharsetException/Akeneo\\Tool\\Component\\Connector\\Exception\\CharsetException/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Connector\\Exception\\DataArrayConversionException/Akeneo\\Tool\\Component\\Connector\\Exception\\DataArrayConversionException/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Connector\\Exception\\InvalidItemFromViolationsException/Akeneo\\Tool\\Component\\Connector\\Exception\\InvalidItemFromViolationsException/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Connector\\Exception\\StructureArrayConversionException/Akeneo\\Tool\\Component\\Connector\\Exception\\StructureArrayConversionException/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Connector\\Item\\CharsetValidator/Akeneo\\Tool\\Component\\Connector\\Item\\CharsetValidator/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Connector\\Job\\JobParameters\\ConstraintCollectionProvider\\SimpleCsvExport/Akeneo\\Tool\\Component\\Connector\\Job\\JobParameters\\ConstraintCollectionProvider\\SimpleCsvExport/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Connector\\Job\\JobParameters\\ConstraintCollectionProvider\\SimpleCsvImport/Akeneo\\Tool\\Component\\Connector\\Job\\JobParameters\\ConstraintCollectionProvider\\SimpleCsvImport/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Connector\\Job\\JobParameters\\ConstraintCollectionProvider\\SimpleXlsxExport/Akeneo\\Tool\\Component\\Connector\\Job\\JobParameters\\ConstraintCollectionProvider\\SimpleXlsxExport/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Connector\\Job\\JobParameters\\ConstraintCollectionProvider\\SimpleXlsxImport/Akeneo\\Tool\\Component\\Connector\\Job\\JobParameters\\ConstraintCollectionProvider\\SimpleXlsxImport/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Connector\\Job\\JobParameters\\ConstraintCollectionProvider\\SimpleYamlExport/Akeneo\\Tool\\Component\\Connector\\Job\\JobParameters\\ConstraintCollectionProvider\\SimpleYamlExport/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Connector\\Job\\JobParameters\\ConstraintCollectionProvider\\SimpleYamlImport/Akeneo\\Tool\\Component\\Connector\\Job\\JobParameters\\ConstraintCollectionProvider\\SimpleYamlImport/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Connector\\Job\\JobParameters\\DefaultValuesProvider\\SimpleCsvExport/Akeneo\\Tool\\Component\\Connector\\Job\\JobParameters\\DefaultValuesProvider\\SimpleCsvExport/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Connector\\Job\\JobParameters\\DefaultValuesProvider\\SimpleCsvImport/Akeneo\\Tool\\Component\\Connector\\Job\\JobParameters\\DefaultValuesProvider\\SimpleCsvImport/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Connector\\Job\\JobParameters\\DefaultValuesProvider\\SimpleXlsxExport/Akeneo\\Tool\\Component\\Connector\\Job\\JobParameters\\DefaultValuesProvider\\SimpleXlsxExport/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Connector\\Job\\JobParameters\\DefaultValuesProvider\\SimpleXlsxImport/Akeneo\\Tool\\Component\\Connector\\Job\\JobParameters\\DefaultValuesProvider\\SimpleXlsxImport/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Connector\\Job\\JobParameters\\DefaultValuesProvider\\SimpleYamlExport/Akeneo\\Tool\\Component\\Connector\\Job\\JobParameters\\DefaultValuesProvider\\SimpleYamlExport/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Connector\\Job\\JobParameters\\DefaultValuesProvider\\SimpleYamlImport/Akeneo\\Tool\\Component\\Connector\\Job\\JobParameters\\DefaultValuesProvider\\SimpleYamlImport/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Connector\\Processor\\BulkMediaFetcher/Akeneo\\Tool\\Component\\Connector\\Processor\\BulkMediaFetcher/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Connector\\Processor\\Denormalization\\AbstractProcessor/Akeneo\\Tool\\Component\\Connector\\Processor\\Denormalization\\AbstractProcessor/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Connector\\Processor\\Denormalization\\JobInstanceProcessor/Akeneo\\Tool\\Component\\Connector\\Processor\\Denormalization\\JobInstanceProcessor/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Connector\\Processor\\Denormalization\\Processor/Akeneo\\Tool\\Component\\Connector\\Processor\\Denormalization\\Processor/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Connector\\Processor\\DummyItemProcessor/Akeneo\\Tool\\Component\\Connector\\Processor\\DummyItemProcessor/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Connector\\Processor\\Denormalization\\Processor/Akeneo\\Tool\\Component\\Connector\\Processor\\Denormalization\\Processor/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Connector\\Reader\\Database\\AbstractReader/Akeneo\\Tool\\Component\\Connector\\Reader\\Database\\AbstractReader/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Connector\\Reader\\DummyItemReader/Akeneo\\Tool\\Component\\Connector\\Reader\\DummyItemReader/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Connector\\Reader\\File\\ArrayReader/Akeneo\\Tool\\Component\\Connector\\Reader\\File\\ArrayReader/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Connector\\Reader\\File\\Csv\\Reader/Akeneo\\Tool\\Component\\Connector\\Reader\\File\\Csv\\Reader/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Connector\\Reader\\File\\Xlsx\\Reader/Akeneo\\Tool\\Component\\Connector\\Reader\\File\\Xlsx\\Reader/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Connector\\Reader\\File\\Yaml\\Reader/Akeneo\\Tool\\Component\\Connector\\Reader\\File\\Yaml\\Reader/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Connector\\Reader\\File\\FileIteratorFactory/Akeneo\\Tool\\Component\\Connector\\Reader\\File\\FileIteratorFactory/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Connector\\Reader\\File\\FileIteratorInterface/Akeneo\\Tool\\Component\\Connector\\Reader\\File\\FileIteratorInterface/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Connector\\Reader\\File\\MediaPathTransformer/Akeneo\\Tool\\Component\\Connector\\Reader\\File\\MediaPathTransformer/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Connector\\Reader\\File\\Yaml\\Reader/Akeneo\\Tool\\Component\\Connector\\Reader\\File\\Yaml\\Reader/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Connector\\Step\\TaskletInterface/Akeneo\\Tool\\Component\\Connector\\Step\\TaskletInterface/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Connector\\Step\\TaskletStep/Akeneo\\Tool\\Component\\Connector\\Step\\TaskletStep/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Connector\\Step\\ValidatorStep/Akeneo\\Tool\\Component\\Connector\\Step\\ValidatorStep/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Connector\\Writer\\Database\\Writer/Akeneo\\Tool\\Component\\Connector\\Writer\\Database\\Writer/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Connector\\Writer\\DummyItemWriter/Akeneo\\Tool\\Component\\Connector\\Writer\\DummyItemWriter/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Connector\\Writer\\File\\AbstractFileWriter/Akeneo\\Tool\\Component\\Connector\\Writer\\File\\AbstractFileWriter/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Connector\\Writer\\File\\ArchivableWriterInterface/Akeneo\\Tool\\Component\\Connector\\Writer\\File\\ArchivableWriterInterface/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Connector\\Writer\\File\\ColumnSorterInterface/Akeneo\\Tool\\Component\\Connector\\Writer\\File\\ColumnSorterInterface/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Connector\\Writer\\File\\DefaultColumnSorter/Akeneo\\Tool\\Component\\Connector\\Writer\\File\\DefaultColumnSorter/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Connector\\Writer\\File\\FileExporterPathGeneratorInterface/Akeneo\\Tool\\Component\\Connector\\Writer\\File\\FileExporterPathGeneratorInterface/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Connector\\Writer\\File\\FlatItemBuffer/Akeneo\\Tool\\Component\\Connector\\Writer\\File\\FlatItemBuffer/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Connector\\Writer\\File\\FlatItemBufferFlusher/Akeneo\\Tool\\Component\\Connector\\Writer\\File\\FlatItemBufferFlusher/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Connector\\Writer\\File\\MediaExporterPathGenerator/Akeneo\\Tool\\Component\\Connector\\Writer\\File\\MediaExporterPathGenerator/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Connector\\Writer\\File\\Csv\\Writer/Akeneo\\Tool\\Component\\Connector\\Writer\\File\\Csv\\Writer/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Connector\\Writer\\File\\Yaml\\Writer/Akeneo\\Tool\\Component\\Connector\\Writer\\File\\Yaml\\Writer/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\Connector/Akeneo\\Tool\\Bundle\\ConnectorBundle/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\NotificationBundle/Akeneo\\Platform\\Bundle\\NotificationBundle/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\DashboardBundle\\Widget\\CompletenessWidget/Akeneo\\Pim\\Enrichment\\Bundle\\Widget\\CompletenessWidget/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\DashboardBundle\\Controller\\WidgetController/Akeneo\\Platform\\Bundle\\DashboardBundle\\Controller\\WidgetController/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\DashboardBundle\\DependencyInjection\\Compiler\\RegisterWidgetsPass/Akeneo\\Platform\\Bundle\\DashboardBundle\\DependencyInjection\\Compiler\\RegisterWidgetsPass/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\DashboardBundle\\DependencyInjection\\PimDashboardExtension/Akeneo\\Platform\\Bundle\\DashboardBundle\\DependencyInjection\\PimDashboardExtension/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\DashboardBundle\\PimDashboardBundle/Akeneo\\Platform\\Bundle\\DashboardBundle\\PimDashboardBundle/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\DashboardBundle\\Widget\\LastOperationsWidget/Akeneo\\Platform\\Bundle\\DashboardBundle\\Widget\\LastOperationsWidget/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\DashboardBundle\\Widget\\Registry/Akeneo\\Platform\\Bundle\\DashboardBundle\\Widget\\Registry/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\DashboardBundle\\Widget\\WidgetInterface/Akeneo\\Platform\\Bundle\\DashboardBundle\\Widget\\WidgetInterface/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\UIBundle/Akeneo\\Platform\\Bundle\\UIBundle/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\ImportExportBundle/Akeneo\\Platform\\Bundle\\ImportExportBundle/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\AnalyticsBundle/Akeneo\\Platform\\Bundle\\AnalyticsBundle/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogVolumeMonitoringBundle/Akeneo\\Platform\\Bundle\\CatalogVolumeMonitoringBundle/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\CatalogVolumeMonitoring/Akeneo\\Platform\\Component\\CatalogVolumeMonitoring/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\DataGridBundle/Oro\\Bundle\\PimDataGridBundle/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\FilterBundle\\Filter\\CompletenessFilter/Oro\\Bundle\\PimFilterBundle\\Filter\\ProductCompletenessFilter/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\FilterBundle/Oro\\Bundle\\PimFilterBundle/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\Normalizer\\CategoryNormalizer/Akeneo\\Pim\\Enrichment\\Component\\Category\\Normalizer\\InternalApi\\CategoryNormalizer/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\Normalizer\\CollectionNormalizer/Akeneo\\Pim\\Enrichment\\Component\\Product\\Normalizer\\InternalApi\\CollectionNormalizer/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\Normalizer\\CompletenessCollectionNormalizer/Akeneo\\Pim\\Enrichment\\Component\\Product\\Normalizer\\InternalApi\\CompletenessCollectionNormalizer/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\Normalizer\\CompletenessNormalizer/Akeneo\\Pim\\Enrichment\\Component\\Product\\Normalizer\\InternalApi\\CompletenessNormalizer/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\Normalizer\\EntityWithFamilyVariantNormalizer/Akeneo\\Pim\\Enrichment\\Component\\Product\\Normalizer\\InternalApi\\EntityWithFamilyVariantNormalizer/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\Normalizer\\GroupNormalizer/Akeneo\\Pim\\Enrichment\\Component\\Product\\Normalizer\\InternalApi\\GroupNormalizer/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\Normalizer\\GroupViolationNormalizer/Akeneo\\Pim\\Enrichment\\Component\\Product\\Normalizer\\InternalApi\\GroupViolationNormalizer/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\Normalizer\\ImageNormalizer/Akeneo\\Pim\\Enrichment\\Component\\Product\\Normalizer\\InternalApi\\ImageNormalizer/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\Normalizer\\IncompleteValuesNormalizer/Akeneo\\Pim\\Enrichment\\Component\\Product\\Normalizer\\InternalApi\\IncompleteValuesNormalizer/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\Normalizer\\ProductModelNormalizer/Akeneo\\Pim\\Enrichment\\Component\\Product\\Normalizer\\InternalApi\\ProductModelNormalizer/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\Normalizer\\ProductNormalizer/Akeneo\\Pim\\Enrichment\\Component\\Product\\Normalizer\\InternalApi\\ProductNormalizer/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\Normalizer\\ProductViolationNormalizer/Akeneo\\Pim\\Enrichment\\Component\\Product\\Normalizer\\InternalApi\\ProductViolationNormalizer/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\Normalizer\\VariantNavigationNormalizer/Akeneo\\Pim\\Enrichment\\Component\\Product\\Normalizer\\InternalApi\\VariantNavigationNormalizer/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\Normalizer\\VersionNormalizer/Akeneo\\Pim\\Enrichment\\Component\\Product\\Normalizer\\InternalApi\\VersionNormalizer/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\Normalizer\\ViolationNormalizer/Akeneo\\Pim\\Enrichment\\Component\\Product\\Normalizer\\InternalApi\\ViolationNormalizer/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\Normalizer\\AttributeOptionValueCollectionNormalizer/Akeneo\\Pim\\Structure\\Component\\Normalizer\\InternalApi\\AttributeOptionValueCollectionNormalizer/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\Normalizer\\AttributeOptionValueNormalizer/Akeneo\\Pim\\Structure\\Component\\Normalizer\\InternalApi\\AttributeOptionValueNormalizer/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\Normalizer\\VersionedAttributeNormalizer/Akeneo\\Pim\\Structure\\Component\\Normalizer\\InternalApi\\VersionedAttributeNormalizer/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\Normalizer\\FileNormalizer/Akeneo\\Pim\\Enrichment\\Component\\Product\\Normalizer\\InternalApi\\FileNormalizer/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\Normalizer\\DatagridViewNormalizer/Oro\\Bundle\\PimDataGridBundle\\Normalizer\\InternalApi\\DatagridViewNormalizer/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\Doctrine\\ORM\\Repository\\ChannelRepository/Akeneo\\Channel\\Component\\Repository\\InternalApi\\ChannelRepository/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\Doctrine\\ORM\\Repository\\CurrencyRepository/Akeneo\\Channel\\Component\\Repository\\InternalApi\\CurrencyRepository/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\Doctrine\\ORM\\Repository\\LocaleRepository/Akeneo\\Channel\\Component\\Repository\\InternalApi\\LocaleRepository/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\Cursor\\SequentialEditProduct/Akeneo\\Pim\\Enrichment\\Bundle\\Cursor\\SequentialEditProduct/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\Doctrine\\Counter\\CategoryItemsCounter/Akeneo\\Pim\\Enrichment\\Bundle\\Doctrine\\ORM\\Counter\\CategoryItemsCounter/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\Doctrine\\Counter\\CategoryItemsCounterInterface/Akeneo\\Pim\\Enrichment\\Bundle\\Doctrine\\ORM\\Counter\\CategoryItemsCounterInterface/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\Doctrine\\Counter\\CategoryItemsCounterRegistry/Akeneo\\Pim\\Enrichment\\Bundle\\Doctrine\\ORM\\Counter\\CategoryItemsCounterRegistry/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\Doctrine\\Counter\\CategoryItemsCounterRegistryInterface/Akeneo\\Pim\\Enrichment\\Bundle\\Doctrine\\ORM\\Counter\\CategoryItemsCounterRegistryInterface/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\Doctrine\\Counter\\CategoryProductsCounter/Akeneo\\Pim\\Enrichment\\Bundle\\Doctrine\\ORM\\Counter\\CategoryProductsCounter/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\Doctrine\\ORM\\Query\\AscendantCategories/Akeneo\\Pim\\Enrichment\\Bundle\\Doctrine\\ORM\\Query\\AscendantCategories/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\Doctrine\\ORM\\Query\\CountImpactedProducts/Akeneo\\Pim\\Enrichment\\Bundle\\Doctrine\\ORM\\Query\\CountImpactedProducts/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\Doctrine\\ORM\\Repository\\CategoryRepository/Akeneo\\Pim\\Enrichment\\Bundle\\Doctrine\\ORM\\Repository\\InternalApi\\CategoryRepository/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\Doctrine\\ORM\\Repository\\GroupRepository/Akeneo\\Pim\\Enrichment\\Bundle\\Doctrine\\ORM\\Repository\\InternalApi\\GroupRepository/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\Elasticsearch\\FromSizeCursor/Akeneo\\Pim\\Enrichment\\Bundle\\Elasticsearch\\FromSizeCursor/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\Elasticsearch\\FromSizeCursorFactory/Akeneo\\Pim\\Enrichment\\Bundle\\Elasticsearch\\FromSizeCursorFactory/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\Elasticsearch\\Sorter\\InGroupSorter/Akeneo\\Pim\\Enrichment\\Bundle\\Elasticsearch\\Sorter\\InGroupSorter/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\Doctrine\\ORM\\Repository\\JobExecutionRepository/Akeneo\\Platform\\Bundle\\ImportExportBundle\\Repository\\InternalApi\\JobExecutionRepository/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\Doctrine\\ORM\\Repository\\JobInstanceRepository/Akeneo\\Platform\\Bundle\\ImportExportBundle\\Repository\\InternalApi\\JobInstanceRepository/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\Doctrine\\ORM\\Repository\\JobTrackerRepository/Akeneo\\Platform\\Bundle\\ImportExportBundle\\Repository\\InternalApi\\JobTrackerRepository/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\Doctrine\\ORM\\Query\\CountImpactedProducts\\ItemsCounter/Akeneo\\Pim\\Enrichment\\Bundle\\Doctrine\\ORM\\Query\\CountImpactedProducts\\ItemsCounter/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\Doctrine\\ORM\\Repository\\ClientRepository/Oro\\Bundle\\PimDataGridBundle\\Repository\\ClientRepository/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\Form\\Handler\\GroupHandler/Akeneo\\Pim\\Enrichment\\Bundle\\Form\\Handler\\GroupHandler/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\Form\\Subscriber\\BindAssociationTargetsSubscriber/Akeneo\\Pim\\Enrichment\\Bundle\\Form\\Subscriber\\BindAssociationTargetsSubscriber/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\Form\\Subscriber\\FilterLocaleSpecificValueSubscriber/Akeneo\\Pim\\Enrichment\\Bundle\\Form\\Subscriber\\FilterLocaleSpecificValueSubscriber/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\Form\\Subscriber\\FilterLocaleValueSubscriber/Akeneo\\Pim\\Enrichment\\Bundle\\Form\\Subscriber\\FilterLocaleValueSubscriber/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\Form\\Subscriber\\FixArrayToStringListener/Akeneo\\Pim\\Enrichment\\Bundle\\Form\\Subscriber\\FixArrayToStringListener/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\Form\\Type\\CategoryType/Akeneo\\Pim\\Enrichment\\Bundle\\Form\\Type\\CategoryType/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\Form\\Type\\GroupType/Akeneo\\Pim\\Enrichment\\Bundle\\Form\\Type\\GroupType/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\Form\\Type\\SelectFamilyType/Akeneo\\Pim\\Enrichment\\Bundle\\Form\\Type\\SelectFamilyType/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\Form\\Type\\AssociationTypeType/Akeneo\\Pim\\Structure\\Bundle\\Form\\Type\\AssociationTypeType/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\Form\\Type\\AvailableAttributesType/Akeneo\\Pim\\Structure\\Bundle\\Form\\Type\\AvailableAttributesType/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\Form\\Exception\\FormException/Akeneo\\Platform\\Bundle\\UIBundle\\Form\\Exception\\FormException/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\Form\\Factory\\IdentifiableModelTransformerFactory/Akeneo\\Platform\\Bundle\\UIBundle\\Form\\Factory\\IdentifiableModelTransformerFactory/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\Form\\Subscriber\\AddTranslatableFieldSubscriber/Akeneo\\Platform\\Bundle\\UIBundle\\Form\\Subscriber\\AddTranslatableFieldSubscriber/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\Form\\Subscriber\\DisableFieldSubscriber/Akeneo\\Platform\\Bundle\\UIBundle\\Form\\Subscriber\\DisableFieldSubscriber/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\Form\\DataTransformer\\ArrayToStringTransformer/Akeneo\\Platform\\Bundle\\UIBundle\\Form\\Transformer\\ArrayToStringTransformer/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\Form\\DataTransformer\\EntitiesToIdsTransformer/Akeneo\\Platform\\Bundle\\UIBundle\\Form\\Transformer\\EntitiesToIdsTransformer/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\Form\\DataTransformer\\EntityToIdTransformer/Akeneo\\Platform\\Bundle\\UIBundle\\Form\\Transformer\\EntityToIdTransformer/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\Form\\DataTransformer\\EntityToIdentifierTransformer/Akeneo\\Platform\\Bundle\\UIBundle\\Form\\Transformer\\EntityToIdentifierTransformer/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\Form\\DataTransformer\\IdentifiableModelTransformer/Akeneo\\Platform\\Bundle\\UIBundle\\Form\\Transformer\\IdentifiableModelTransformer/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\Form\\DataTransformer\\StringToBooleanTransformer/Akeneo\\Platform\\Bundle\\UIBundle\\Form\\Transformer\\StringToBooleanTransformer/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\Form\\Type\\AsyncSelectType/Akeneo\\Platform\\Bundle\\UIBundle\\Form\\Type\\AsyncSelectType/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\Form\\Type\\EntityIdentifierType/Akeneo\\Platform\\Bundle\\UIBundle\\Form\\Type\\EntityIdentifierType/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\Form\\Type\\LocalizedCollectionType/Akeneo\\Platform\\Bundle\\UIBundle\\Form\\Type\\LocalizedCollectionType/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\Form\\Type\\MediaType/Akeneo\\Platform\\Bundle\\UIBundle\\Form\\Type\\MediaType/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\Form\\Type\\ObjectIdentifierType/Akeneo\\Platform\\Bundle\\UIBundle\\Form\\Type\\ObjectIdentifierType/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\Form\\Type\\ProductGridFilterChoiceType/Akeneo\\Platform\\Bundle\\UIBundle\\Form\\Type\\ProductGridFilterChoiceType/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\Form\\Type\\UploadType/Akeneo\\Platform\\Bundle\\UIBundle\\Form\\Type\\UploadType/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\Provider\\Form\\ProductFormProvider/Akeneo\\Pim\\Enrichment\\Bundle\\Provider\\Form\\ProductFormProvider/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\Provider\\Form\\ProductModelFormProvider/Akeneo\\Pim\\Enrichment\\Bundle\\Provider\\Form\\ProductModelFormProvider/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\Provider\\Form\\JobInstanceFormProvider/Akeneo\\Platform\\Bundle\\ImportExportBundle\\Provider\\Form\\JobInstanceFormProvider/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\Provider\\EmptyValue\\BaseEmptyValueProvider/Akeneo\\Platform\\Bundle\\UIBundle\\Provider\\EmptyValue\\BaseEmptyValueProvider/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\Provider\\EmptyValue\\EmptyValueChainedProvider/Akeneo\\Platform\\Bundle\\UIBundle\\Provider\\EmptyValue\\EmptyValueChainedProvider/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\Provider\\EmptyValue\\EmptyValueProviderInterface/Akeneo\\Platform\\Bundle\\UIBundle\\Provider\\EmptyValue\\EmptyValueProviderInterface/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\Provider\\Field\\BaseFieldProvider/Akeneo\\Platform\\Bundle\\UIBundle\\Provider\\Field\\BaseFieldProvider/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\Provider\\Field\\FieldChainedProvider/Akeneo\\Platform\\Bundle\\UIBundle\\Provider\\Field\\FieldChainedProvider/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\Provider\\Field\\FieldProviderInterface/Akeneo\\Platform\\Bundle\\UIBundle\\Provider\\Field\\FieldProviderInterface/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\Provider\\Field\\WysiwygFieldProvider/Akeneo\\Platform\\Bundle\\UIBundle\\Provider\\Field\\WysiwygFieldProvider/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\Provider\\Filter\\BaseFilterProvider/Akeneo\\Platform\\Bundle\\UIBundle\\Provider\\Filter\\BaseFilterProvider/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\Provider\\Filter\\FilterChainedProvider/Akeneo\\Platform\\Bundle\\UIBundle\\Provider\\Filter\\FilterChainedProvider/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\Provider\\Filter\\FilterProviderInterface/Akeneo\\Platform\\Bundle\\UIBundle\\Provider\\Filter\\FilterProviderInterface/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\Provider\\Form\\FormChainedProvider/Akeneo\\Platform\\Bundle\\UIBundle\\Provider\\Form\\FormChainedProvider/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\Provider\\Form\\FormProviderInterface/Akeneo\\Platform\\Bundle\\UIBundle\\Provider\\Form\\FormProviderInterface/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\Provider\\Form\\NoCompatibleFormProviderFoundException/Akeneo\\Platform\\Bundle\\UIBundle\\Provider\\Form\\NoCompatibleFormProviderFoundException/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\Provider\\FormExtensionProvider/Akeneo\\Platform\\Bundle\\UIBundle\\Provider\\FormExtensionProvider/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\Provider\\StructureVersion\\StructureVersionProviderInterface/Akeneo\\Platform\\Bundle\\UIBundle\\Provider\\StructureVersion\\StructureVersionProviderInterface/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\Controller\\Rest\\CurrencyController/Akeneo\\Channel\\Bundle\\Controller\\InternalApi\\CurrencyController/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\Controller\\CurrencyController/Akeneo\\Channel\\Bundle\\Controller\\UI\\CurrencyController/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\Controller\\Rest\\CategoryController/Akeneo\\Pim\\Enrichment\\Bundle\\Controller\\InternalApi\\CategoryController/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\Controller\\Rest\\GroupController/Akeneo\\Pim\\Enrichment\\Bundle\\Controller\\InternalApi\\GroupController/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\Controller\\Rest\\MassEditController/Akeneo\\Pim\\Enrichment\\Bundle\\Controller\\InternalApi\\MassEditController/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\Controller\\Rest\\MediaController/Akeneo\\Pim\\Enrichment\\Bundle\\Controller\\InternalApi\\MediaController/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\Controller\\Rest\\ProductCategoryController/Akeneo\\Pim\\Enrichment\\Bundle\\Controller\\InternalApi\\ProductCategoryController/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\Controller\\Rest\\ProductCommentController/Akeneo\\Pim\\Enrichment\\Bundle\\Controller\\InternalApi\\ProductCommentController/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\Controller\\Rest\\ProductController/Akeneo\\Pim\\Enrichment\\Bundle\\Controller\\InternalApi\\ProductController/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\Controller\\Rest\\SequentialEditController/Akeneo\\Pim\\Enrichment\\Bundle\\Controller\\InternalApi\\SequentialEditController/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\Controller\\Rest\\ValuesController/Akeneo\\Pim\\Enrichment\\Bundle\\Controller\\InternalApi\\ValuesController/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\Controller\\Rest\\VersioningController/Akeneo\\Pim\\Enrichment\\Bundle\\Controller\\InternalApi\\VersioningController/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\Controller\\AbstractListCategoryController/Akeneo\\Pim\\Enrichment\\Bundle\\Controller\\Ui\\AbstractListCategoryController/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\Controller\\FileController/Akeneo\\Pim\\Enrichment\\Bundle\\Controller\\Ui\\FileController/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\Controller\\ProductModelController/Akeneo\\Pim\\Enrichment\\Bundle\\Controller\\Ui\\ProductModelController/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\Controller\\Rest\\JobInstanceController/Akeneo\\Platform\\Bundle\\ImportExportBundle\\Controller\\InternalApi\\JobInstanceController/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\Controller\\JobTrackerController/Akeneo\\Platform\\Bundle\\ImportExportBundle\\Controller\\Ui\\JobTrackerController/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\Controller\\Rest\\FormExtensionController/Akeneo\\Platform\\Bundle\\UIBundle\\Controller\\InternalApi\\FormExtensionController/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\Twig\\LocaleExtension/Akeneo\\Channel\\Bundle\\Twig\\LocaleExtension/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\Twig\\CategoryExtension/Akeneo\\Pim\\Enrichment\\Bundle\\Twig\\CategoryExtension/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\Twig\\CategoryExtension/Akeneo\\Pim\\Structure\\Bundle\\Twig\\CategoryExtension/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\Twig\\ObjectClassExtension/Akeneo\\Platform\\Bundle\\UIBundle\\Twig\\ObjectClassExtension/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\Twig\\TranslationsExtension/Akeneo\\Platform\\Bundle\\UIBundle\\Twig\\TranslationsExtension/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\Twig\\VersionExtension/Akeneo\\Platform\\Bundle\\UIBundle\\Twig\\VersionExtension/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\Twig\\ViewElementExtension/Akeneo\\Platform\\Bundle\\UIBundle\\Twig\\ViewElementExtension/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\Exception\\MissingOptionException/Akeneo\\Platform\\Bundle\\UIBundle\\Exception\\MissingOptionException/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\File\\DefaultImageProvider/Akeneo\\Pim\\Enrichment\\Bundle\\File\\DefaultImageProvider/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\File\\DefaultImageProviderInterface/Akeneo\\Pim\\Enrichment\\Bundle\\File\\DefaultImageProviderInterface/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\File\\FileTypeGuesser/Akeneo\\Pim\\Enrichment\\Bundle\\File\\FileTypeGuesser/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\File\\FileTypeGuesserInterface/Akeneo\\Pim\\Enrichment\\Bundle\\File\\FileTypeGuesserInterface/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\File\\FileTypes/Akeneo\\Pim\\Enrichment\\Bundle\\File\\FileTypes/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\Event\\AttributeGroupEvents/Akeneo\\Pim\\Structure\\Bundle\\Event\\AttributeGroupEvents/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\Factory\\MassEditNotificationFactory/Akeneo\\Platform\\Bundle\\ImportExportBundle\\Factory\\MassEditNotificationFactory/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\Mailer\\MailRecorder/Akeneo\\Platform\\Bundle\\ImportExportBundle\\Test\\MailRecorder/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\Flash\\Message/Akeneo\\Platform\\Bundle\\UIBundle\\Flash\\Message/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\Filter\\ProductEditDataFilter/Akeneo\\Pim\\Enrichment\\Bundle\\Filter\\ProductEditDataFilter/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\Filter\\ProductValuesEditDataFilter/Akeneo\\Pim\\Enrichment\\Bundle\\Filter\\ProductValuesEditDataFilter/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\Imagine\\Loader\\FlysystemLoader/Akeneo\\Platform\\Bundle\\UIBundle\\Imagine\\FlysystemLoader/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\Resolver\\LocaleResolver/Akeneo\\Platform\\Bundle\\UIBundle\\Resolver\\LocaleResolver/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\VersionStrategy\\CacheBusterVersionStrategy/Akeneo\\Platform\\Bundle\\UIBundle\\VersionStrategy\\CacheBusterVersionStrategy/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\ViewElement/Akeneo\\Platform\\Bundle\\UIBundle\\ViewElement/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\Extension\\Action\\Actions\\DeleteProductAction/Akeneo\\Pim\\Enrichment\\Bundle\\Extension\\Action\\DeleteProductAction/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\Extension\\Action\\Actions\\EditInModalAction/Akeneo\\Pim\\Enrichment\\Bundle\\Extension\\Action\\EditInModalAction/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\Extension\\Action\\Actions\\NavigateProductAndProductModelAction/Akeneo\\Pim\\Enrichment\\Bundle\\Extension\\Action\\NavigateProductAndProductModelAction/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\Extension\\Action\\Actions\\ToggleProductAction/Akeneo\\Pim\\Enrichment\\Bundle\\Extension\\Action\\ToggleProductAction/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\MassEditAction\\Operation\\BatchableOperationInterface/Akeneo\\Pim\\Enrichment\\Bundle\\MassEditAction\\Operation\\BatchableOperationInterface/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\MassEditAction\\Operation\\MassEditOperation/Akeneo\\Pim\\Enrichment\\Bundle\\MassEditAction\\Operation\\MassEditOperation/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\MassEditAction\\OperationJobLauncher/Akeneo\\Pim\\Enrichment\\Bundle\\MassEditAction\\OperationJobLauncher/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\ProductQueryBuilder\\Filter\\DummyFilter/Akeneo\\Pim\\Enrichment\\Bundle\\ProductQueryBuilder\\Filter\\DummyFilter/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\ProductQueryBuilder\\ProductAndProductModelQueryBuilder/Akeneo\\Pim\\Enrichment\\Bundle\\ProductQueryBuilder\\ProductAndProductModelQueryBuilder/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\StructureVersion\\EventListener\\StructureVersionUpdater/Akeneo\\Pim\\Enrichment\\Bundle\\StructureVersion\\EventListener\\StructureVersionUpdater/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\StructureVersion\\EventListener\\TableCreator/Akeneo\\Pim\\Enrichment\\Bundle\\StructureVersion\\EventListener\\TableCreator/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\StructureVersion\\Provider\\StructureVersion/Akeneo\\Pim\\Enrichment\\Bundle\\StructureVersion\\Provider\\StructureVersion/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\EventListener\\AddLocaleListener/Akeneo\\Platform\\Bundle\\UIBundle\\EventListener\\AddLocaleListener/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\EventListener\\CloseSessionListener/Akeneo\\Platform\\Bundle\\UIBundle\\EventListener\\AddLocaleListener/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\EventListener\\ExceptionListener/Akeneo\\Platform\\Bundle\\UIBundle\\EventListener\\ExceptionListener/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\EventListener\\TranslateFlashMessagesSubscriber/Akeneo\\Platform\\Bundle\\UIBundle\\EventListener\\TranslateFlashMessagesSubscriber/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnrichBundle\\EventListener\\UserContextListener/Akeneo\\Platform\\Bundle\\UIBundle\\EventListener\\UserContextListener/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Enrich\\Converter\\ConverterInterface/Akeneo\\Pim\\Enrichment\\Component\\Product\\Converter\\ConverterInterface/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Enrich\\Converter\\EnrichToStandard\\ValueConverter/Akeneo\\Pim\\Enrichment\\Component\\Product\\Converter\\InternalApiToStandard\\ValueConverter/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Enrich\\Converter\\StandardToEnrich\\ValueConverter/Akeneo\\Pim\\Enrichment\\Component\\Product\\Converter\\StandardToInternalApi\\ValueConverter/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Enrich\\Converter\\MassOperationConverter/Akeneo\\Pim\\Enrichment\\Component\\Product\\Converter\\MassOperationConverter/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Enrich\\Job\\DeleteProductsAndProductModelsTasklet/Akeneo\\Pim\\Enrichment\\Component\\Product\\Job\\DeleteProductsAndProductModelsTasklet/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Enrich\\Query\\AscendantCategoriesInterface/Akeneo\\Pim\\Enrichment\\Component\\Category\\Query\\AscendantCategoriesInterface/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Enrich\\Model\\AvailableAttributes/Akeneo\\Pim\\Structure\\Component\\Model\\AvailableAttributes/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Enrich\\Provider\\TranslatedLabelsProviderInterface/Akeneo\\Platform\\Bundle\\UIBundle\\Provider\\TranslatedLabelsProviderInterface/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\Doctrine\\ORM\\Query\\AttributeIsAFamilyVariantAxis/Akeneo\\Pim\\Enrichment\\Bundle\\Doctrine\\ORM\\Query\\AttributeIsAFamilyVariantAxis/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\Doctrine\\ORM\\Query\\CompleteFilter/Akeneo\\Pim\\Enrichment\\Bundle\\Doctrine\\ORM\\Query\\CompleteFilter/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\Doctrine\\ORM\\Query\\CountEntityWithFamilyVariant/Akeneo\\Pim\\Enrichment\\Bundle\\Doctrine\\ORM\\Query\\CountEntityWithFamilyVariant/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\Doctrine\\ORM\\Query\\CountProductsWithFamily/Akeneo\\Pim\\Enrichment\\Bundle\\Doctrine\\ORM\\Query\\CountProductsWithFamily/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\Doctrine\\ORM\\Query\\VariantProductRatio/Akeneo\\Pim\\Enrichment\\Bundle\\Doctrine\\ORM\\Query\\VariantProductRatio/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\Doctrine\\ORM\\Repository\\AssociationRepository/Akeneo\\Pim\\Enrichment\\Bundle\\Doctrine\\ORM\\Repository\\AssociationRepository/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\Doctrine\\ORM\\Repository\\CompletenessRepository/Akeneo\\Pim\\Enrichment\\Bundle\\Doctrine\\ORM\\Repository\\CompletenessRepository/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\Doctrine\\ORM\\Repository\\EntityWithFamilyVariantRepository/Akeneo\\Pim\\Enrichment\\Bundle\\Doctrine\\ORM\\Repository\\EntityWithFamilyVariantRepository/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\Doctrine\\ORM\\Repository\\GroupRepository/Akeneo\\Pim\\Enrichment\\Bundle\\Doctrine\\ORM\\Repository\\GroupRepository/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\Doctrine\\ORM\\Repository\\ProductCategoryRepository/Akeneo\\Pim\\Enrichment\\Bundle\\Doctrine\\ORM\\Repository\\ProductCategoryRepository/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\Doctrine\\ORM\\Repository\\ProductMassActionRepository/Akeneo\\Pim\\Enrichment\\Bundle\\Doctrine\\ORM\\Repository\\ProductMassActionRepository/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\Doctrine\\ORM\\Repository\\ProductModelCategoryRepository/Akeneo\\Pim\\Enrichment\\Bundle\\Doctrine\\ORM\\Repository\\ProductModelCategoryRepository/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\Doctrine\\ORM\\Repository\\ProductModelRepository/Akeneo\\Pim\\Enrichment\\Bundle\\Doctrine\\ORM\\Repository\\ProductModelRepository/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\Doctrine\\ORM\\Repository\\ProductRepository/Akeneo\\Pim\\Enrichment\\Bundle\\Doctrine\\ORM\\Repository\\ProductRepository/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\Doctrine\\ORM\\Repository\\ProductUniqueDataRepository/Akeneo\\Pim\\Enrichment\\Bundle\\Doctrine\\ORM\\Repository\\ProductUniqueDataRepository/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\Doctrine\\ORM\\Repository\\VariantProductRepository/Akeneo\\Pim\\Enrichment\\Bundle\\Doctrine\\ORM\\Repository\\VariantProductRepository/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\Doctrine\\ORM\\CompletenessRemover/Akeneo\\Pim\\Enrichment\\Bundle\\Doctrine\\ORM\\CompletenessRemover/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\Doctrine\\ORM\\QueryBuilderUtility/Akeneo\\Pim\\Enrichment\\Bundle\\Doctrine\\ORM\\QueryBuilderUtility/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\/Akeneo\\Pim\\Enrichment\\Bundle\\Doctrine\\ORM\\QueryBuilderUtility/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Akeneo\\Bundle\\ElasticsearchBundle/Akeneo\\Tool\\Bundle\\ElasticsearchBundle/g'

find ./src/ -type f -print0 | xargs -0 sed -i 's/\(UserContext.[gs]et(['\''\"'']\)firstName/\1first_name/gi'
find ./src/ -type f -print0 | xargs -0 sed -i 's/\(UserContext.[gs]et(['\''\"'']\)lastName/\1last_name/gi'
find ./src/ -type f -print0 | xargs -0 sed -i 's/%pim_enrich.provider.form.job_instance.class%/Akeneo\\Platform\\Bundle\\ImportExportBundle\\Provider\\Form\\JobInstanceFormProvider/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/%pim_connector.array_converter.flat_to_standard.product.attribute_columns_resolver.class%/Akeneo\\Pim\\Enrichment\\Component\\Product\\Connector\\ArrayConverter\\FlatToStandard\\AttributeColumnsResolver/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/%pim_connector.array_converter.flat_to_standard.product.class%/Akeneo\\Pim\\Enrichment\\Component\\Product\\Connector\\ArrayConverter\\FlatToStandard\\Product/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/%pim_connector.writer.file.product.column_sorter.class%/Akeneo\\Pim\\Enrichment\\Component\\Product\\Connector\\ProductColumnSorter/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/%pim_enrich.connector.job.job_parameters.default_values_provider.product_quick_export.class%/Akeneo\\Pim\\Enrichment\\Component\\Product\\Connector\\Job\\JobParameters\\DefaultValueProvider\\ProductQuickExport/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/%pim_enrich.connector.job.job_parameters.constraint_collection_provider.product_and_product_model_quick_export.class%/Akeneo\\Pim\\Enrichment\\Component\\Product\\Connector\\Job\\JobParameters\\ConstraintCollectionProvider\\ProductAndProductModelQuickExport/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/@pim_connector.job.job_parameters.default_values_provider.simple_xlsx_export/@akeneo_pim_enrichment.job.job_parameters.default_values_provider.simple_xlsx_export/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/%pim_connector.job.job_parameters.default_values_provider.product_xlsx_export.class%/Akeneo\\Pim\\Enrichment\\Component\\Product\\Connector\\Job\\JobParameters\\DefaultValueProvider\\ProductXlsxExport/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/%pim_connector.job.job_parameters.constraint_collection_provider.product_xlsx_export.class%/Akeneo\\Pim\\Enrichment\\Component\\Product\\Connector\\Job\\JobParameters\\ConstraintCollectionProvider\\ProductXlsxExport/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\UserBundle/Akeneo\\UserManagement\\Bundle/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\User/Akeneo\\UserManagement\\Component/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\ApiBundle/Akeneo\\Tool\\Bundle\\ApiBundle/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Akeneo\\Component\\Batch/Akeneo\\Tool\\Component\\Batch/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Akeneo\\Bundle\\BatchBundle/Akeneo\\Tool\\Bundle\\BatchBundle/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Akeneo\\Component\\BatchQueue/Akeneo\\Tool\\Component\\BatchQueue/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Akeneo\\Bundle\\BatchQueueBundle/Akeneo\\Tool\\Bundle\\BatchQueueBundle/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Akeneo\\Component\\StorageUtils/Akeneo\\Tool\\Component\\StorageUtils/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Akeneo\\Bundle\\StorageUtilsBundle/Akeneo\\Tool\\Bundle\\StorageUtilsBundle/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Akeneo\\Bundle\\ElasticsearchBundle/Akeneo\\Tool\\Bundle\\StorageUtilsBundle/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Akeneo\\Component\\Analytics/Akeneo\\Tool\\Component\\Analytics/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Akeneo\\Component\\Buffer/Akeneo\\Tool\\Component\\Buffer/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Akeneo\\Component\\Console/Akeneo\\Tool\\Component\\Console/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Akeneo\\Component\\Localization/Akeneo\\Tool\\Component\\Localization/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Akeneo\\Component\\Versioning/Akeneo\\Tool\\Component\\Versioning/g'
```

2. Reactivate your custom code

You are now ready to re enable your custom bundles in the `AppKernel.php` file.

3. Restart the queue consumer

Now you are ready to restart the queue consumer daemon.

If you use `supervisor`, then restart your daemon as following:

    ```bash
    supervisorctl status
    # the command returns the following daemons
    # pim-queue-daemon:pim-queue-daemon_00 STOPPED    Jan 24 11:41 AM

    supervisorctl start pim-queue-daemon:pim-queue-daemon_00

    supervisorctl status
    # pim-queue-daemon:pim-queue-daemon_00 RUNNING    pid 3500, uptime 0:00:04
    # the daemon has been restarted
    ```
