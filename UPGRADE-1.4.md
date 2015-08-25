# UPGRADE FROM 1.3 to 1.4

> Please perform a backup of your database before proceeding to the migration. You can use tools like  [mysqldump](http://dev.mysql.com/doc/refman/5.1/en/mysqldump.html) and [mongodump](http://docs.mongodb.org/manual/reference/program/mongodump/).

> Please perform a backup of your codebase if you don't use any VCS.

## ENHANCED UPDATER API

The 1.4 enhances the Updater API (introduced in 1.3).

In 1.3, the API covers only update of values of a product (set and copy), with the 1.4 we:
 - provide updaters for other objects (ObjectUpdaterInterface::update)
 - provide a way to set fields and attribute values of product (PropertySetterInterface::setData)
 - provide a way to add data in fields and attribute values of product (PropertyAdderInterface::addData)
 - provide a way to remove data in fields and attribute values of product (PropertyRemoverInterface::removeData)
 - provide a way to copy data in fields and attribute values of product (PropertyCopierInterface::copyData)

The goal of this API is to give a straightforward and normalized way to update objects of the PIM to enhance the Developer Experience.

To achieve a consistent API and avoid BC Breaks, we depreciate few methods from ProductUpdater.

To have a better consistence between updaters and normalizers, `Pim\Bundle\TransformBundle\Normalizer\Structured\ProductValueNormalizer` now returns an array with a "data" key instead of "value" key.
This has an impact on the table `pim_catalog_product_template` which is used by the variant groups for instance. To convert the database structure of this table, you can execute the following command in your project folder:

```
    php upgrades/common/migrate_product_template.php --env=YOUR_ENV
```

## UPGRADE IMPORT/EXPORT

The Import/Export system has been reworked.

The current system has been introduced with the 1.0 and become more and more complex to understand with successive changes.

The challenge is, in one hand to provide a more straightforward and extensible system and in other hand ensure the backward compatibility.

With the current system:
 - BatchBundle is responsible to provide the batch architecture and base classes (inspired by Spring Batch)
 - BaseConnector provides Readers, Processors, Writers, others technicals classes and DI which allows to import and export Catalog Data
 - TransformBundle provides Normalizers and Denormalizers to transform array to object and object to array, some Transformers kind of "extended Denormalizers"
 - ImportExportBundle provides controllers, form and UI

Responsibilities are not that clear, for instance, we have different implementations for a same service, successively introduced and kept for BC concerns.

This part is often used and extended in custom projects and backward compatibility must be handled on classes and DI levels.

To make the new system more understandable, we introduce it in a new ConnectorBundle and depreciate the BaseConnectorBundle.

Strategy is the following,
 - remove the deprecated batch_jobs.yml in the BaseConnectorBundle (to avoid automatic loading)
 - keep old services and classes in the BaseConnector to be backward compatible
 - introduce new classes and services in the new Connector bundle and component
 - behat and specs are runned on deprecated classes and import too

## MIGRATION TO SYMFONY 2.7

PIM has been migrated to Symfony 2.7.
You can read this guide to see all modifications: https://gist.github.com/mickaelandrieu/5211d0047e7a6fbff925.
 
You can execute the following commands in your project folder:

```
    find ./src -type f -print0 | xargs -0 sed -i 's/use Symfony\\Component\\OptionsResolver\\OptionsResolverInterface;/use Symfony\\Component\\OptionsResolver\\OptionsResolver;/g'
    find ./src -type f -print0 | xargs -0 sed -i 's/public function setDefaultOptions(OptionsResolverInterface $resolver)/public function configureOptions(OptionsResolver $resolver)/g'
    find ./src -type f -print0 | xargs -0 sed -i 's/var OptionsResolverInterface/var OptionsResolver/g'
    find ./src -type f -print0 | xargs -0 sed -i 's/* @return OptionsResolverInterface/* @return OptionsResolver/g'
    find ./src -type f -print0 | xargs -0 sed -i 's/use Symfony\\Component\\Validator\\ValidatorInterface;/use Symfony\\Component\\Validator\\Validator\\ValidatorInterface;/g'
    find ./src -type f -print0 | xargs -0 sed -i 's/use Symfony\\Component\\Form\\Extension\\Core\\View\\ChoiceView;/use Symfony\\Component\\Form\\ChoiceList\\View\\ChoiceView;/g'
    find ./src -type f -print0 | xargs -0 sed -i 's/use Symfony\\Component\\Form\\Tests\\Extension\\Core\\Type\\TypeTestCase;/use Symfony\\Component\\Form\\Test\\TypeTestCase;/g'
    find ./src -type f -print0 | xargs -0 sed -i 's/use Symfony\\Component\\Validator\\MetadataFactoryInterface;/use Symfony\\Component\\Validator\\Mapping\\Factory\\MetadataFactoryInterface;/g'
    find ./src -type f -print0 | xargs -0 sed -i 's/use Symfony\\Component\\Validator\\ExecutionContextInterface;/use Symfony\\Component\\Validator\\Context\\ExecutionContextInterface;/g'
```

In 2.7, the `Symfony\Component\Security\Core\SecurityContext` is marked as deprecated in favor of the `Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface` and `Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface` (see: http://symfony.com/blog/new-in-symfony-2-6-security-component-improvements).

```
   isGranted  => AuthorizationCheckerInterface
   getToken   => TokenStorageInterface
   setToken   => TokenStorageInterface
```

To use TokenStorageInterface:
```
    find PATH_OF_SPECIFIC_CLASSES -type f -print0 | xargs -0 sed -i 's/use Symfony\\Component\\Security\\Core\\SecurityContextInterface;/use Symfony\\Component\\Security\\Core\\Authentication\\Token\\Storage\\TokenStorageInterface;/g'
    find PATH_OF_SPECIFIC_CLASSES -type f -print0 | xargs -0 sed -i 's/SecurityContextInterface/TokenStorageInterface/g'
    find PATH_OF_SPECIFIC_CLASSES -type f -print0 | xargs -0 sed -i 's/$this->securityContext/$this->tokenStorage/g'
    find PATH_OF_SPECIFIC_CLASSES -type f -print0 | xargs -0 sed -i 's/getSecurityContext/getTokenStorage/g'
    find PATH_OF_SPECIFIC_CLASSES -type f -print0 | xargs -0 sed -i 's/security.context/security.token_storage/g'
    find PATH_OF_SPECIFIC_CLASSES -type f -print0 | xargs -0 sed -i 's/SecurityContext::/Security::/g'
    find PATH_OF_SPECIFIC_CLASSES -type f -print0 | xargs -0 sed -i 's/$securityContext/$tokenStorage/g'
```

To use AuthorizationCheckerInterface:
```
    find PATH_OF_SPECIFIC_CLASSES -type f -print0 | xargs -0 sed -i 's/use Symfony\\Component\\Security\\Core\\SecurityContextInterface;/use Symfony\\Component\\Security\\Core\\Authorization\\AuthorizationCheckerInterface;/g'
    find PATH_OF_SPECIFIC_CLASSES -type f -print0 | xargs -0 sed -i 's/SecurityContextInterface/AuthorizationCheckerInterface/g'
    find PATH_OF_SPECIFIC_CLASSES -type f -print0 | xargs -0 sed -i 's/$this->securityContext/$this->authorizationChecker/g'
    find PATH_OF_SPECIFIC_CLASSES -type f -print0 | xargs -0 sed -i 's/getSecurityContext/getAuthorizationChecker/g'
    find PATH_OF_SPECIFIC_CLASSES -type f -print0 | xargs -0 sed -i 's/security.context/security.authorization_checker/g'
    find PATH_OF_SPECIFIC_CLASSES -type f -print0 | xargs -0 sed -i 's/SecurityContext::/Security::/g'
    find PATH_OF_SPECIFIC_CLASSES -type f -print0 | xargs -0 sed -i 's/$securityContext/$authorizationChecker/g'
```

## Partially fix BC breaks

If you have a standard installation with some custom code inside, the following command allows to update changed services or use statements.

**It does not cover all possible BC breaks, as the changes of arguments of a service, consider using this script on versioned files to be able to check the changes with a `git diff` for instance.**

Based on a PIM standard installation, execute the following command in your project folder:

```
    find ./src/ -type f -print0 | xargs -0 sed -i 's/CatalogBundle\\Doctrine\\MongoDBODM\\CompletenessRepository/CatalogBundle\\Doctrine\\MongoDBODM\\Repository\\CompletenessRepository/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/CatalogBundle\\Doctrine\\MongoDBODM\\ProductCategoryRepository/CatalogBundle\\Doctrine\\MongoDBODM\\Repository\\ProductCategoryRepository/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/CatalogBundle\\Doctrine\\MongoDBODM\\ProductMassActionRepository/CatalogBundle\\Doctrine\\MongoDBODM\\Repository\\ProductMassActionRepository/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/CatalogBundle\\Doctrine\\MongoDBODM\\ProductRepository/CatalogBundle\\Doctrine\\MongoDBODM\\Repository\\ProductRepository/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/CatalogBundle\\Doctrine\\ORM\\CompletenessRepository/CatalogBundle\\Doctrine\\ORM\\Repository\\CompletenessRepository/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/CatalogBundle\\Doctrine\\ORM\\ProductCategoryRepository/CatalogBundle\\Doctrine\\ORM\\Repository\\ProductCategoryRepository/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/CatalogBundle\\Doctrine\\ORM\\ProductMassActionRepository/CatalogBundle\\Doctrine\\ORM\\Repository\\ProductMassActionRepository/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/CatalogBundle\\Doctrine\\ORM\\ProductRepository/CatalogBundle\\Doctrine\\ORM\\Repository\\ProductRepository/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/CatalogBundle\\Entity\\Repository\\AssociationRepository/CatalogBundle\\Doctrine\\ORM\\Repository\\AssociationRepository/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/CatalogBundle\\Entity\\Repository\\AssociationTypeRepository/CatalogBundle\\Doctrine\\ORM\\Repository\\AssociationTypeRepository/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/CatalogBundle\\Entity\\Repository\\AttributeGroupRepository/CatalogBundle\\Doctrine\\ORM\\Repository\\AttributeGroupRepository/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/CatalogBundle\\Entity\\Repository\\AttributeOptionRepository/CatalogBundle\\Doctrine\\ORM\\Repository\\AttributeOptionRepository/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/CatalogBundle\\Entity\\Repository\\AttributeRepository/CatalogBundle\\Doctrine\\ORM\\Repository\\AttributeRepository/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/CatalogBundle\\Entity\\Repository\\CategoryRepository/CatalogBundle\\Doctrine\\ORM\\Repository\\CategoryRepository/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/CatalogBundle\\Entity\\Repository\\ChannelRepository/CatalogBundle\\Doctrine\\ORM\\Repository\\ChannelRepository/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/CatalogBundle\\Entity\\Repository\\CurrencyRepository/CatalogBundle\\Doctrine\\ORM\\Repository\\CurrencyRepository/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/CatalogBundle\\Entity\\Repository\\FamilyRepository/CatalogBundle\\Doctrine\\ORM\\Repository\\FamilyRepository/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/CatalogBundle\\Entity\\Repository\\GroupRepository/CatalogBundle\\Doctrine\\ORM\\Repository\\GroupRepository/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/CatalogBundle\\Entity\\Repository\\GroupTypeRepository/CatalogBundle\\Doctrine\\ORM\\Repository\\GroupTypeRepository/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/CatalogBundle\\Entity\\Repository\\LocaleRepository/CatalogBundle\\Doctrine\\ORM\\Repository\\LocaleRepository/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\Updater\\Setter\\AbstractValueSetter/Pim\\Component\\Catalog\\Updater\\Setter\\AbstractAttributeSetter/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\Updater\\Setter\\BooleanValueSetter/Pim\\Component\\Catalog\\Updater\\Setter\\BooleanAttributeSetter/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\Updater\\Setter\\DateValueSetter/Pim\\Component\\Catalog\\Updater\\Setter\\DateAttributeSetter/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\Updater\\Setter\\MediaValueSetter/Pim\\Component\\Catalog\\Updater\\Setter\\MediaAttributeSetter/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\Updater\\Setter\\MetricValueSetter/Pim\\Component\\Catalog\\Updater\\Setter\\MetricAttributeSetter/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\Updater\\Setter\\MultiSelectValueSetter/Pim\\Component\\Catalog\\Updater\\Setter\\MultiSelectAttributeSetter/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\Updater\\Setter\\NumberValueSetter/Pim\\Component\\Catalog\\Updater\\Setter\\NumberAttributeSetter/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\Updater\\Setter\\PriceCollectionValueSetter/Pim\\Component\\Catalog\\Updater\\Setter\\PriceCollectionAttributeSetter/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\Updater\\Setter\\SimpleSelectValueSetter/Pim\\Component\\Catalog\\Updater\\Setter\\SimpleSelectAttributeSetter/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\Updater\\Setter\\TextValueSetter/Pim\\Component\\Catalog\\Updater\\Setter\\TextAttributeSetter/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\Updater\\Copier\\CopierInterface/Pim\\Component\\Catalog\\Updater\\Copier\\AttributeCopierInterface/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\Updater\\Copier\\AbstractValueCopier/Pim\\Component\\Catalog\\Updater\\Copier\\AbstractAttributeCopier/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\Updater\\Copier\\BaseValueCopier/Pim\\Component\\Catalog\\Updater\\Copier\\BaseAttributeCopier/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\Updater\\Copier\\MediaValueCopier/Pim\\Component\\Catalog\\Updater\\Copier\\MediaAttributeCopier/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\Updater\\Copier\\MetricValueCopier/Pim\\Component\\Catalog\\Updater\\Copier\\MetricAttributeCopier/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\Updater\\Copier\\MultiSelectValueCopier/Pim\\Component\\Catalog\\Updater\\Copier\\MultiSelectAttributeCopier/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\Updater\\Copier\\PriceCollectionValueCopier/Pim\\Component\\Catalog\\Updater\\Copier\\PriceCollectionAttributeCopier/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\Updater\\Copier\\SimpleSelectValueCopier/Pim\\Component\\Catalog\\Updater\\Copier\\SimpleSelectAttributeCopier/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\Updater\\ProductTemplateUpdaterInterface/Pim\\Component\\Catalog\\Updater\\ProductTemplateUpdaterInterface/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\Doctrine\\ORM\\Repository\\CategoryRepository/Pim\\Bundle\\ClassificationBundle\\Doctrine\\ORM\\Repository\\CategoryRepository/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\Repository\\CategoryRepositoryInterface/Pim\\Component\\Classification\\Repository\\CategoryRepositoryInterface/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\DataGridBundle\\Datagrid\\Product\\ConfiguratorInterface/Pim\\Bundle\\DataGridBundle\\Datagrid\\Configuration\\ConfiguratorInterface/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\DataGridBundle\\Datagrid\\Product\\ConfigurationRegistry/Pim\\Bundle\\DataGridBundle\\Datagrid\\Configuration\\Product\\ConfigurationRegistry/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\DataGridBundle\\Datagrid\\Product\\ContextConfigurator/Pim\\Bundle\\DataGridBundle\\Datagrid\\Configuration\\Product\\ContextConfigurator/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\DataGridBundle\\Datagrid\\Product\\FiltersConfigurator/Pim\\Bundle\\DataGridBundle\\Datagrid\\Configuration\\Product\\FiltersConfigurator/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\DataGridBundle\\Datagrid\\Product\\GroupColumnsConfigurator/Pim\\Bundle\\DataGridBundle\\Datagrid\\Configuration\\Product\\GroupColumnsConfigurator/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\DataGridBundle\\Datagrid\\Product\\SortersConfigurator/Pim\\Bundle\\DataGridBundle\\Datagrid\\Configuration\\Product\\SortersConfigurator/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\DataGridBundle\\Datagrid\\RequestParametersExtractor/Pim\\Bundle\\DataGridBundle\\Datagrid\\Request\\RequestParametersExtractor/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\DataGridBundle\\Datagrid\\RequestParametersExtractorInterface/Pim\Bundle\DataGridBundle\Datagrid\Request\RequestParametersExtractorInterface/g'
```
