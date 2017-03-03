# UPGRADE FROM 1.6 to 1.7

<!-- START doctoc generated TOC please keep comment here to allow auto update -->
<!-- DON'T EDIT THIS SECTION, INSTEAD RE-RUN doctoc TO UPDATE -->
**Table of Contents:**

- [Disclaimer](#disclaimer)
- [Migrate your standard project](#migrate-your-standard-project)
- [Migrate your custom code](#migrate-your-custom-code)
  - [Structured normalizers to Standard Normalizers](#structured-normalizers-to-standard-normalizers)
  - [Import/export UI migration](#importexport-ui-migration)
  - [Update references to business reader classes that have been moved](#update-references-to-business-reader-classes-that-have-been-moved)
  - [Versioning formats](#versioning-formats)
  - [Operator](#operator)
  - [Various updated references](#various-updated-references)
  - [CSS Refactoring](#css-refactoring)

<!-- END doctoc generated TOC please keep comment here to allow auto update -->
<!-- To update this content, execute `doctoc UPGRADE-1.7.md --title '**Table of Contents:**' --maxlevel 3` -->

## Disclaimer

> Please check that you're using Akeneo PIM v1.6

> We're assuming that you created your project from the standard distribution

> This documentation helps to migrate projects based on the Community Edition and the Enterprise Edition

> Please perform a backup of your database before proceeding to the migration. You can use tools like [mysqldump](http://dev.mysql.com/doc/refman/5.1/en/mysqldump.html) and [mongodump](http://docs.mongodb.org/manual/reference/program/mongodump/).

> Please perform a backup of your codebase if you don't use a VCS (Version Control System).


## Migrate your standard project

1. Download and extract the latest standard archive,

    * Download it from the website [PIM community standard](http://www.akeneo.com/download/) and extract:

    ```bash
wget http://download.akeneo.com/pim-community-standard-v1.7-latest.tar.gz
tar -zxf pim-community-standard-v1.7-latest.tar.gz
cd pim-community-standard/
    ```

2. Copy the following files to your PIM installation:

    ```bash
export PIM_DIR=/path/to/your/pim/installation
cp app/SymfonyRequirements.php $PIM_DIR/app
cp app/PimRequirements.php $PIM_DIR/app

mv $PIM_DIR/app/config/pim_parameters.yml $PIM_DIR/app/config/pim_parameters.yml.bak
cp app/config/pim_parameters.yml $PIM_DIR/app/config

mv $PIM_DIR/composer.json $PIM_DIR/composer.json.bak
cp composer.json $PIM_DIR/
    ```

3. Update the configuration of your application `$PIM_DIR/app/config/config.yml` to add these new lines:

    ```YAML
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

4. Update the security configuration `$PIM_DIR/app/config/security.yml`:

    Add these new lines under `security.firewalls`:
   
    ```YAML
oauth_token:
        pattern:                        ^/api/oauth/v1/token
        security:                       false
api_index:
        pattern:                        ^/api/rest/v1$
        security:                       false
api:
        pattern:                        ^/api
        fos_oauth:                      true
        stateless:                      true
        access_denied_handler:          pim_api.security.access_denied_handler
    ```

    Add these new lines under `security.access_control`:
    
    ```YAML
- { path: ^/api/rest/v1$, role: IS_AUTHENTICATED_ANONYMOUSLY }
- { path: ^/api/, role: pim_api_overall_access }
    ```

    Remove these lines under `security.firewalls`:
    
    ```YAML
wsse_secured:
        pattern:                        ^/api/(rest|soap).*
        wsse:
            lifetime:                   3600
            realm:                      "Secured API"
            profile:                    "UsernameToken"
        context:                        main
    ```

5. Update your application Kernel `$PIM_DIR/app/AppKernel.php`:

    * Remove the following bundles:

    ```PHP
Oro\Bundle\UIBundle\OroUIBundle,
Oro\Bundle\FormBundle\OroFormBundle,
Pim\Bundle\WebServiceBundle\PimWebServiceBundle,
    ```

    * Add the following bundles in the following functions:

        - `getPimDependenciesBundles()`:

          ```PHP
new FOS\OAuthServerBundle\FOSOAuthServerBundle()
          ```

        - `getPimBundles()`:

          ```PHP
new Pim\Bundle\ApiBundle\PimApiBundle()
          ```

6. Update your routing configuration `$PIM_DIR/app/config/routing.yml`:

    * Remove the following lines:

    ```YAML
pim_webservice:
        resource: "@PimWebServiceBundle/Resources/config/routing.yml"
    ```

    * Add the following lines:

    ```YAML
pim_api:
        resource: "@PimApiBundle/Resources/config/routing.yml"
        prefix: /api
    ```

7. Then remove your old upgrades folder:

    ```bash
rm -rf $PIM_DIR/upgrades/schema
    ```

8. Now update your dependencies:

    * [Optional] If you had added dependencies to your project, you will need to do it again in your `composer.json`.
      You can display the differences of your previous composer.json in `$PIM_DIR/composer.json.bak`.

        ```JSON
    "require": {
            "your/dependency": "version",
            "your/other-dependency": "version",
    }
        ```

    * Then run the command to update your dependencies:

        ```bash
php -d memory_limit=3G composer update
        ```

        This step will copy the upgrades folder from `pim-community-dev/` to your Pim project root in order to migrate.
        If you have custom code in your project, this step may raise errors in the "post-script" command.
        In this case, go to the chapter "Migrate your custom code" before running the database migration.

9. Then you can migrate your database using:

    ```bash
php app/console cache:clear --env=prod
php app/console doctrine:migration:migrate --env=prod
    ```

10. Then, generate JS translations and re-generate the PIM assets:

    ```bash
rm -rf $PIM_DIR/web/js/translation/*
php app/console pim:installer:assets
    ```

## Migrate your custom code

With the 1.7 edition of the PIM come several technical improvements.
This chapter lists most of the actions to do in your custom code to manually or automatically change service or class names.
These instructions will fix most of the migrations of your custom code. The entire list of backward compatibility breaks is available for
[the Community Edition](https://github.com/akeneo/pim-community-dev/blob/1.7/CHANGELOG-1.7.md#bc-breaks).

The provided commands are based on a custom code located in `$PIM_DIR/src/`; if this is not the case, please update their paths before running them.

### Structured normalizers to Standard Normalizers

The 1.7 edition introduces a "standard" format to be able to use a unified format for every normalizer and denormalizer.
In order to use the standard format, Structured Normalizers have been replaced by Standard Normalizers.
The definition of the complete standard format can be find in the (documentation)[https://docs.akeneo.com/1.7/reference/standard_format/index.html].

The following commands help to migrate references to these classes or services.
```bash
find ./src/ -type f -print0 | xargs -0 sed -i 's/Akeneo\\Component\\Batch\\Normalizer\\Structured\\JobInstanceNormalizer/Akeneo\\Component\\Batch\\Normalizer\\Standard\\JobInstanceNormalizer/g'
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
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Denormalizer\\Structured/Pim\\Component\\Catalog\\Denormalizer\\Standard/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\ReferenceData\\Denormalizer\\Structured/Pim\\Component\\ReferenceData\\Denormalizer\\Standard/g'
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
find ./src/ -type f -print0 | xargs -0 sed -i 's/pim_serializer\.normalizer\.job_instance/pim_catalog\.normalizer\.standard\.job_instance/g'
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
find ./src/ -type f -print0 | xargs -0 sed -i 's/pim_connector\.array_converter\.structured\.job_instance/pim_connector\.array_converter\.standard\.job_instance/g'
```

To call these normalizers via the Symfony Normalizer service, the key `standard` has to be filled in. Example:

```PHP
$this->normalizer->normalize($entity, 'standard');
```

Originally, the Normalizer `Pim\Component\Catalog\Normalizer\Structured\GroupNormalizer` was used to normalize both Groups and Variant Groups.
This normalizer has been split in two distinct normalizers:

* [`Pim\Component\Catalog\Normalizer\Standard\GroupNormalizer`](https://github.com/akeneo/pim-community-dev/blob/1.7/src/Pim/Component/Catalog/Normalizer/Standard/GroupNormalizer.php) class is used to normalize Groups
* [`Pim\Component\Catalog\Normalizer\Standard\VariantGroupNormalizer`](https://github.com/akeneo/pim-community-dev/blob/1.7/src/Pim/Component/Catalog/Normalizer/Standard/VariantGroupNormalizer.php) class is used to normalize Variant Groups

In order to use the right one, a proxy group normalizer
[`Pim\Component\Catalog\Normalizer\Standard\ProxyGroupNormalizer`](https://github.com/akeneo/pim-community-dev/blob/1.7/src/Pim/Component/Catalog/Normalizer/Standard/ProxyGroupNormalizer.php)
has been created. This proxy normalizer will be used instead of `Pim\Component\Catalog\Normalizer\Structured\GroupNormalizer`.

### Import/export UI migration

With this 1.7 edition, we migrated the old import/export configuration screens to new javascript architecture. It means
that if you had customized them, you will need to migrate your configuration to the new one.

There are three levels of customization:

#### You only added custom import/export without UI changes

In this case, you only need to add your custom form provider for your connector.
In the 1.6, you declared an import/export with a default values provider, a constraint collection provider and a form configuration provider.
In the 1.7, the default values provider and constraint collection provider do not change, but the form configuration provider have to be replaced by a form job instance.

In the 1.6 edition, the service was defined like this:

```YAML
services:
    acme.job_parameters.form_configuration_provider.simple_csv_import:
        class: '%pim_import_export.job_parameters.form_configuration_provider.simple_csv_import.class%'
        arguments:
            -
                - 'my_custom_import_job_name'
        tags:
            - { name: pim_import_export.job_parameters.form_configuration_provider }
```

In the 1.7, you have to declare a new form job instance like this:

```YAML
services:
    acme_dummy_connector.provider.form.job_instance:
        class: '%pim_enrich.provider.form.job_instance.class%'
        arguments:
            -
                my_custom_import_job_name: pim-job-instance-csv-base-import
        tags:
            - { name: pim_enrich.provider.form, priority: 100 }
```

You can find the loaded JS modules of `pim-job-instance-csv-base-import` in
[view mode](https://github.com/akeneo/pim-community-dev/blob/1.7/src/Pim/Bundle/EnrichBundle/Resources/config/form_extensions/job_instance/csv_base_import_show.yml) and
[edit mode](https://github.com/akeneo/pim-community-dev/blob/1.7/src/Pim/Bundle/EnrichBundle/Resources/config/form_extensions/job_instance/csv_base_import_edit.yml).

#### You added some fields to your custom job

In this case you will also need to register it in your form provider but also declare a custom form. You will find a
detailed documentation [here](https://docs.akeneo.com/1.7/cookbook/import_export/create-connector.html).

#### You created a fully customized screen for your job

In this case, you will have to redo this screen with the new javascript architecture and register it like we've seen
above.


### Update references to business reader classes that have been moved

In order to be more precise about the roles of our existing file iterators, we have renamed some existing classes since
existing file iterators would only support a tabular file format, such as CSV and XLSX.

Please execute the following commands in your project folder to update the references you may have to these classes:

```bash
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Connector\\Reader\\File\\FileIterator/Pim\\Component\\Connector\\Reader\\File\\FlatFileIterator/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/pim_connector\.reader\.file\.file_iterator\.class/pim_connector\.reader\.file\.flat_file_iterator\.class/g'
```

### Versioning formats

Previously, to normalize an entity for versioning, allowed formats were `flat` and `csv`. To avoid confusion, only `flat` format will be allowed.

### Operator

For consistency we changed the variable name of an operator. To update your project you can run this command

```bash
find ./src/ -type f -print0 | xargs -0 sed -i 's/Operators::NOT_LIKE/Operators::IS_NOT_LIKE/g'
```

### Various updated references

The following command helps to migrate updated references:

```bash
find ./src/ -type f -print0 | xargs -0 sed -i 's/pim_user_user_rest_get/pim_user_user_rest_get_current/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\ImportExportBundle\\Validator\\Constraints\\WritableDirectory/Pim\\Component\\Catalog\\Validator\\Constraints\\WritableDirectory/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Connector\\Validator\\Constraints\\Channel/Pim\\Component\\Catalog\\Validator\\Constraints\\Channel/g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/pim_import_export\.repository\.job_instance/akeneo_batch\.job\.job_instance_repository/g'
```

### CSS Refactoring

The 1.7 edition comes with a remake of a large part of the CSS, with the implementation of [BEM methodology](http://getbem.com/introduction/).
For more information about our choices, please read the [Akeneo Style guide documentation](https://docs.akeneo.com/1.7/styleguide/).

This work has been done for several reasons:

- Remove all the unused CSS declarations (~ 8600 CSS lines)
- Make re-usable components (independent of context)
- Avoid hard overriding (`!important` or selectors with tags are now forbidden)
- List all the components for developers
- Split code into dedicated files [in one folder](https://github.com/akeneo/pim-community-dev/tree/1.7/src/Pim/Bundle/UIBundle/Resources/public/less/components)

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

#### Non-exhaustive changes

The next table lists usual previous classes and the new ones to use.

| 1.6                        | 1.7                                        |
| -------------------------- | ------------------------------------------ |
| `<div class="btn">`        | `<div class="AknButton">`                  |
| `<div class="grid">`       | `<div class="AknGrid">`                    |
| `<input type="text">`      | `<input type="text" class="AknTextField">` |
| `<input type="btn-group">` | `<input type="AknButtonList">`             |

The complete list of changes is available on [Akeneo Style guide documentation](https://docs.akeneo.com/1.7/styleguide/).
