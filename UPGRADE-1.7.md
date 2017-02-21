# UPGRADE FROM 1.6 to 1.7

<!-- START doctoc generated TOC please keep comment here to allow auto update -->
<!-- DON'T EDIT THIS SECTION, INSTEAD RE-RUN doctoc TO UPDATE -->
**Table of Contents:**

- [Disclaimer](#disclaimer)
- [Migrate your standard project](#migrate-your-standard-project)
- [Standard Normalizers](#standard-normalizers)
- [Migrate your custom code](#migrate-your-custom-code)
  - [Import/export UI migration](#importexport-ui-migration)
    - [You only added custom import/export without UI changes](#you-only-added-custom-importexport-without-ui-changes)
    - [You added some fields to your custom job](#you-added-some-fields-to-your-custom-job)
    - [You created a fully customized screen for your job](#you-created-a-fully-customized-screen-for-your-job)
  - [Global updates for any project](#global-updates-for-any-project)
    - [Update references to moved `Pim\Bundle\ConnectorBundle\Reader` business classes](#update-references-to-moved-pim%5Cbundle%5Cconnectorbundle%5Creader-business-classes)
    - [Update references to the standardized `Pim\Component\Catalog\Normalizer\Standard` classes](#update-references-to-the-standardized-pim%5Ccomponent%5Ccatalog%5Cnormalizer%5Cstandard-classes)
    - [Versioning](#versioning)
    - [Operator](#operator)
  - [Rule structure modifications](#rule-structure-modifications)
    - [Metrics](#metrics)
    - [Prices](#prices)
    - [Pictures and files](#pictures-and-files)
  - [CSS Refactoring](#css-refactoring)
    - [Examples](#examples)
    - [Non-exhausting changes](#non-exhausting-changes)

<!-- END doctoc generated TOC please keep comment here to allow auto update -->
<!-- To update doctoc UPGRADE-1.7.md --title '**Table of Contents:**' -->

## Disclaimer

> Please check that you're using Akeneo PIM v1.6

> We're assuming that you created your project from the standard distribution

> This documentation helps to migrate projects based on the Enterprise Edition

> Please perform a backup of your database before proceeding to the migration. You can use tools like [mysqldump](http://dev.mysql.com/doc/refman/5.1/en/mysqldump.html) and [mongodump](http://docs.mongodb.org/manual/reference/program/mongodump/).

> Please perform a backup of your codebase if you don't use a VCS (Version Control System).

## Migrate your standard project

1. Download and extract the latest standard archive,

    * Download the archive from the Partner Portal and extract:

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

    * Add this configuration:

    ```
    # FOSOAuthServer Configuration
    fos_oauth_server:
        db_driver:                orm
        client_class:             Pim\Bundle\ApiBundle\Entity\Client
        access_token_class:       Pim\Bundle\ApiBundle\Entity\AccessToken
        refresh_token_class:      Pim\Bundle\ApiBundle\Entity\RefreshToken
        auth_code_class:          Pim\Bundle\ApiBundle\Entity\AuthCode
        service:
            user_provider:        pim_user.provider.user
    ```

4. Update your **app/AppKernel.php**:

    * Remove the following bundle:
        - `Oro\Bundle\UIBundle\OroUIBundle`
        - `Oro\Bundle\FormBundle\OroFormBundle`
        - `Pim\Bundle\WebServiceBundle\PimWebServiceBundle`
        - `PimEnterprise\Bundle\WebServiceBundle\PimEnterpriseWebServiceBundle`

    * Add the following bundles:
        - `FOS\OAuthServerBundle\FOSOAuthServerBundle`
        - `Pim\Bundle\ApiBundle\PimApiBundle`
        - `PimEnterprise\Bundle\ActivityManagerBundle\PimEnterpriseActivityManagerBundle`

5. Update your **app/config/routing.yml**:

    * Remove the route: `pim_webservice`

    * Add this configuration:
    
    ```
    pimee_activity_manager:
        resource: "@PimEnterpriseActivityManagerBundle/Resources/config/routing/routing.yml"

    pim_api:
        resource: "@PimApiBundle/Resources/config/routing.yml"
    ```

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

    * Then run the command to update your dependencies:

        ```
         cd $PIM_DIR
         composer update
        ```

        This step will also copy the upgrades folder from `vendor/akeneo/pim-enterprise-dev/` to your Pim project root in order to migrate.

        If you have custom code in your project, this step may raise errors in the "post-script" command.

        In this case, go to the chapter "Migrate your custom code" before running the database migration.

8. Then you can migrate your database using:

    ```
     php app/console cache:clear --env=prod
     php app/console doctrine:migration:migrate --env=prod
    ```

9. Then, generate JS translations and re-generate the PIM assets:

    ```
     rm -rf $PIM_DIR/web/js/translation/*
     php app/console pim:installer:assets
    ```
    
## Standard Normalizers

In order to use the standard format, Structured Normalizers have been replaced by Standard Normalizers.

The following command helps to migrate references to these classes or services.
```
    find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Component\\Catalog\\Normalizer\\Structured\\AttributeNormalizer/PimEnterprise\\Component\\Catalog\\Normalizer\\Standard\\AttributeNormalizer/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Component\\ProductAsset\\Normalizer\\Structured\\AssetNormalizer/PimEnterprise\\Component\\ProductAsset\\Normalizer\\Standard\\AssetNormalizer/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Component\\ProductAsset\\Normalizer\\Structured\\ChannelConfigurationNormalizer/PimEnterprise\\Component\\ProductAsset\\Normalizer\\Standard\\ChannelConfigurationNormalizer/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Component\\ProductAsset\\Normalizer\\Structured\\VariationNormalizer/PimEnterprise\\Component\\ProductAsset\\Normalizer\\Standard\\VariationNormalizer/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/pimee_serializer\.normalizer\.structured\.attribute/pimee_catalog\.normalizer\.standard\.attribute/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/pim_product_asset\.normalizer\.structured\.asset/pimee_product_asset\.normalizer\.standard\.asset/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/pim_product_asset\.normalizer\.structured\.variation/pimee_product_asset\.normalizer\.standard\.variation/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/pim_product_asset\.normalizer\.structured\.channel_configuration/pimee_product_asset\.normalizer\.standard\.channel_configuration/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/pim_product_asset\.normalizer\.flat\.asset/pimee_product_asset\.normalizer\.flat\.asset/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/pimee_enrich\.normalizer\.structured\.asset/pimee_enrich\.normalizer\.standard\.asset/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/akeneo_rule_engine\.normalizer\.rule:/akeneo_rule_engine\.normalizer\.standard\.rule:/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/akeneo_rule_engine\.normalizer\.rule\.class/akeneo_rule_engine\.normalizer\.standard\.rule\.class/g'
```

## Migrate your custom code

### Import/export UI migration

With this 1.7 version, we migrated the old import/export configuration screens to new javascript architecture. It means
that if you had customized them, you will need to migrate your configuration to the new one.

There is three level of customization:

#### You only added custom import/export without UI changes

In this case, you only need to add your custom form provider for your connector. Here is an example:

```
services:
    acme_dummy_connector.provider.form.job_instance:
        class: '%pim_enrich.provider.form.job_instance.class%'
        arguments:
            -
                my_custom_export_job_name: pim-job-instance-csv-base-export
                my_custom_import_job_name: pim-job-instance-csv-base-import
```

#### You added some fields to your custom job

In this case you will also need to register it in your form provider but aslo declare a custom form. You will find a
detailed documentation [here](https://docs.akeneo.com/1.7/cookbook/import_export/create-connector.html).

#### You created a fully customized screen for your job

In this case, you will have to redo this screen with the new javascript architecture and register it like we've seen
above.

### Global updates for any project

#### Update references to moved `Pim\Bundle\ConnectorBundle\Reader` business classes

In order to be more precise about the roles our existing file iterators have we renamed some existing classed as existing file iterators would only supports only tabular file format like CSV and XLSX.

Please execute the following commands in your project folder to update the references you may have to these classes:
```
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Connector\\Reader\\File\\FileIterator/Pim\\Component\\Connector\\Reader\\File\\FlatFileIterator/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/pim_connector\.reader\.file\.file_iterator\.class/pim_connector\.reader\.file\.flat_file_iterator\.class/g'
```

#### Update references to the standardized `Pim\Component\Catalog\Normalizer\Standard` classes

In order to use the standard format, Structured Normalizers have been replaced by Standard Normalizers.
To call these normalizers via the Symfony Normalizer service, the key `standard` has to be filled. Example:

```
     $this->normalizer->normalize($entity, 'standard');
```

Originally, the Normalizer `Pim\Component\Catalog\Normalizer\Structured\GroupNormalizer` was used to normalize both Groups and Variant Groups.
This normalizer has been split in two distinct normalizers :

* `Pim\Component\Catalog\Normalizer\Standard\GroupNormalizer` class is used to normalize Groups
* `Pim\Component\Catalog\Normalizer\Standard\VariantGroupNormalizer` class is used to normalize Variant Groups

In order to use the good one, a proxy group normalizer `Pim\Component\Catalog\Normalizer\Standard\ProxyGroupNormalizer` has been created.
This proxy normalizer will be used  instead of `Pim\Component\Catalog\Normalizer\Structured\GroupNormalizer`.

The following command helps to migrate references to Normalizer classes or services :
```
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Normalizer\\Structured\\AssociationTypeNormalizer/Pim\\Component\\Catalog\\Normalizer\\Standard\\AssociationTypeNormalizer/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Normalizer\\Structured\\AttributeGroupNormalizer/Pim\\Component\\Catalog\\Normalizer\\Standard\\AttributeGroupNormalizer/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Normalizer\\Structured\\AttributeNormalizer/Pim\\Component\\Catalog\\Normalizer\\Standard\\AttributeNormalizer/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Normalizer\\Structured\\AttributeOptionNormalizer/Pim\\Component\\Catalog\\Normalizer\\Standard\\AttributeOptionNormalizer/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Normalizer\\Structured\\CategoryNormalizer/Pim\\Component\\Catalog\\Normalizer\\Standard\\CategoryNormalizer/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Normalizer\\Structured\\ChannelNormalizer/Pim\\Component\\Catalog\\Normalizer\\Standard\\ChannelNormalizer/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Normalizer\\Structured\\CurrencyNormalizer/Pim\\Component\\Catalog\\Normalizer\\Standard\\CurrencyNormalizer/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Normalizer\\Structured\\DateTimeNormalizer/Pim\\Component\\Catalog\\Normalizer\\Standard\\DateTimeNormalizer/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Normalizer\\Structured\\FamilyNormalizer/Pim\\Component\\Catalog\\Normalizer\\Standard\\FamilyNormalizer/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Normalizer\\Structured\\FileNormalizer/Pim\\Component\\Catalog\\Normalizer\\Standard\\FileNormalizer/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Normalizer\\Structured\\GroupNormalizer/Pim\\Component\\Catalog\\Normalizer\\Standard\\ProxyGroupNormalizer/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Normalizer\\Structured\\GroupTypeNormalizer/Pim\\Component\\Catalog\\Normalizer\\Standard\\GroupTypeNormalizer/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Normalizer\\Structured\\LocaleNormalizer/Pim\\Component\\Catalog\\Normalizer\\Standard\\LocaleNormalizer/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Normalizer\\Structured\\MetricNormalizer/Pim\\Component\\Catalog\\Normalizer\\Standard\\Product\\MetricNormalizer/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Normalizer\\Structured\\ProductAssociationsNormalizer/Pim\\Component\\Catalog\\Normalizer\\Standard\\Product\\AssociationsNormalizer/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Normalizer\\Structured\\ProductNormalizer/Pim\\Component\\Catalog\\Normalizer\\Standard\\ProductNormalizer/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Normalizer\\Structured\\ProductPriceNormalizer/Pim\\Component\\Catalog\\Normalizer\\Standard\\Product\\PriceNormalizer/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Normalizer\\Structured\\ProductPropertiesNormalizer/Pim\\Component\\Catalog\\Normalizer\\Standard\\Product\\PropertiesNormalizer/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Normalizer\\Structured\\ProductValueNormalizer/Pim\\Component\\Catalog\\Normalizer\\Standard\\Product\\ProductValueNormalizer/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Normalizer\\Structured\\ProductValuesNormalizer/Pim\\Component\\Catalog\\Normalizer\\Standard\\Product\\ProductValuesNormalizer/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Normalizer\\Structured\\TranslationNormalizer/Pim\\Component\\Catalog\\Normalizer\\Standard\\TranslationNormalizer/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Comment\\Normalizer\\Structured\\CommentNormalizer/Pim\\Component\\Comment\\Normalizer\\Standard\\CommentNormalizer/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/pim_serializer\.normalizer\.association_type/pim_catalog\.normalizer\.standard\.association_type/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/pim_serializer\.normalizer\.attribute/pim_catalog\.normalizer\.standard\.attribute/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/pim_serializer\.normalizer\.attribute_group/pim_catalog\.normalizer\.standard\.attribute_group/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/pim_serializer\.normalizer\.attribute_option/pim_catalog\.normalizer\.standard\.attribute_option/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/pim_serializer\.normalizer\.category/pim_catalog\.normalizer\.standard\.category/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/pim_serializer\.normalizer\.channel/pim_catalog\.normalizer\.standard\.channel/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/pim_serializer\.normalizer\.datetime/pim_catalog\.normalizer\.standard\.datetime/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/pim_serializer\.normalizer\.family/pim_catalog\.normalizer\.standard\.family/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/pim_serializer\.normalizer\.group/pim_catalog\.normalizer\.standard\.proxy_group/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/pim_serializer\.normalizer\.product/pim_catalog\.normalizer\.standard\.product/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/pim_serializer\.normalizer\.product_properties/pim_catalog\.normalizer\.standard\.product\.properties/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/pim_serializer\.normalizer\.product_associations/pim_catalog\.normalizer\.standard\.product\.associations/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/pim_serializer\.normalizer\.product_values/pim_catalog\.normalizer\.standard\.product\.product_values/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/pim_serializer\.normalizer\.product_value/pim_catalog\.normalizer\.standard\.product\.product_value/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/pim_serializer\.normalizer\.product_price/pim_catalog\.normalizer\.standard\.product\.price/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/pim_serializer\.normalizer\.metric/pim_catalog\.normalizer\.standard\.product\.metric/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/pim_serializer\.normalizer\.file/pim_catalog\.normalizer\.standard\.file/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/pim_serializer\.normalizer\.currency/pim_catalog\.normalizer\.standard\.currency/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/pim_serializer\.normalizer\.group_type/pim_catalog\.normalizer\.standard\.group_type/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/pim_serializer\.normalizer\.locale/pim_catalog\.normalizer\.standard\.locale/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/pim_serializer\.normalizer\.label_translation/pim_catalog\.normalizer\.standard\.translation/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/pim_serializer\.normalizer\.comment/pim_comment\.normalizer\.standard\.comment/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Denormalizer\\Structured/Pim\\Component\\Catalog\\Denormalizer\\Standard/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\ReferenceData\\Denormalizer\\Structured/Pim\\Component\\ReferenceData\\Denormalizer\\Standard/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Akeneo\\Component\\Batch\\Normalizer\\Structured\\JobInstanceNormalizer/Akeneo\\Component\\Batch\\Normalizer\\Standard\\JobInstanceNormalizer/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/pim_serializer\.normalizer\.job_instance/pim_catalog\.normalizer\.standard\.job_instance/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/pim_connector\.array_converter\.structured\.job_instance/pim_connector\.array_converter\.standard\.job_instance/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/pim_serializer\.denormalizer\.product_values/pim_catalog\.denormalizer\.standard\.product_values/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/pim_serializer\.denormalizer\.product_value/pim_catalog\.denormalizer\.standard\.product_value/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/pim_serializer\.denormalizer\.base_value/pim_catalog\.denormalizer\.standard\.base_value/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/pim_serializer\.denormalizer\.attribute_option/pim_catalog\.denormalizer\.standard\.attribute_option/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/pim_serializer\.denormalizer\.attribute_options/pim_catalog\.denormalizer\.standard\.attribute_options/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/pim_serializer\.denormalizer\.prices/pim_catalog\.denormalizer\.standard\.prices/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/pim_serializer\.denormalizer\.metric/pim_catalog\.denormalizer\.standard\.metric/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/pim_serializer\.denormalizer\.number/pim_catalog\.denormalizer\.standard\.number/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/pim_serializer\.denormalizer\.datetime/pim_catalog\.denormalizer\.standard\.datetime/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/pim_serializer\.denormalizer\.file/pim_catalog\.denormalizer\.standard\.file/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/pim_serializer\.denormalizer\.boolean/pim_catalog\.denormalizer\.standard\.boolean/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/pim_user_user_rest_get/pim_user_user_rest_get_current/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\ImportExportBundle\\Validator\\Constraints\\WritableDirectory/Pim\\Component\\Catalog\\Validator\\Constraints\\WritableDirectory/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Connector\\Validator\\Constraints\\Channel/Pim\\Component\\Catalog\\Validator\\Constraints\\Channel/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/pim_import_export\.repository\.job_instance/akeneo_batch\.job\.job_instance_repository/g'
```

#### Versioning

Previously, to normalize an entity for versioning, formats allowed were `flat` and `csv`. To avoid confusion, only `flat` format will be allowed.

#### Operator

For concistency we changed the variable name of an operator. To update your project you can run this command

```
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Operators::NOT_LIKE/Operators::IS_NOT_LIKE/g'
```

### Rule structure modifications

#### Metrics

In the enrichment rules, the key "data" has been replaced by the key "amount" for metrics.

In 1.6 version, the rule structure was defined like this :

```
field: weight
operator: =
value:
 data: 0.5
 unit: KILOGRAM
```

In 1.7 version, the rule structure is defined like this :

```
field: weight
operator: =
value:
 amount: 0.5
 unit: KILOGRAM
```

#### Prices

In the enrichment rules, the key "data" has been replaced by the key "amount" for prices.

In 1.6 version, the rule structure was defined like this :

```
field: null_price
operator: NOT EMPTY
value:
  data: null
  currency: EUR
```

In 1.7 version, the rule structure is defined like this :

```
field: basic_price
operator: <=
value:
  amount: 12
  currency: EUR
```

#### Pictures and files

In the enrichment rules, the rule structure has been changed for pictures and files.
The notion of original filename has been removed. The filename will be directly determined from the full path.

In 1.6 version, the rule structure was defined like this :

```
field: small_image
operator: CONTAIN
value:
  - filePath: /tmp/image.jpg
  - originalFilename: akeneo.jpg
```

In 1.7 version, the rule structure is defined like this :

```
field: small_image
operator: CONTAIN
value: /tmp/image.jpg
```

According to the full path specified in this example, the filename will be "image.jpg".

### CSS Refactoring

Akeneo 1.7 comes with a refactor of a large part of the CSS, with the implementation of [BEM methodology](http://getbem.com/introduction/).
For more information about our choices, please read the [Akeneo Style guide documentation](https://docs.akeneo.com/master/styleguide/).

This work has been done for several reasons:

- Remove all the unused CSS declarations (~ 8600 CSS lines)
- Make re-usable components (independent of context)
- Avoid hard overriding (`!important` or selectors with tags are now forbidden)
- List all the components for developers
- Split code into dedicated files [in one folder](https://github.com/akeneo/pim-community-dev/tree/master/src/Pim/Bundle/UIBundle/Resources/public/less/components)

If you used styled components in a custom bundle, you have to do some changes manually.

#### Examples

For example, if you had HTML like:
```html
<button class="btn btn-primary">Primary Button</button>
```

You now have to use:
```html
<button class="AknButton AknButton--apply">Primary Button</button>
```

**Warning!** In the previous example, you may use bootstrap `btn` class to catch Javascript events.
We very strongly encourage you to avoid using "style" class to select elements for Javascript events.
A better solution is to add a unique class to your element, like "view-creator", to use for Javascript events.

#### Non-exhausting changes

The next table lists usual previous classes and the new ones to use.

| 1.6                        | 1.7                                        |
| -------------------------- | ------------------------------------------ |
| `<div class="btn">`        | `<div class="AknButton">`                  |
| `<div class="grid">`       | `<div class="AknGrid">`                    |
| `<input type="text">`      | `<input type="text" class="AknTextField">` |
| `<input type="btn-group">` | `<input type="AknButtonList">`             |

The complete list of changes is available on [Akeneo Style guide documentation](https://docs.akeneo.com/master/styleguide/).