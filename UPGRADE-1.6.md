# UPGRADE FROM 1.5 to 1.6

> Please perform a backup of your database before proceeding to the migration. You can use tools like  [mysqldump](http://dev.mysql.com/doc/refman/5.1/en/mysqldump.html) and [mongodump](http://docs.mongodb.org/manual/reference/program/mongodump/).

> Please perform a backup of your codebase if you don't use any VCS.


## Update dependencies and configuration

1. Extract the latest **PIM enterprise standard** you have received by mail:

    ```
     tar -zxf pim-enterprise-standard.tar.gz
     cd pim-enterprise-standard/
    ```

2. Copy the following files to your PIM installation:

    ```
     export PIM_DIR=/path/to/your/pim/installation
     cp app/PimRequirements.php $PIM_DIR/app
     cp app/SymfonyRequirements.php $PIM_DIR/app
     cp app/config/pim_parameters.yml $PIM_DIR/app/config
     cp composer.json $PIM_DIR
    ```

3. Update your **config.yml**:

    * The ProductValue model has been moved into the catalog component.
    
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

    * Remove the configuration of `CatalogBundle` from this file (config tree :`pim_catalog`).
    * Update the default locale from `en_US` to `en`

4. Update your **app/AppKernel.php**:

    * Remove the following bundles: 
        - `PimEnterprise\Bundle\BaseConnectorBundle\PimEnterpriseBaseConnectorBundle`
        - `Pim\Bundle\BaseConnectorBundle\PimBaseConnectorBundle`
        - `Pim\Bundle\TransformBundle\PimTransformBundle`
        - `Nelmio\ApiDocBundle\NelmioApiDocBundle`
        
    * Add the following bundles: 
        - `PimEnterprise\Bundle\ConnectorBundle\PimEnterpriseConnectorBundle` to `getPimEnterpriseBundles`

5. Update your **app/config/routing.yml**: 

    * Remove the route: `nelmio_api_doc`

6. Then remove your old upgrades folder: 
    ```
     rm upgrades/ -rf
    ```

7. Now you're ready to update your dependencies:

    * **Caution**, don't forget to re-add your own dependencies to your *composer.json* in case you have some:
        
        ```
        "require": {
            "your/dependencies": "version",
            "your/other-dependencies": "version",
        }
        ```
        
        Especially you store your product in Mongo, don't forget to add `doctrine/mongodb-odm-bundle`:
        
        ```
        "require": {
            "doctrine/mongodb-odm-bundle": "3.2.0"
        }
        ```
    
    * Then run the command:
    
        ```
         cd $PIM_DIR
         composer update
        ```

8. Then you can migrate your database using:

    ```
     php app/console doctrine:migration:migrate
    ```

## Partially fix BC breaks

If you have a standard installation with some custom code inside, the following command allows to update changed services or use statements.

**It does not cover all possible BC breaks, as the changes of arguments of a service, consider using this script on versioned files to be able to check the changes with a `git diff` for instance.**

Based on a PIM standard installation, execute the following command in your project folder:

```
    find ./src -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\SecurityBundle\\Model/PimEnterprise\\Component\\Security\\Model/g'
    find ./src -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\SecurityBundle\\Entity\\Repository\\AccessRepositoryInterface/PimEnterprise\\Component\\Security\\Repository\\AccessRepositoryInterface/g'
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
    find ./src -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\CatalogBundle\\Model/PimEnterprise\\Component\\Catalog\\Model/g'
    find ./src -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\SecurityBundle\\Attributes/PimEnterprise\\Component\\Security\\Attributes/g'
    find ./src -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\WorkflowBundle\\Publisher\\Product\\FilePublisher/PimEnterprise\\Component\\Workflow\\Publisher\\Product\\FileInfoPublisher/g'
    find ./src -type f -print0 | xargs -0 sed -i 's/ Pim\\Bundle\\TransformBundle\\Normalizer\\Flat/PimEnterprise\\Bundle\\VersioningBundle\\Normalizer\\Flat/g'
    find ./src -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Connector\\ArrayConverter\\StandardArrayConverterInterface/Pim\\Component\\Connector\\ArrayConverter\\ArrayConverterInterface/g'
    find ./src -type f -print0 | xargs -0 sed -i 's/implements StandardArrayConverterInterface/implements ArrayConverterInterface/g'
    find ./src -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Connector\\ArrayConverter\\Flat/Pim\\Component\\Connector\\ArrayConverter\\FlatToStandard/g'
    find ./src -type f -print0 | xargs -0 sed -i 's/pim_connector\.array_converter\.flat\./pim_connector\.array_converter\.flat_to_standard\./g'
    find ./src -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Component\\ProductAsset\\Connector\\ArrayConverter\\FlatToStandard\\Tag/PimEnterprise\\Component\\ProductAsset\\Connector\\ArrayConverter\\FlatToStandard\\Tags/g'
    find ./src -type f -print0 | xargs -0 sed -i 's/pimee_product_asset\.array_converter.flat_to_standard.tag/pimee_product_asset.array_converter.flat_to_standard.tags/g'
    find ./src -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Component\\Workflow\\Connector\\ArrayConverter\\FlatToStandard\\ProductDraft/PimEnterprise\\Component\\Workflow\\Connector\\ArrayConverter\\FlatToStandard\\ProductDraftChanges/g'
    find ./src -type f -print0 | xargs -0 sed -i 's/pimee_base_connector\.array_converter\.flat_to_standard\.product_draft/pimee_base_connector\.array_converter\.flat_to_standard\.product_draft_changes/g'
    find ./src -type f -print0 | xargs -0 sed -i 's/pimee_catalog_rule\.reader\.doctrine\.rule_definition/pimee_catalog_rule\.reader\.database\.rule_definition/g'
    find ./src -type f -print0 | xargs -0 sed -i 's/pimee_product_asset\.reader\.orm\.asset/pimee_product_asset\.reader\.database\.asset/g'
    find ./src -type f -print0 | xargs -0 sed -i 's/pimee_product_asset\.reader\.orm\.csv_channel_configuration/pimee_product_asset\.reader\.database\.channel_configuration/g'
    find ./src -type f -print0 | xargs -0 sed -i 's/pimee_product_asset\.reader\.orm\.variation/pimee_product_asset\.reader\.database\.variation/g'
    find ./src -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Component\\ProductAsset\\Connector\\Reader\\Doctrine\\AssetCategoryReader/PimEnterprise\\Component\\ProductAsset\\Connector\\Reader\\Database\\AssetCategoryReader/g'
    find ./src -type f -print0 | xargs -0 sed -i 's/pimee_product_asset\.reader\.orm\.category/pimee_product_asset\.reader\.database\.category/g'
    find ./src -type f -print0 | xargs -0 sed -i 's/pimee_connector\.reader\.file\.yaml_channel_configuration/pimee_product_asset\.reader\.file\.yaml_channel_configuration/g'
    find ./src -type f -print0 | xargs -0 sed -i 's/pim_connector\.reader\.file\.yaml_rule/pimee_catalog_rule\.reader\.file\.yaml_rule/g'
```
