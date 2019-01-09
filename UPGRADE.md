# UPGRADE FROM 2.3 TO 3.0

## Disclaimer

> Please check that you're using Akeneo PIM v2.3.

> We're assuming that you created your project from the standard distribution.

> This documentation helps to migrate projects based on the Enterprise Edition.

> Please perform a backup of your database before proceeding to the migration. You can use tools like [mysqldump](https://dev.mysql.com/doc/refman/5.7/en/mysqldump.html).

> Please perform a backup of your codebase if you don't use a VCS (Version Control System).

## Requirements

Please, see the complete [list of requirements](https://docs.akeneo.com/3.0/install_pim/manual/system_requirements/system_requirements.html) for PIM v3.0.

### PHP Version

Akeneo PIM v3.0 now expects PHP 7.2.

### MySQL version

Akeneo PIM v3.0 now expects MySQL 5.7.22.

## Database charset migration

MySQL charset for Akeneo is now utf8mb4, instead of the flawed utf8. If you have custom table, you can convert them with `ALTER TABLE my_custom_table CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci`. For Akeneo native tables, the migration scripts apply the conversion.

## The main changes of the 3.0 version

Main changes of the 3.0 are related to the code organization. In order to help the product team grow and to deliver more features, we had to reorganize the code structure. Now it is split by functional domain instead of being grouped by technical concerns. 

In a nutshell, we went from

```bash
$ tree pim-community-dev/src/ -d -L 3

src/
├── Akeneo
│   ├── Bundle
│   │   └── ...
│   ├── Component
│   │   └── ...
└── Pim
    ├── Bundle
    │   ├── AnalyticsBundle
    │   ├── ApiBundle
    │   ├── CatalogBundle
    │   ├── CatalogVolumeMonitoringBundle
    │   ├── CommentBundle
    │   ├── ConnectorBundle
    │   ├── DashboardBundle
    │   ├── DataGridBundle
    │   ├── EnrichBundle
    │   ├── FilterBundle
    │   ├── ImportExportBundle
    │   ├── InstallerBundle
    │   ├── LocalizationBundle
    │   ├── NavigationBundle
    │   ├── NotificationBundle
    │   ├── PdfGeneratorBundle
    │   ├── ReferenceDataBundle
    │   ├── UIBundle
    │   ├── UserBundle
    │   └── VersioningBundle
    └── Component
        ├── Api
        ├── Catalog
        ├── CatalogVolumeMonitoring
        ├── Connector
        ├── Enrich
        ├── ReferenceData
        └── User
        
        
$ tree pim-enterprise-dev/src/ -d -L 3        

src/
├── Akeneo
│   ├── Bundle
│   │   ├── FileMetadataBundle
│   │   ├── FileTransformerBundle
│   │   └── RuleEngineBundle
│   └── Component
│       ├── FileMetadata
│       ├── FileTransformer
│       └── RuleEngine
└── PimEnterprise
    ├── Bundle
    │   ├── ApiBundle
    │   ├── CatalogBundle
    │   ├── CatalogRuleBundle
    │   ├── ConnectorBundle
    │   ├── DashboardBundle
    │   ├── DataGridBundle
    │   ├── EnrichBundle
    │   ├── FilterBundle
    │   ├── ImportExportBundle
    │   ├── InstallerBundle
    │   ├── PdfGeneratorBundle
    │   ├── ProductAssetBundle
    │   ├── ReferenceDataBundle
    │   ├── SecurityBundle
    │   ├── TeamworkAssistantBundle
    │   ├── UIBundle
    │   ├── UserBundle
    │   ├── VersioningBundle
    │   └── WorkflowBundle
    └── Component
        ├── Api
        ├── Catalog
        ├── CatalogRule
        ├── Connector
        ├── ProductAsset
        ├── Security
        ├── TeamworkAssistant
        ├── User
        └── Workflow
```

to something like

```bash
$ tree pim-community-dev/src/ -d -L 4

src/
└── Akeneo
   ├── Channel
   │   ├── Bundle
   │   └── Component
   ├── Pim
   │   ├── Enrichment
   │   │   ├── Bundle
   │   │   └── Component
   │   └── Structure
   │       ├── Bundle
   │       └── Component
   ├── Platform
   │   ├── Bundle
   │   │   └── ...
   │   ├── Component
   │   │   └── ...
   │   └── config
   ├── Tool
   │   ├── Bundle
   │   │   └── ...
   │   └── Component
   │       └── ...
   └── UserManagement
        ├── Bundle
        └── Component


$ tree pim-enterprise-dev/src/ -d -L 3

src/
└── Akeneo
    ├── Asset
    │   ├── Bundle
    │   └── Component
    ├── Pim
    │   ├── Enrichment
    │   ├── Automation
    │       ├── RuleEngine
    │       └── FranklinInsights
    │   ├── Permission
    │   └── WorkOrganization
    │       ├── TeamorkAssistant
    │       ├── Workflow
    │       └── ProductRevert
    ├── Platform
    │   └── ...
    ├── ReferenceEntity
    └── Tool
        ├── Bundle
        └── Component

```

This change lead us to move all the classes of the PIM (`sed` commands are provided in the section _Migrate your custom code_ of this upgrade guide). It has also a small impact on the configuration files as described in the section *Migrate your standard project*.

If you want to know more about this topic, you can read the [blog posts](#) we have written. You can also refer to [the definitions of each of those new folders](https://github.com/akeneo/pim-community-dev/blob/master/internal_doc/ARCHITECTURE.md#you-said-bounded-contexts).

TODO: link to blog post

## Migrate your standard project

/!\ Before starting the migration process, we advise you to stop the job queue consumer daemon and start it again only when the migration process is finished.

TODO: add command to stop the daemon...

To give you a quick overview of the changes made to a standard project, you can check on [Github](https://github.com/akeneo/pim-enterprise-standard/compare/2.3...standard-for-3dot0).

TODO: change the link!!

1. Download the latest standard edition from the [Partner Portal](https://partners.akeneo.com/login) and extract it:

    ```bash
    tar -zxf pim-enterprise-standard.tar.gz
    cd pim-enterprise-standard/

    ```

2. Update the configuration files:

    First, we'll consider you have a `$PIM_DIR` variable:

    ```bash
    export PIM_DIR=/path/to/your/current/pim/installation
    ```
    
    Then copy the following files, normally you shouldn't have made a single change to them in your project. If it's the case, don't forget to update them with your changes:

    ```bash
    cp .env.dist $PIM_DIR/
    cp .gitignore $PIM_DIR/
    cp docker-compose.override.yml.dist $PIM_DIR/
    cp docker-compose.yml $PIM_DIR/

    cp app/PimRequirements.php $PIM_DIR/app/
    cp app/config/config_behat.yml $PIM_DIR/app/config/
    cp app/config/config_test.yml $PIM_DIR/app/config/
    cp app/config/pim_parameters.yml $PIM_DIR/app/config/
    cp app/config/security.yml $PIM_DIR/app/config/
    cp app/config/security_test.yml $PIM_DIR/app/config/
    ```
    
    At this step, most of the configuration files have been updated. But we still miss a few that are detailed in the next steps.
    
    In those files, the following changes occurred:
    
    * The user provider `oro_user` has been replaced by `pim_user`.
    * the user provider ID `oro_user.security.provider` has been replaced by `pim_user.provider.user`
    * the route `oro_user_security_check` has been replaced by `pim_user_security_check`
    * the route `oro_user_security_login` has been replaced by `pim_user_security_login`
    * the route `oro_user_security_logout` has been replaced by `pim_user_security_logout`
    * the Elasticsearch configuration files are now: 
    
    ```yaml
    elasticsearch_index_configuration_files:
        - '%pim_ce_dev_src_folder_location%/src/Akeneo/Pim/Enrichment/Bundle/Resources/elasticsearch/index_configuration.yml'
        - '%pim_ee_dev_src_folder_location%/src/Akeneo/Pim/WorkOrganization/Workflow/Bundle/Resources/elasticsearch/index_configuration.yml'
    ```
    

3. Update your **app/config/config.yml**

    An easy way to update it is to copy/paste from the latest standard edition and add your custom changes.

    ```bash
    cp app/config/config.yml $PIM_DIR/app/config
    # then add your own changes
    ```

    Or you can follow the detailed list of changes:
    
        
    * The configuration file `pim.yml` is not located in the *PimEnrichBundle* anymore:

        v2.x
        ```
        imports:
            - { resource: '@PimEnterpriseEnrichBundle/Resources/config/pimee.yml' }
        ```

        v3.0
        ```
        imports:
            - { resource: '../../vendor/akeneo/pim-enterprise-dev/Akeneo/Platform/config/pimee.yml' }
        ```    
    
    * The translator now expects the language `en_US`:

        v2.x
        ```
        framework:
            translator:      { fallback: en }
        ```

        v3.0
        ```
        framework:
            translator:      { fallback: en_US }
        ```
             
    * The reference data configuration has been moved in the Pim Structure. Therefore, you must update your reference data configuration. 
    The key `pim_reference_data` is replaced by `akeneo_pim_structure.reference_data`:

        v2.x
        ```
        pim_reference_data:
            fabrics:
                class: Acme\Bundle\AppBundle\Entity\Fabric
                type: multi
            color:
                class: Acme\Bundle\AppBundle\Entity\Color
                type: simple

        ```

        v3.0
        ```
        akeneo_pim_structure:
            reference_data:
                fabrics:
                    class: Acme\Bundle\AppBundle\Entity\Fabric
                    type: multi
                color:
                    class: Acme\Bundle\AppBundle\Entity\Color
                    type: simple
        ```

    * The key `pim_enrich.max_products_category_removal` has been removed. Please use the container parameter `max_products_category_removal` instead if needed.

4. Update your **app/config/routing.yml**

    An easy way to update it is to copy/paste from the latest standard edition and add your custom changes.

    ```bash
    cp app/config/routing.yml $PIM_DIR/app/config
    # then add your own changes
    ```

    Or you can follow the detailed list of changes:

    * The following route configurations have been removed:
        - `pim_enrich`
        - `pim_comment`
        - `pim_pdf_generator`
        - `pim_localization`
        - `pim_reference_data`
        - `oro_user`
        - `pimee_ui`
        - `pimee_datagrid`
        - `pimee_api`
        
    * The following route configurations have been added:
        
        ```yaml
        akeneo_channel:
            resource: "@AkeneoChannelBundle/Resources/config/routing.yml"
            prefix:   /
         
        akeneo_pim_structure:
            resource: "@AkeneoPimStructureBundle/Resources/config/routing.yml"
         
        akeneo_pim_enrichment:
            resource: "@AkeneoPimEnrichmentBundle/Resources/config/routing.yml"
      
        pim_franklin_insights:
            resource: "@AkeneoFranklinInsightsBundle/Resources/config/routing.yml"
        
        pimee_reference_entity:
            resource: "@AkeneoReferenceEntityBundle/Resources/config/routing.yml"
        ```
        
    * The following route configurations have been updated:
        
        - oro_default
        
        ```yaml
        oro_default:
            path:  /
            defaults:
                template:    PimEnrichBundle::index.html.twig
                _controller: FrameworkBundle:Template:template
        ```
        
        to
        
        ```yaml
        oro_default:
            path:  /
            defaults:
                template:    PimUIBundle::index.html.twig
                _controller: FrameworkBundle:Template:template
        ```

        - pimee_teamwork_assistant
        
        ```yaml
        pimee_teamwork_assistant:
            resource: "@PimEnterpriseTeamworkAssistantBundle/Resources/config/routing/routing.yml"

        ```
        
        to
        
        ```yaml
        pimee_teamwork_assistant:
            resource: "@AkeneoPimTeamworkAssistantBundle/Resources/config/routing/routing.yml"

        ```

        - pimee_workflow
        
        ```yaml
        pimee_workflow:
            resource: "@PimEnterpriseWorkflowBundle/Resources/config/routing.yml"

        ```
        
        to
        
        ```yaml
        pimee_workflow:
            resource: "@AkeneoPimWorkflowBundle/Resources/config/routing.yml"

        ```
        
        - pim_versioning
        pim_versioning:
            resource: "@PimEnterpriseVersioningBundle/Resources/config/routing.yml"
        
        ```yaml

        ```
        
        to
        
        ```yaml
        pim_versioning:
            resource: "@AkeneoPimProductRevertBundle/Resources/config/routing.yml"

        ```

        - pim_catalog_rule
        
        ```yaml
        pim_catalog_rule:
            resource: "@PimEnterpriseCatalogRuleBundle/Resources/config/routing.yml"

        ```
        
        to
        
        ```yaml
        pim_catalog_rule:
            resource: "@AkeneoPimRuleEngineBundle/Resources/config/routing.yml"

        ```

        - pimee_product_asset
        
        ```yaml
        pimee_product_asset:
            resource: "@PimEnterpriseProductAssetBundle/Resources/config/routing.yml"

        ```
        
        to
        
        ```yaml
        pimee_product_asset:
            resource: "@AkeneoAssetBundle/Resources/config/routing.yml"

        ```

        - pim_security
        
        ```yaml
        pim_security:
            resource: "@PimEnterpriseSecurityBundle/Resources/config/routing.yml"

        ```
        
        to
        
        ```yaml
        pim_security:
            resource: "@AkeneoPimPermissionBundle/Resources/config/routing.yml"

        ```

5. Update your **app/config/security.yml**:

    An easy way to update it is to copy/paste from the latest standard edition and add your own custom security configuration.

    * The following have been updated:
    
    The parameter `security.encoders` has moved from
     
    ```yaml
    encoders:
        Pim\Bundle\UserBundle\Entity\User: sha512
    ```
    
    to
    
    ```yaml
    encoders:
        Akeneo\UserManagement\Component\Model\User: sha512
    ```
        
5. Update your **app/AppKernel.php**:

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
        - `Akeneo\Bundle\RuleEngineBundle\AkeneoRuleEngineBundle` now is `Akeneo\Tool\Bundle\RuleEngineBundle\AkeneoRuleEngineBundle`
        - `PimEnterprise\Bundle\CatalogRuleBundle\PimEnterpriseCatalogRuleBundle` now is `Akeneo\Pim\Automation\RuleEngine\Bundle\AkeneoPimRuleEngineBundle`
        - `Akeneo\Bundle\FileMetadataBundle\AkeneoFileMetadataBundle` now is `Akeneo\Tool\Bundle\FileMetadataBundle\AkeneoFileMetadataBundle`
        - `Akeneo\Bundle\FileTransformerBundle\AkeneoFileTransformerBundle` now is `Akeneo\Tool\Bundle\FileTransformerBundle\AkeneoFileTransformerBundle`
        - `PimEnterprise\Bundle\TeamworkAssistantBundle\PimEnterpriseTeamworkAssistantBundle` now is ``
        - `PimEnterprise\Bundle\WorkflowBundle\PimEnterpriseWorkflowBundle` now is `Akeneo\Pim\WorkOrganization\Workflow\Bundle\AkeneoPimWorkflowBundle`
        - `PimEnterprise\Bundle\UIBundle\PimEnterpriseUIBundle` now is `Akeneo\Platform\Bundle\UIBundle\PimEnterpriseUIBundle`
        - `PimEnterprise\Bundle\ProductAssetBundle\PimEnterpriseProductAssetBundle` now is `Akeneo\Asset\Bundle\AkeneoAssetBundle`
        - `PimEnterprise\Bundle\InstallerBundle\PimEnterpriseInstallerBundle` now is `Akeneo\Platform\Bundle\InstallerBundle\PimEnterpriseInstallerBundle`

    * The following bundles have been removed:
        - `Pim\Bundle\NavigationBundle\PimNavigationBundle`
        - `Pim\Bundle\CatalogBundle\PimCatalogBundle`
        - `Pim\Bundle\CommentBundle\PimCommentBundle`
        - `Pim\Bundle\EnrichBundle\PimEnrichBundle`
        - `Pim\Bundle\LocalizationBundle\PimLocalizationBundle`
        - `Pim\Bundle\PdfGeneratorBundle\PimPdfGeneratorBundle`
        - `Pim\Bundle\ReferenceDataBundle\PimReferenceDataBundle`
        - `Oro\Bundle\UserBundle\OroUserBundle`
        - `PimEnterprise\Bundle\CatalogBundle\PimEnterpriseCatalogBundle`
        - `PimEnterprise\Bundle\UserBundle\PimEnterpriseUserBundle`
        - `PimEnterprise\Bundle\ApiBundle\PimEnterpriseApiBundle`
        - `PimEnterprise\Bundle\VersioningBundle\PimEnterpriseVersioningBundle`
        - `PimEnterprise\Bundle\SecurityBundle\PimEnterpriseSecurityBundle`
        - `PimEnterprise\Bundle\PdfGeneratorBundle\PimEnterprisePdfGeneratorBundle`
        - `PimEnterprise\Bundle\ImportExportBundle\PimEnterpriseImportExportBundle`
        - `PimEnterprise\Bundle\FilterBundle\PimEnterpriseFilterBundle`
        - `PimEnterprise\Bundle\EnrichBundle\PimEnterpriseEnrichBundle`
        - `PimEnterprise\Bundle\DataGridBundle\PimEnterpriseDataGridBundle`
        - `PimEnterprise\Bundle\DashboardBundle\PimEnterpriseDashboardBundle`
        - `PimEnterprise\Bundle\ConnectorBundle\PimEnterpriseConnectorBundle`
        - `PimEnterprise\Bundle\ReferenceDataBundle\PimEnterpriseReferenceDataBundle`

    * The following bundles have been added:
        - `Akeneo\Channel\Bundle\AkeneoChannelBundle`
        - `Akeneo\Pim\Enrichment\Bundle\AkeneoPimEnrichmentBundle`
        - `Akeneo\Pim\Structure\Bundle\AkeneoPimStructureBundle`
        - `Akeneo\Pim\Enrichment\Asset\Bundle\AkeneoPimEnrichmentAssetBundle`
        - `Akeneo\Pim\Permission\Bundle\AkeneoPimPermissionBundle`
        - `Akeneo\ReferenceEntity\Infrastructure\Symfony\AkeneoReferenceEntityBundle`
        - `Akeneo\Pim\WorkOrganization\ProductRevert\AkeneoPimProductRevertBundle`
        - `Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Symfony\AkeneoFranklinInsightsBundle`
        - `Akeneo\Tool\Bundle\RuleEngineBundle\AkeneoRuleEngineBundle`
    
6. Update your dependencies:

    The easiest way to update your `composer.json` is to copy/paste from the latest standard edition and add your custom dependencies.
    
    ```bash
    cp composer.json $PIM_DIR/
    # then add your own dependencies
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
    
     **This step will copy the upgrades folder from `pim-enterprise-dev/` to your Pim project root in order to migrate.**
    If you have custom code in your project, this step may raise errors in the "post-script" command.
    In this case, go to the chapter "Migrate your custom code" before running the database migration.

    And we also have to update the frontend dependencies:
    
    ```bash
    yarn install
    ```

7. Migrate your database:

    ```bash
    rm -rf var/cache
    bin/console doctrine:migration:migrate --env=prod
    ```

8. Then re-generate the PIM assets:

    ```bash
    bin/console pim:installer:assets --clean --env=prod
    yarn run webpack
    ```

## Migrate your custom code

Several classes and services have been moved or renamed. The following commands help to migrate references to them:

TODO: copy CE changes!

```bash
find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\DataGridBundle\\Extension\\Filter\\FilterExtension/Akeneo\\Pim\\Permission\\Bundle\\Datagrid\\Extension\\Filter\\FilterExtension/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\DataGridBundle\\Extension\\MassAction\\Event\\MassActionEvents/Akeneo\\Pim\\WorkOrganization\\Workflow\\Bundle\\Datagrid\\MassActionEvents/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\DataGridBundle\\Adapter\\OroToPimGridFilterAdapter/Akeneo\\Pim\\WorkOrganization\\Workflow\\Bundle\\Datagrid\\OroToPimGridFilterAdapter/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\DataGridBundle\\Extension\\MassAction\\Handler\\RuleImpactedProductCountActionHandler/Akeneo\\Pim\\Automation\\RuleEngine\\Bundle\\Datagrid\\Extension\\MassAction\\RuleImpactedProductCountActionHandler/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\DataGridBundle\\Datagrid\\Configuration\\ProductHistory\\GridHelper/Akeneo\\Pim\\WorkOrganization\\ProductRevert\\Datagrid\\Configuration\\ProductHistory\\GridHelper/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\DataGridBundle\\Datagrid\\Configuration\\Product\\FiltersConfigurator/Akeneo\\Pim\\WorkOrganization\\TeamworkAssistant\\Bundle\\Datagrid\\Configuration\\Product\\FiltersConfigurator/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\ProductAssetBundle\\Doctrine\\ORM\\CompletenessRemover/Akeneo\\Pim\\Enrichment\\Asset\\Bundle\\Doctrine\\ORM\\CompletenessRemover/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Component\\ProductAsset\\Completeness\\CompletenessRemoverInterface/Akeneo\\Pim\\Enrichment\\Asset\\Component\\Completeness\\CompletenessRemoverInterface/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Component\\ProductAsset\\Completeness\\Checker\\AssetCollectionCompleteChecker/Akeneo\\Pim\\Enrichment\\Asset\\Component\\Completeness\\Checker\\AssetCollectionCompleteChecker/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Component\\ProductAsset\\Comparator\\Attribute\\AssetCollectionComparator/Akeneo\\Pim\\Enrichment\\Asset\\Component\\Comparator\\Attribute\\AssetCollectionComparator/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\ApiBundle\\Security\\AccessDeniedHandler/Akeneo\\Pim\\Permission\\Bundle\\Api\\AccessDeniedHandler/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\ApiBundle\\Checker\\QueryParametersChecker/Akeneo\\Pim\\Permission\\Bundle\\Api\\QueryParametersChecker/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\CatalogBundle\\Filter\\AbstractAuthorizationFilter\\DatagridViewFilter/Akeneo\\Pim\\Permission\\Bundle\\Filter\\AbstractAuthorizationFilter\\DatagridViewFilter/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\CatalogBundle\\Manager\\CategoryManager\\ProductController/Akeneo\\Pim\\Permission\\Bundle\\Persistence\\ORM\\Category\\CategoryManager\\ProductController/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\CatalogBundle\\Manager\\CategoryManager\\ProductModelController/Akeneo\\Pim\\Permission\\Bundle\\Persistence\\ORM\\Category\\CategoryManager\\ProductModelController/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\CatalogBundle\\Filter\\AttributeViewRightFilter\\AttributeRepository/Akeneo\\Pim\\Permission\\Bundle\\Filter\\AttributeViewRightFilter\\AttributeRepository/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\CatalogBundle\\Filter\\JobInstanceEditRightFilter/Akeneo\\Pim\\Permission\\Bundle\\Filter\\JobInstanceEditRightFilter/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\CatalogBundle\\Filter\\AbstractAuthorizationFilter/Akeneo\\Pim\\Permission\\Bundle\\Filter\\AbstractAuthorizationFilter/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\CatalogBundle\\Filter\\AttributeEditRightFilter/Akeneo\\Pim\\Permission\\Bundle\\Filter\\AbstractAuthorizationFilter/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\CatalogBundle\\Filter\\AttributeGroupViewRightFilter/Akeneo\\Pim\\Permission\\Bundle\\Filter\\AttributeGroupViewRightFilter/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\CatalogBundle\\Filter\\AttributeViewRightFilter/Akeneo\\Pim\\Permission\\Bundle\\Filter\\AttributeViewRightFilter/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\CatalogBundle\\Filter\\LocaleEditRightFilter/Akeneo\\Pim\\Permission\\Bundle\\Filter\\LocaleEditRightFilter/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\CatalogBundle\\Filter\\LocaleViewRightFilter/Akeneo\\Pim\\Permission\\Bundle\\Filter\\LocaleViewRightFilter/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\CatalogBundle\\Filter\\ProductAndProductModelDeleteRightFilter/Akeneo\\Pim\\Permission\\Bundle\\Filter\\ProductAndProductModelDeleteRightFilter/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\CatalogBundle\\Filter\\ProductRightEditFilter/Akeneo\\Pim\\Permission\\Bundle\\Filter\\ProductRightEditFilter/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\CatalogBundle\\Filter\\ProductRightViewFilter/Akeneo\\Pim\\Permission\\Bundle\\Filter\\ProductRightViewFilter/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\CatalogBundle\\Filter\\ProductValueAttributeGroupRightFilter/Akeneo\\Pim\\Permission\\Bundle\\Filter\\ProductValueAttributeGroupRightFilter/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\CatalogBundle\\Filter\\ProductValueLocaleRightFilter/Akeneo\\Pim\\Permission\\Bundle\\Filter\\ProductValueLocaleRightFilter/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\CatalogBundle\\Doctrine\\ORM\\Repository\\AttributeRepository/Akeneo\\Pim\\Permission\\Bundle\\Persistence\\ORM\\Attribute\\AttributeRepository/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\CatalogBundle\\Manager\\CategoryManager/Akeneo\\Pim\\Permission\\Bundle\\Persistence\\ORM\\Category\\CategoryManager/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\CatalogBundle\\Doctrine\\ORM\\Repository\\ProductMassActionRepository/Akeneo\\Pim\\Permission\\Bundle\\Persistence\\ORM\\EntityWithValue\\ProductMassActionRepository/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\CatalogBundle\\Doctrine\\ORM\\Repository\\ProductModelRepository/Akeneo\\Pim\\Permission\\Bundle\\Persistence\\ORM\\EntityWithValue\\ProductModelRepository/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\CatalogBundle\\Security\\Elasticsearch\\ProductQueryBuilderFactory/Akeneo\\Pim\\Permission\\Bundle\\Persistence\\ORM\\EntityWithValue\\ProductQueryBuilderFactory/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\CatalogBundle\\Doctrine\\ORM\\Repository\\ProductRepository/Akeneo\\Pim\\Permission\\Bundle\\Persistence\\ORM\\EntityWithValue\\ProductRepository/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\CatalogBundle\\Security\\Doctrine\\Common\\Saver\\FilteredEntitySaver/Akeneo\\Pim\\Permission\\Bundle\\Persistence\\ORM\\FilteredEntitySaver/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\DataGridBundle\\Filter\\DatagridViewFilter/Akeneo\\Pim\\Permission\\Bundle\\Datagrid\\DatagridViewFilter/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\DataGridBundle\\Manager\\DatagridViewManager/Akeneo\\Pim\\Permission\\Bundle\\Datagrid\\DatagridViewManager/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\DataGridBundle\\EventListener\\AddPermissionsToGridListener/Akeneo\\Pim\\Permission\\Bundle\\Datagrid\\EventListener\\AddPermissionsToGridListener/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\DataGridBundle\\EventListener\\ConfigureProductGridListener/Akeneo\\Pim\\Permission\\Bundle\\Datagrid\\EventListener\\ConfigureProductGridListener/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\DataGridBundle\\EventListener\\ConfigureProductGridListener/Akeneo\\Pim\\Permission\\Bundle\\Datagrid\\EventListener\\ConfigureProductGridListener/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\DataGridBundle\\Extension\\MassAction\\Util\\ProductFieldsBuilder/Akeneo\\Pim\\Permission\\Bundle\\Datagrid\\MassAction\\ProductFieldsBuilder/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\DataGridBundle\\Extension\\MassAction\\Util\\ProductFieldsBuilder/Akeneo\\Pim\\Permission\\Bundle\\Datagrid\\MassAction\\ProductFieldsBuilder/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\DataGridBundle\\Datagrid\\Configuration\\Product\\RowActionsConfigurator/Akeneo\\Pim\\Permission\\Bundle\\Datagrid\\Product\\RowActionsConfigurator/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\EnrichBundle\\Doctrine\\Counter\\GrantedCategoryItemsCounter/Akeneo\\Asset\\Bundle\\Doctrine\\ORM\\Query\\GrantedCategoryItemsCounter/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\SecurityBundle\\EventSubscriber\\Datagrid\\ProductCategoryAccessSubscriber/Akeneo\\Pim\\Permission\\Bundle\\Datagrid\\EventListener\\ProductCategoryAccessSubscriber/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\EnrichBundle\\EventSubscriber\\SavePermissionsSubscriber/Akeneo\\Pim\\Permission\\Bundle\\EventSubscriber\\SavePermissionsSubscriber/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\EnrichBundle\\Connector\\Writer\\MassEdit\\ProductAndProductModelWriter/Akeneo\\Pim\\Permission\\Bundle\\MassEdit\\Writer\\ProductAndProductModelWriter/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\EnrichBundle\\Normalizer\\IncompleteValuesNormalizer/Akeneo\\Pim\\Permission\\Bundle\\Normalizer\\InternalApi\\IncompleteValuesNormalizer/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\FilterBundle\\Filter\\Product\\PermissionFilter/Akeneo\\Pim\\Permission\\Bundle\\Datagrid\\Filter\\PermissionFilter/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\ImportExportBundle\\Form\\Subscriber\\JobProfilePermissionsSubscriber/Akeneo\\Pim\\Permission\\Bundle\\Form\\EventListener\\JobProfilePermissionsSubscriber/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\ImportExportBundle\\Form\\Type\\JobProfilePermissionsType/Akeneo\\Pim\\Permission\\Bundle\\Form\\Type\\JobProfilePermissionsType/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\ImportExportBundle\\Manager\\JobExecutionManager/Akeneo\\Pim\\Permission\\Bundle\\Manager\\JobExecutionManager/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\FilterBundle\\Filter\\Product\\ProjectCompletenessFilter/PimEnterprise\\Bundle\\TeamworkAssistantBundle\\Datagrid\\Filter\\ProjectCompletenessFilter/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\PdfGeneratorBundle\\Twig\\ImageExtension/Akeneo\\Asset\\Bundle\\TwigExtension\\ImageExtension/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\SecurityBundle\\Controller\\PermissionRestController/Akeneo\\Pim\\Permission\\Bundle\\Controller\\InternalApi\\PermissionRestController/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\PdfGeneratorBundle\\Controller\\ProductController/Akeneo\\Pim\\Permission\\Bundle\\Controller\\ProductController/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\PdfGeneratorBundle\\Renderer\\ProductPdfRenderer/Akeneo\\Pim\\Permission\\Bundle\\Pdf\\ProductPdfRenderer/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\EnrichBundle\\Filter/Akeneo\\Pim\\Permission\\Bundle\\Filter/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\EnrichBundle\\Form\\Subscriber/Akeneo\\Pim\\Permission\\Bundle\\Form\\EventListener/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\EnrichBundle\\Provider\\Form/Akeneo\\Pim\\Permission\\Bundle\\Form\\Provider/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\EnrichBundle\\Form\\Type/Akeneo\\Pim\\Permission\\Bundle\\Form\\Type/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\EnrichBundle\\Connector\\Processor\\MassEdit\\Product/Akeneo\\Pim\\Permission\\Bundle\\MassEdit\\Processor/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\EnrichBundle\\NormalizerProduct\\PublishedProductNormalizer/PimEnterprise\\Component\\Workflow\\Normalizer\\InternalApi\\Processor\\PublishedProductNormalizer/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Akeneo\\Bundle\\FileMetadataBundle/Akeneo\\Tool\\Bundle\\FileMetadataBundle/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Akeneo\\Bundle\\FileTransformerBundle/Akeneo\\Tool\\Bundle\\FileTransformerBundle/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Akeneo\\Bundle\\RuleEngineBundle/Akeneo\\Bundle\\Tool\\RuleEngineBundle/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Akeneo\\Component\\FileMetadata/Akeneo\\Tool\\Component\\FileMetadata/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Akeneo\\Component\\FileTransformer/Akeneo\\Tool\\Component\\FileTransformer/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Akeneo\\Component\\RuleEngine/Akeneo\\Tool\\Component\\RuleEngine/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\ProductAssetBundle/Akeneo\\Asset\\Bundle/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Component\\ProductAsset\\Remover\\CategoryAssetRemover/Akeneo\\Asset\\Bundle\\Doctrine\\ORM\\Remover\\CategoryAssetRemover/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Component\\ProductAsset\\Factory\\NotificationFactory/Akeneo\\Asset\\Bundle\\Notification\\NotificationFactory/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\ProductAssetBundle\\EventSubscriber\\ORM\\AssetEventSubscriber/PimEnterprise\\Bundle\\WorkflowBundle\\EventSubscriber\\Asset\\AssetEventSubscriber/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\ProductAssetBundle\\Workflow\\Presenter\\AssetsCollectionPresenter/PimEnterprise\\Bundle\\WorkflowBundle\\Presenter\\AssetsCollectionPresenter/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\ProductAssetBundle\\Workflow\\Presenter\\AssetsCollectionPresenter/PimEnterprise\\Bundle\\WorkflowBundle\\Presenter\\AssetsCollectionPresenter/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Component\\TeamworkAssistant/Akeneo\\Pim\\WorkOrganization\\TeamWorkAssistant\\Component/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\TeamworkAssistantBundle/Akeneo\\Pim\\WorkOrganization\\TeamworkAssistant\\Bundle/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\VersioningBundle/Akeneo\\Pim\\WorkOrganization\\ProductRevert/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Component\\Workflow/Akeneo\\Pim\\WorkOrganization\\Workflow\\Component/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\WorkflowBundle/Akeneo\\Pim\\WorkOrganization\\Workflow\\Bundle/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Component\\CatalogRule/Akeneo\\Pim\\Automation\\RuleEngine\\Component/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\CatalogRuleBundle/Akeneo\\Pim\\Automation\\RuleEngine\\Bundle/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\SecurityBundle/Akeneo\\Pim\\Permission\\Bundle/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Component\\Security/Akeneo\\Pim\\Automation\\RuleEngine\\Component/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\ApiBundle\\Controller\\ProductModelDraftController/Akeneo\\Pim\\WorkOrganization\\Workflow\\Bundle\\Controller\\ExternalApi\\ProductModelDraftController/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\ApiBundle\\Controller\\ProductModelProposalController/Akeneo\\Pim\\WorkOrganization\\Workflow\\Bundle\\Controller\\ExternalApi\\ProductModelProposalController/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\ReferenceDataBundle\\Workflow\\Presenter\\AbstractReferenceDataPresenter/Akeneo\\Pim\\WorkOrganization\\Workflow\\Bundle\\Presenter\\ReferenceData\\AbstractReferenceDataPresenter/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\ReferenceDataBundle\\Workflow\\Presenter\\ReferenceDataCollectionPresenter/Akeneo\\Pim\\WorkOrganization\\Workflow\\Bundle\\Presenter\\ReferenceData\\ReferenceDataCollectionPresenter/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\ReferenceDataBundle\\Workflow\\Presenter\\ReferenceDataPresenter/Akeneo\\Pim\\WorkOrganization\\Workflow\\Bundle\\Presenter\\ReferenceData\\ReferenceDataPresenter/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Component\\Api\\Normalizer\\ProductModelNormalizer/Akeneo\\Pim\\WorkOrganization\\Workflow\\Component\\Normalizer\\ExternalApi\\ProductModelNormalizer/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\ReferenceDataBundle\\Publisher\\ReferenceDataPublisher/Akeneo\\Pim\\WorkOrganization\\Workflow\\Component\\Publisher\\ReferenceDataPublisher/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\ReferenceDataBundle\\Publisher\\ReferenceDataPublisher/Akeneo\\Pim\\WorkOrganization\\Workflow\\Component\\Publisher\\ReferenceDataPublisher/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Akeneo\Pim\WorkOrganization\TeamworkAssistant\Bundle\PimEnterpriseTeamworkAssistantBundle/Akeneo\Pim\WorkOrganization\TeamworkAssistant\Bundle\AkeneoPimTeamworkAssistantBundle/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Akeneo\Pim\WorkOrganization\ProductRevert\PimEnterpriseRevertBundle/Akeneo\Pim\WorkOrganization\ProductRevert\AkeneoPimProductRevertBundle/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Akeneo\Pim\WorkOrganization\Workflow\Bundle\PimEnterpriseWorkflowBundle/Akeneo\Pim\WorkOrganization\Workflow\Bundle\AkeneoPimWorkflowBundle/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Akeneo\Pim\Automation\RuleEngine\Bundle\PimEnterpriseCatalogRuleBundle/Akeneo\Pim\Automation\RuleEngine\Bundle\AkeneoPimRuleEngineBundle/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Akeneo\Asset\Bundle\PimEnterpriseProductAssetBundle/Akeneo\Asset\Bundle\AkeneoAssetBundle/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Component\\ProductAsset/Akeneo\\Asset\\Component/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\EnrichBundle\\Twig\\AttributeExtension/Akeneo\\Pim\\WorkOrganization\\Workflow\\Bundle\\Twig\\AttributeExtension/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\EnrichBundle\\Normalizer\\ProductModelNormalizer/Akeneo\\Pim\\WorkOrganization\\Workflow\\Bundle\\Normalizer\\ProductModelNormalizer/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Akeneo\\Asset\\Bundle\\Doctrine\\ORM\\Query\\GrantedCategoryItemsCounter/Akeneo\\Pim\\Permission\\Bundle\\Persistence\\ORM\\Category\\Query\\GrantedCategoryItemsCounter/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\EnrichBundle\\Form\\Type\\LocaleType/Akeneo\\Pim\\Permission\\Bundle\\Form\\Type\\LocaleType/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\EnrichBundle\\Doctrine\\ORM\\Repository\\JobExecutionRepository/Akeneo\\Pim\\Permission\\Bundle\\Entity\\Repository\\JobExecutionRepository/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\EnrichBundle\\Doctrine\\ORM\\Repository\\JobInstanceRepository/Akeneo\\Pim\\Permission\\Bundle\\Entity\\Repository\\JobInstanceRepository/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\EnrichBundle\\Repository\\AttributeRepositoryInterface/Akeneo\\Pim\\Permission\\Bundle\\Entity\\Repository\\AttributeRepositoryInterface/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\EnrichBundle\\Repository\\AttributeSearchableRepository/Akeneo\\Pim\\Permission\\Bundle\\Entity\\Repository\\AttributeSearchableRepository/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\EnrichBundle\\Doctrine\\ORM\\Repository\\AttributeRepository/Akeneo\\Pim\\Permission\\Bundle\\Entity\\Repository\\AttributeRepository/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\EnrichBundle\\Controller\\ProductController/Akeneo\\Pim\\Permission\\Bundle\\Controller\\Ui\\ProductController/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\EnrichBundle\\Controller\\ProductModelController/Akeneo\\Pim\\Permission\\Bundle\\Controller\\Ui\\ProductModelController/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\EnrichBundle\\Controller\\LocaleController/Akeneo\\Pim\\Permission\\Bundle\\Controller\\Ui\\LocaleController/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\EnrichBundle\\Controller\\CategoryTreeController/Akeneo\\Pim\\Permission\\Bundle\\Controller\\Ui\\CategoryTreeController/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\EnrichBundle\\Normalizer\\RuleRelationNormalizer/Akeneo\\Pim\\Automation\\RuleEngine\\Bundle\\Normalizer\\RuleRelationNormalizer/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\EnrichBundle\\Controller\\RuleRelationController/Akeneo\\Pim\\Automation\\RuleEngine\\Bundle\\Controller\\InternalApi\\RuleRelationController/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\EnrichBundle\\Normalizer\\ImageNormalizer/Akeneo\\Asset\\Component\\Normalizer\\InternalApi\\ImageNormalizer/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Component\\Catalog\\Manager\\AttributeValuesResolver/Akeneo\\Pim\\Permission\\Component\\Manager\\AttributeValuesResolver/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Component\\Catalog\\ProductModel\\Filter\\GrantedProductAttributeFilter/Akeneo\\Pim\\Permission\\Component\\Filter\\GrantedProductAttributeFilter/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Component\\Catalog\\Updater\\Adder\\AssetCollectionAdder/Akeneo\\Pim\\Enrichment\\Asset\\Component\\Updater\\Adder\\AssetCollectionAdder/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Component\\Catalog\\\\AssetCollectionValueFactory/Akeneo\\Pim\\Enrichment\\Asset\\Component\\AssetCollectionValueFactory/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Component\\Catalog\\Security/PimEnterprise\\Component\\Security/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\VersioningBundle/PimEnterprise\\Bundle\\RevertBundle/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\VersioningBundle\\UpdateGuesser/Akeneo\\Pim\\Permission\\Bundle\\UpdateGuesser/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\VersioningBundle\\EventSubscriber\\AddVersionSubscriber/PimEnterprise\\Bundle\\WorkflowBundle\\EventSubscriber\\PublishedProduct\\SkipVersionSubscriber/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\VersioningBundle\\Purger/PimEnterprise\\Bundle\\WorkflowBundle\\Purger/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\ApiBundle\\Normalizer/Akeneo\\Asset\\Component\\Normalizer\\ExternalApi/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\EnrichBundle\\Controller\\Rest/Akeneo\\Asset\\Bundle\\Controller\\Rest/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\FilterBundle\\Filter\\Tag/Akeneo\\Asset\\Bundle\\Datagrid\\Filter/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\EnrichBundle\\Controller\\Rest\\ChannelController/Akeneo\\Asset\\Bundle\\Controller\\Rest\\AssetTransformationController/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\EnrichBundle\\Normalizer\\AssetNormalizer/Akeneo\\Asset\\Component\\Normalizer\\InternalApi\\AssetNormalizer/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\SecurityBundle\\EventSubscriber\\Datagrid\\AssetCategoryAccessSubscriber/Akeneo\\Asset\\Bundle\\Security\\AssetCategoryAccessSubscriber/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\SecurityBundle\\Normalizer\\Flat\\AssetCategoryNormalizer/Akeneo\\Asset\\Component\\Normalizer\\Flat\\AssetCategoryNormalizer/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\ApiBundle\\Controller\\ProductDraftController/PimEnterprise\\Bundle\\WorkflowBundle\\Controller\\Api\\ProductDraftController/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\ApiBundle\\Controller\\ProductProposalController/PimEnterprise\\Bundle\\WorkflowBundle\\Controller\\Api\\ProductProposalController/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\ApiBundle\\Router\\ProxyProductRouter/PimEnterprise\\Bundle\\WorkflowBundle\\Router\\ProxyProductRouter/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Component\\Api\\Normalizer\\ProductNormalizer/PimEnterprise\\Component\\Workflow\\Normalizer\\ExternalApi\\ProductNormalizer/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\ApiBundle\\Doctrine\\ORM\\Repository\\AssetRepository/Akeneo\\Asset\\Bundle\\Doctrine\\ORM\\Repository\\ExternalApi\\AssetRepository/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\ApiBundle\\Controller\\AssetCategoryController/Akeneo\\Asset\\Bundle\\Controller\\ExternalApi\\AssetCategoryController/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\ApiBundle\\Controller\\AssetController/Akeneo\\Asset\\Bundle\\Controller\\ExternalApi\\AssetController/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\ApiBundle\\Controller\\AssetReferenceController/Akeneo\\Asset\\Bundle\\Controller\\ExternalApi\\AssetReferenceController/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\ApiBundle\\Controller\\AssetTagController/Akeneo\\Asset\\Bundle\\Controller\\ExternalApi\\AssetTagController/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\ApiBundle\\Controller\\AssetVariationController/Akeneo\\Asset\\Bundle\\Controller\\ExternalApi\\AssetVariationController/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\ApiBundle\\Normalizer\\AssetReferenceNormalizer/Akeneo\\Asset\\Component\\Normalizer\\ExternalApi\\AssetReferenceNormalizer/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\ApiBundle\\Normalizer\\AssetVariationNormalizer/Akeneo\\Asset\\Component\\Normalizer\\ExternalApi\\AssetVariationNormalizer/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\DataGridBundle\\Datagrid\\Configuration\\ProductDraft\\GridHelper/PimEnterprise\\Bundle\\WorkflowBundle\\Datagrid\\Configuration\\ProductDraft\\GridHelper/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\DataGridBundle\\Datagrid\\Configuration\\Proposal\\ContextConfigurator/PimEnterprise\\Bundle\\WorkflowBundle\\Datagrid\\Configuration\\Proposal\\ContextConfigurator/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\DataGridBundle\\Datagrid\\Configuration\\Proposal\\GridHelper/PimEnterprise\\Bundle\\WorkflowBundle\\Datagrid\\Configuration\\Proposal\\GridHelper/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\DataGridBundle\\Datasource\\ProductProposalDatasource/PimEnterprise\\Bundle\\WorkflowBundle\\Datagrid\\Datasource\\ProductProposalDatasource/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\DataGridBundle\\Datasource\\ResultRecord\\ORM\\ProductDraftHydrator/PimEnterprise\\Bundle\\WorkflowBundle\\Datagrid\\Datasource\\ResultRecord\\ProductDraftHydrator/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\DataGridBundle\\EventListener\\ConfigureProposalGridListener/PimEnterprise\\Bundle\\WorkflowBundle\\Datagrid\\EventListener\\ConfigureProposalGridListener/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\FilterBundle\\Filter\\ProductDraft\\AttributeChoiceFilter/PimEnterprise\\Bundle\\WorkflowBundle\\Datagrid\\Filter\\AttributeChoiceFilter/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\FilterBundle\\Filter\\ProductDraft\\AuthorFilter/PimEnterprise\\Bundle\\WorkflowBundle\\Datagrid\\Filter\\AuthorFilter/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\FilterBundle\\Filter\\ProductDraft\\ChoiceFilter/PimEnterprise\\Bundle\\WorkflowBundle\\Datagrid\\Filter\\ChoiceFilter/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\FilterBundle\\Filter\\ProductDraftFilterUtility/PimEnterprise\\Bundle\\WorkflowBundle\\Datagrid\\Filter\\ProductDraftFilterUtility/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\DataGridBundle\\Extension\\MassAction\\Handler\\MassApproveActionHandler/PimEnterprise\\Bundle\\WorkflowBundle\\Datagrid\\MassAction\\Handler\\MassApproveActionHandler/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\DataGridBundle\\Extension\\MassAction\\Handler\\MassRefuseActionHandler/PimEnterprise\\Bundle\\WorkflowBundle\\Datagrid\\MassAction\\Handler\\MassRefuseActionHandler/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\SecurityBundle\\Voter\\ProductDraftVoter/PimEnterprise\\Bundle\\WorkflowBundle\\Security\\ProductDraftVoter/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\DashboardBundle\\Widget\\ProposalWidget/PimEnterprise\\Bundle\\WorkflowBundle\\Widget\\ProposalWidget/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\DashboardBundle\\Widget\\ProposalWidget/PimEnterprise\\Bundle\\WorkflowBundle\\Widget\\ProposalWidget/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Component\\Api\\Updater\\AssetUpdater/Akeneo\\Asset\\Component\\Updater\\ExternalApi\\AssetUpdater/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\WorkflowBundle\\Controller\\Api\\ProductDraftController/Akeneo\\Pim\\WorkOrganization\\Workflow\\Bundle\\Controller\\ExternalApi\\ProductDraftController/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\WorkflowBundle\\Controller\\Api\\ProductProposalController/Akeneo\\Pim\\WorkOrganization\\Workflow\\Bundle\\Controller\\ExternalApi\\ProductProposalController/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\ApiBundle\\Controller\\PublishedProductController/PimEnterprise\\Bundle\\WorkflowBundle\\Controller\\ExternalApi\\PublishedProductController/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Component\\Api\\Normalizer\\PublishedProductNormalizer/PimEnterprise\\Component\\Workflow\\Normalizer\\ExternalApi\\PublishedProductNormalizer/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\DataGridBundle\\Datagrid\\Configuration\\PublishedProduct\\GridHelper/PimEnterprise\\Bundle\\WorkflowBundle\\Datagrid\\Configuration\\PublishedProduct\\GridHelper/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\DataGridBundle\\Datasource\\ResultRecord\\ORM\\ProductHistoryHydrator/PimEnterprise\\Bundle\\WorkflowBundle\\Datagrid\\Datasource\\ResultRecord\\PublishedProductHistoryHydrator/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\EnrichBundle\\MassEditAction\\Tasklet\\AbstractProductPublisherTasklet/PimEnterprise\\Bundle\\WorkflowBundle\\MassEditAction\\Tasklet\\AbstractProductPublisherTasklet/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\EnrichBundle\\Connector\\Job\\JobParameters\\ConstraintCollectionProvider\\MassPublish/PimEnterprise\\Bundle\\WorkflowBundle\\MassEditAction\\Tasklet\\JobParameters\\ConstraintCollection/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\EnrichBundle\\Connector\\Job\\JobParameters\\DefaultValuesProvider\\MassPublish/PimEnterprise\\Bundle\\WorkflowBundle\\MassEditAction\\Tasklet\\JobParameters\\DefaultValues/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\EnrichBundle\\MassEditAction\\Tasklet\\PublishProductTasklet/PimEnterprise\\Bundle\\WorkflowBundle\\MassEditAction\\Tasklet\\PublishProductTasklet/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\EnrichBundle\\MassEditAction\\Tasklet\\UnpublishProductTasklet/PimEnterprise\\Bundle\\WorkflowBundle\\MassEditAction\\Tasklet\\UnpublishProductTasklet/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\EnrichBundle\\Normalizer\\ProductNormalizer/PimEnterprise\\Bundle\\WorkflowBundle\\Normalizer\\ProductNormalizer/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\EnrichBundle\\Normalizer\\VersionNormalizer/PimEnterprise\\Bundle\\WorkflowBundle\\Versioning\\VersionNormalizer/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\ApiBundle\\Security\\AccessDeniedHandler/Akeneo\\Pim\\Permission\\Bundle\\Api\\AccessDeniedHandler/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\ApiBundle\\Checker\\QueryParametersChecker/Akeneo\\Pim\\Permission\\Bundle\\Api\\QueryParametersChecker/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\CatalogBundle\\Filter\\AbstractAuthorizationFilter\\DatagridViewFilter/Akeneo\\Pim\\Permission\\Bundle\\Filter\\AbstractAuthorizationFilter\\DatagridViewFilter/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\CatalogBundle\\Manager\\CategoryManager\\ProductController/Akeneo\\Pim\\Permission\\Bundle\\Persistence\\ORM\\Category\\CategoryManager\\ProductController/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\CatalogBundle\\Manager\\CategoryManager\\ProductModelController/Akeneo\\Pim\\Permission\\Bundle\\Persistence\\ORM\\Category\\CategoryManager\\ProductModelController/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\CatalogBundle\\Filter\\AttributeViewRightFilter\\AttributeRepository/Akeneo\\Pim\\Permission\\Bundle\\Filter\\AttributeViewRightFilter\\AttributeRepository/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\CatalogBundle\\Filter\\JobInstanceEditRightFilter/Akeneo\\Pim\\Permission\\Bundle\\Filter\\JobInstanceEditRightFilter/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\CatalogBundle\\Filter\\AbstractAuthorizationFilter/Akeneo\\Pim\\Permission\\Bundle\\Filter\\AbstractAuthorizationFilter/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\CatalogBundle\\Filter\\AttributeEditRightFilter/Akeneo\\Pim\\Permission\\Bundle\\Filter\\AttributeEditRightFilter/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\CatalogBundle\\Filter\\AttributeGroupViewRightFilter/Akeneo\\Pim\\Permission\\Bundle\\Filter\\AttributeGroupViewRightFilter/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\CatalogBundle\\Filter\\AttributeViewRightFilter/Akeneo\\Pim\\Permission\\Bundle\\Filter\\AttributeViewRightFilter/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\CatalogBundle\\Filter\\LocaleEditRightFilter/Akeneo\\Pim\\Permission\\Bundle\\Filter\\LocaleEditRightFilter/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\CatalogBundle\\Filter\\LocaleViewRightFilter/Akeneo\\Pim\\Permission\\Bundle\\Filter\\LocaleViewRightFilter/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\CatalogBundle\\Filter\\ProductAndProductModelDeleteRightFilter/Akeneo\\Pim\\Permission\\Bundle\\Filter\\ProductAndProductModelDeleteRightFilter/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\CatalogBundle\\Filter\\ProductRightEditFilter/Akeneo\\Pim\\Permission\\Bundle\\Filter\\ProductRightEditFilter/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\CatalogBundle\\Filter\\ProductRightViewFilter/Akeneo\\Pim\\Permission\\Bundle\\Filter\\ProductRightViewFilter/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\CatalogBundle\\Filter\\ProductValueAttributeGroupRightFilter/Akeneo\\Pim\\Permission\\Bundle\\Filter\\ProductValueAttributeGroupRightFilter/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\CatalogBundle\\Filter\\ProductValueLocaleRightFilter/Akeneo\\Pim\\Permission\\Bundle\\Filter\\ProductValueLocaleRightFilter/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\CatalogBundle\\Doctrine\\ORM\\Repository\\AttributeRepository/Akeneo\\Pim\\Permission\\Bundle\\Persistence\\ORM\\Attribute\\AttributeRepository/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\CatalogBundle\\Manager\\CategoryManager/Akeneo\\Pim\\Permission\\Bundle\\Persistence\\ORM\\Category\\CategoryManager/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\CatalogBundle\\Doctrine\\ORM\\Repository\\ProductMassActionRepository/Akeneo\\Pim\\Permission\\Bundle\\Persistence\\ORM\\EntityWithValue\\ProductMassActionRepository/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\CatalogBundle\\Doctrine\\ORM\\Repository\\ProductModelRepository/Akeneo\\Pim\\Permission\\Bundle\\Persistence\\ORM\\EntityWithValue\\ProductModelRepository/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\CatalogBundle\\Security\\Elasticsearch\\ProductQueryBuilderFactory/Akeneo\\Pim\\Permission\\Bundle\\Persistence\\ORM\\EntityWithValue\\ProductQueryBuilderFactory/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\CatalogBundle\\Doctrine\\ORM\\Repository\\ProductRepository/Akeneo\\Pim\\Permission\\Bundle\\Persistence\\ORM\\EntityWithValue\\ProductRepository/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\CatalogBundle\\Security\\Doctrine\\Common\\Saver\\FilteredEntitySaver/Akeneo\\Pim\\Permission\\Bundle\\Persistence\\ORM\\FilteredEntitySaver/g'
```
