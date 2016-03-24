# UPGRADE FROM 1.4 to 1.5

> Please perform a backup of your database before proceeding to the migration. You can use tools like  [mysqldump](http://dev.mysql.com/doc/refman/5.1/en/mysqldump.html) and [mongodump](http://docs.mongodb.org/manual/reference/program/mongodump/).

> Please perform a backup of your codebase if you don't use any VCS.

## Catalog Bundle & Component

We've extracted following classes and interfaces from the Catalog bundle to the Catalog component:
 - model interfaces and classes as ProductInterface
 - repository interfaces as ProductRepositoryInterface
 - builder interfaces as ProductBuilderInterface

In v1.4, we've re-worked the file storage system, the model `Pim\Component\Catalog\Model\ProductMediaInterface` is not used anymore, we now use `Akeneo\Component\FileStorage\Model\FileInfoInterface`.

In v1.5, we've removed the following deprecated classes, interfaces and services:
 - `Pim\Component\Catalog\Model\ProductMediaInterface`
 - `Pim\Component\Catalog\Model\AbstractProductMedia`
 - `Pim\Component\Catalog\Model\ProductMedia`
 - `Pim\Bundle\CatalogBundle\Factory\MediaFactory` and `@pim_catalog.factory.media`
 - `Pim\Bundle\CatalogBundle\MongoDB\Normalizer\MediaNormalizer`
 - `Pim\Bundle\TransformBundle\Normalizer\MongoDB\ProductMediaNormalizer`
 - `PimEnterprise\Bundle\WorkflowBundle\DependencyInjection\Compiler\RegisterProductValuePresentersPass`
 - `PimEnterprise\Bundle\WorkflowBundle\Presenter\ProductValue\BooleanPresenter`
 - `PimEnterprise\Bundle\WorkflowBundle\Presenter\ProductValue\DatePresenter`
 - `PimEnterprise\Bundle\WorkflowBundle\Presenter\ProductValue\FilePresenter`
 - `PimEnterprise\Bundle\WorkflowBundle\Presenter\ProductValue\ImagePresenter`
 - `PimEnterprise\Bundle\WorkflowBundle\Presenter\ProductValue\ProductValuePresenterInterface`
 - `PimEnterprise\Bundle\WorkflowBundle\Twig\ProductValuePresenterExtension`

We've also removed the following requirements from composer.json, you can do the same in your project:

```
    "knplabs/gaufrette": "0.1.9",
    "knplabs/knp-gaufrette-bundle": "0.1.7"
```

As usual, we provide upgrade commands (cf last chapter) to easily update projects migrating from 1.4 to 1.5.

Don't forget to change the app/config.yml if you did mapping overrides:

v1.4
```
akeneo_storage_utils:
    mapping_overrides:
        -
            original: Pim\Bundle\CatalogBundle\Model\ProductValue
            override: PimEnterprise\Bundle\CatalogBundle\Model\ProductValue
        -
            original: PimEnterprise\Bundle\CatalogBundle\Model\ProductValue
            override: AcmeEnterprise\Bundle\AppBundle\Model\ProductValue
```

v1.5
```
akeneo_storage_utils:
    mapping_overrides:
        -
            original: Pim\Component\Catalog\Model\ProductValue
            override: PimEnterprise\Bundle\CatalogBundle\Model\ProductValue
        -
            original: PimEnterprise\Bundle\CatalogBundle\Model\ProductValue
            override: AcmeEnterprise\Bundle\AppBundle\Model\ProductValue
```

## Update dependencies and configuration

Extract the latest **PIM enterprise standard** you have received by mail:

```
 tar -zxf pim-enterprise-standard.tar.gz
 cd pim-enterprise-standard/
```

Copy the following files to your PIM installation:

```
 export PIM_DIR=/path/to/your/pim/installation
 cp app/PimRequirements.php $PIM_DIR/app
 cp app/SymfonyRequirements.php $PIM_DIR/app
 cp app/config/pim_parameters.yml $PIM_DIR/app/config
 cp composer.json $PIM_DIR
```

**In case your products are stored in Mongo**, don't forget to re-add the mongo dependencies to your *composer.json*:

```
 "doctrine/mongodb-odm-bundle": "3.0.1"
```

The mongodb-odm-bundle has been upgraded in the v1.5. Don't use anymore :

```
 "doctrine/mongodb-odm": "v1.0.0-beta12@dev",
 "doctrine/mongodb-odm-bundle": "v3.0.0-BETA6@dev"
```

And don't forget to add your own dependencies to your *composer.json* in case you have some.

Merge the following files into your PIM installation:
 - *app/AppKernel.php*: We added the Pim *Localization bundle*. We merged some Oro Platform bundles in our structure. The easiest way to merge is to copy the PIM-1.5 *AppKernel.php* file into your installation (`cp app/AppKernel.php $PIM_DIR/app/`), and then register your custom bundles. Don't forget to register *DoctrineMongoDBBundle* in case your products are stored with *MongoDB*.
 - *app/config/routing.yml*: we have added the entries *pim_localization* and merged some entry from Oro bundles. The easiest way to merge is copy the PIM-1.5 *routing.yml* file into your installation (`cp app/config/routing.yml $PIM_DIR/app/config/`), and then register your custom routes.
 - *app/config/config.yml*: the entry *pim_catalog* has been added. The easiest way to merge is copy the PIM-1.5 *config.yml* file into your installation (`cp app/config/config.yml $PIM_DIR/app/config/`), and then register your own bundles' configuration.

Then remove your old upgrades folder:
```
 rm upgrades/ -rf
```

Now you're ready to update your dependencies:

```
 cd $PIM_DIR
 composer update
```

This step will also copy the upgrades folder from `vendor/akeneo/pim-community-dev/` to your Pim project root to allow you to migrate.

Then you can migrate your database using:

```
 php app/console doctrine:migration:migrate
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
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\Model\\LocalizableInterface/Akeneo\\Component\\Localization\\Model\\LocalizableInterface/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Bundle\\CatalogBundle\\Model\\Metric/Component\\Catalog\\Model\\Metric/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Bundle\\CatalogBundle\\Model\\MetricInterface/Component\\Catalog\\Model\\MetricInterface/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Bundle\\CatalogBundle\\Model\\Product/Component\\Catalog\\Model\\Product/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Bundle\\CatalogBundle\\Model\\ProductInterface/Component\\Catalog\\Model\\ProductInterface/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Bundle\\CatalogBundle\\Model\\ProductPrice/Component\\Catalog\\Model\\ProductPrice/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Bundle\\CatalogBundle\\Model\\ProductPriceInterface/Component\\Catalog\\Model\\ProductPriceInterface/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Bundle\\CatalogBundle\\Model\\ProductTemplateInterface/Component\\Catalog\\Model\\ProductTemplateInterface/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\Model\\ProductValue/Pim\\Component\\Catalog\\Model\\ProductValue/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\Model\\ProductValueInterface/Pim\\Component\\Catalog\\Model\\ProductValueInterface/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Bundle\\CatalogBundle\\Model\\ReferableInterface/Component\\Catalog\\Model\\ReferableInterface/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Bundle\\CatalogBundle\\Model\\ScopableInterface/Component\\Catalog\\Model\\ScopableInterface/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Bundle\\CatalogBundle\\Model\\TimestampableInterface/Component\\Catalog\\Model\\TimestampableInterface/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Bundle\\CatalogBundle\\Model\\AvailableAttributes/Component\\Enrich\\Model\\AvailableAttributes/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Bundle\\CatalogBundle\\Model\\ChosableInterface/Component\\Enrich\\Model\\ChosableInterface/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Bundle\\ConnectorBundle\\Writer\\File\\ContextableCsvWriter/Bundle\\BaseConnectorBundle\\Writer\\File\\ContextableCsvWriter/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\Builder\\ProductBuilderInterface/Pim\\Component\\Catalog\\Builder\\ProductBuilderInterface/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\Util\\ProductValueKeyGenerator/Pim\\Component\\Catalog\\Model\\ProductValueKeyGenerator/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\Repository\\AttributeRepositoryInterface/Pim\\Component\\Catalog\\Repository\\AttributeRepositoryInterface/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\Repository\\LocaleRepositoryInterface/Pim\\Component\\Catalog\\Repository\\LocaleRepositoryInterface/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\Repository\\ChannelRepositoryInterface/Pim\\Component\\Catalog\\Repository\\ChannelRepositoryInterface/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\Exception\\InvalidArgumentException/Pim\\Component\\Catalog\\Exception\\InvalidArgumentException/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\Exception\\MissingIdentifierException/Pim\\Component\\Catalog\\Exception\\MissingIdentifierException/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\VersioningBundle\\Model\\VersionableInterface/Akeneo\\Component\\Versioning\\Model\\VersionableInterface/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\VersioningBundle\\Model\\Version/Akeneo\\Component\\Versioning\\Model\\Version/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Akeneo\\Bundle\\BatchBundle\\Item\\ItemReaderInterface/Akeneo\\Component\\Batch\\Item\\ItemReaderInterface/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Akeneo\\Bundle\\BatchBundle\\Item\\ItemProcessorInterface/Akeneo\\Component\\Batch\\Item\\ItemProcessorInterface/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Akeneo\\Bundle\\BatchBundle\\Item\\ItemWriterInterface/Akeneo\\Component\\Batch\\Item\\ItemWriterInterface/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Akeneo\\Bundle\\BatchBundle\\Item\\InvalidItemException/Akeneo\\Component\\Batch\\Item\\InvalidItemException/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Akeneo\\Bundle\\BatchBundle\\Item\\AbstractConfigurableStepElement/Akeneo\\Component\\Batch\\Item\\AbstractConfigurableStepElement/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Akeneo\\Bundle\\BatchBundle\\Item\\ExecutionContext/Akeneo\\Component\\Batch\\Item\\ExecutionContext/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Akeneo\\Bundle\\BatchBundle\\Step\\StepInterface/Akeneo\\Component\\Batch\\Step\\StepInterface/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Akeneo\\Bundle\\BatchBundle\\Step\\AbstractStep/Akeneo\\Component\\Batch\\Step\\AbstractStep/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Akeneo\\Bundle\\BatchBundle\\Step\\StepExecutionAwareInterface/Akeneo\\Component\\Batch\\Step\\StepExecutionAwareInterface/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Akeneo\\Bundle\\BatchBundle\\Step\\ItemStep/Akeneo\\Component\\Batch\\Step\\ItemStep/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Akeneo\\Bundle\\BatchBundle\\Event\\EventInterface/Akeneo\\Component\\Batch\\Event\\EventInterface/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Akeneo\\Bundle\\BatchBundle\\Event\\InvalidItemEvent/Akeneo\\Component\\Batch\\Event\\InvalidItemEvent/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Akeneo\\Bundle\\BatchBundle\\Event\\JobExecutionEvent/Akeneo\\Component\\Batch\\Event\\JobExecutionEvent/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Akeneo\\Bundle\\BatchBundle\\Event\\StepExecutionEvent/Akeneo\\Component\\Batch\\Event\\StepExecutionEvent/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Akeneo\\Bundle\\BatchBundle\\Job\\JobRepositoryInterface/Akeneo\\Component\\Batch\\Job\\JobRepositoryInterface/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Akeneo\\Bundle\\BatchBundle\\Job\\JobInterruptedException/Akeneo\\Component\\Batch\\Job\\JobInterruptedException/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Akeneo\\Bundle\\BatchBundle\\Job\\ExitStatus/Akeneo\\Component\\Batch\\Job\\ExitStatus/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Akeneo\\Bundle\\BatchBundle\\Job\\BatchStatus/Akeneo\\Component\\Batch\\Job\\BatchStatus/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Akeneo\\Bundle\\BatchBundle\\Job\\JobInterface/Akeneo\\Component\\Batch\\Job\\JobInterface/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Akeneo\\Bundle\\BatchBundle\\Job\\Job/Akeneo\\Component\\Batch\\Job\\Job/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Akeneo\\Bundle\\BatchBundle\\Job\\RuntimeErrorException/Akeneo\\Component\\Batch\\Job\\RuntimeErrorException/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Akeneo\\Bundle\\BatchBundle\\Entity\\JobInstance/Akeneo\\Component\\Batch\\Model\\JobInstance/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Akeneo\\Bundle\\BatchBundle\\Entity\\Warning/Akeneo\\Component\\Batch\\Model\\Warning/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Akeneo\\Bundle\\BatchBundle\\Entity\\StepExecution/Akeneo\\Component\\Batch\\Model\\StepExecution/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Akeneo\\Bundle\\BatchBundle\\Entity\\JobExecution/Akeneo\\Component\\Batch\\Model\\JobExecution/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Oro\\Bundle\\FilterBundle\\Form\\Type\\DateRangeType/Pim\\Bundle\\FilterBundle\\Form\\Type\\DateRangeType/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Oro\\Bundle\\FilterBundle\\Form\\Type\\DateTimeRangeType/Pim\\Bundle\\FilterBundle\\Form\\Type\\DateTimeRangeType/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Oro\\Bundle\\LocaleBundle\\DoctrineExtensions\\DBAL\\Types\\UTCDateTimeType/Akeneo\\Bundle\\StorageUtilsBundle\\Doctrine\\DBAL\\Types\\UTCDateTimeType/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/oro_filter.form.type.datetime_range/pim_filter.form.type.datetime_range/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/oro_filter.form.type.date_range/pim_filter.form.type.date_range/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\TranslationBundle\\Entity\\TranslatableInterface/Akeneo\\Component\\Localization\\Model\\TranslatableInterface/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\TranslationBundle\\Entity\\AbstractTranslation/Akeneo\\Component\\Localization\\Model\\AbstractTranslation/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\TranslationBundle\\Factory\\TranslationFactory/Akeneo\\Component\\Localization\\Factory\\TranslationFactory/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\TranslationBundle\\EventListener\\AddLocaleListener/Pim\\Bundle\\EnrichBundle\\EventListener\\AddLocaleListener/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\TranslationBundle\\Exception\\MissingOptionException/Pim\\Bundle\\EnrichBundle\\Exception\\MissingOptionException/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\TranslationBundle\\Form\\Subscriber\\AddTranslatableFieldSubscriber/Pim\\Bundle\\EnrichBundle\\Form\\Subscriber\\AddTranslatableFieldSubscriber/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\TranslationBundle\\Twig\\TranslationsExtension/Pim\\Bundle\\EnrichBundle\\Twig\\TranslationsExtension/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\TranslationBundle\\Form\\Type\\TranslatableFieldType/Pim\\Bundle\\EnrichBundle\\Form\\Type\\TranslatableFieldType/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\MongoDB\\Normalizer\\AttributeOptionNormalizer/Pim\\Bundle\\CatalogBundle\\MongoDB\\Normalizer\\NormalizedData\\AttributeOptionNormalizer/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\MongoDB\\Normalizer\\CompletenessNormalizer/Pim\\Bundle\\CatalogBundle\\MongoDB\\Normalizer\\NormalizedData\\CompletenessNormalizer/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\MongoDB\\Normalizer\\DateTimeNormalizer/Pim\\Bundle\\CatalogBundle\\MongoDB\\Normalizer\\NormalizedData\\DateTimeNormalizer/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\MongoDB\\Normalizer\\FamilyNormalizer/Pim\\Bundle\\CatalogBundle\\MongoDB\\Normalizer\\NormalizedData\\FamilyNormalizer/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\MongoDB\\Normalizer\\FileNormalizer/Pim\\Bundle\\CatalogBundle\\MongoDB\\Normalizer\\NormalizedData\\FileNormalizer/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\MongoDB\\Normalizer\\GroupNormalizer/Pim\\Bundle\\CatalogBundle\\MongoDB\\Normalizer\\NormalizedData\\GroupNormalizer/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\MongoDB\\Normalizer\\MetricNormalizer/Pim\\Bundle\\CatalogBundle\\MongoDB\\Normalizer\\NormalizedData\\MetricNormalizer/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\MongoDB\\Normalizer\\ProductNormalizer/Pim\\Bundle\\CatalogBundle\\MongoDB\\Normalizer\\NormalizedData\\ProductNormalizer/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\MongoDB\\Normalizer\\ProductPriceNormalizer/Pim\\Bundle\\CatalogBundle\\MongoDB\\Normalizer\\NormalizedData\\ProductPriceNormalizer/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\CatalogBundle\\MongoDB\\Normalizer\\ProductValueNormalizer/Pim\\Bundle\\CatalogBundle\\MongoDB\\Normalizer\\NormalizedData\\ProductValueNormalizer/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\ReferenceDataBundle\\MongoDB\\Normalizer\\ReferenceDataNormalizer/Pim\\Bundle\\ReferenceDataBundle\\MongoDB\\Normalizer\\NormalizedData\\ReferenceDataNormalizer/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\TransformBundle\\Normalizer\\MongoDB\\AssociationNormalizer/Pim\\Bundle\\CatalogBundle\\MongoDB\\Normalizer\\Document\\AssociationNormalizer/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\TransformBundle\\Normalizer\\MongoDB\\DateTimeNormalizer/Pim\\Bundle\\CatalogBundle\\MongoDB\\Normalizer\\Document\\DateTimeNormalizer/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\TransformBundle\\Normalizer\\MongoDB\\GenericNormalizer/Pim\\Bundle\\CatalogBundle\\MongoDB\\Normalizer\\Document\\GenericNormalizer/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\TransformBundle\\Normalizer\\MongoDB\\MetricNormalizer/Pim\\Bundle\\CatalogBundle\\MongoDB\\Normalizer\\Document\\MetricNormalizer/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\TransformBundle\\Normalizer\\MongoDB\\ProductNormalizer/Pim\\Bundle\\CatalogBundle\\MongoDB\\Normalizer\\Document\\ProductNormalizer/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\TransformBundle\\Normalizer\\MongoDB\\ProductPriceNormalizer/Pim\\Bundle\\CatalogBundle\\MongoDB\\Normalizer\\Document\\ProductPriceNormalizer/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\TransformBundle\\Normalizer\\MongoDB\\ProductValueNormalizer/Pim\\Bundle\\CatalogBundle\\MongoDB\\Normalizer\\Document\\ProductValueNormalizer/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\TransformBundle\\Normalizer\\MongoDB\\VersionNormalizer/Pim\\Bundle\\CatalogBundle\\MongoDB\\Normalizer\\Document\\VersionNormalizer/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\ReferenceDataBundle\\Normalizer\\MongoDB\\ReferenceDataNormalizer/Pim\\Bundle\\ReferenceDataBundle\\MongoDB\\Normalizer\\Document\\ReferenceDataNormalizer/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/pim_catalog.mongodb.normalizer./pim_catalog.mongodb.normalizer.normalized_data./g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/pim_serializer.normalizer.mongodb./pim_catalog.mongodb.normalizer.document./g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/pim_catalog.entity.available_attributes.class/pim_enrich.entity.available_attributes.class/g'
```

## Product rule import rework

### Functionnal changes

In 1.5 we introduce a new `add` action type. This new action allows the user to add collections of items into another collection. For example with this new action you can add multiple categories to a product or add multiple options to a multi select attribute. The import syntax is as follow:

    rule_sku_jacket:
        priority: 10
        conditions:
            - field:    sku
              operator: =
              value:    my-jacket
        actions:
            - type:  add
              field: weather_conditions
              data:
                - dry
                - hot
          - type:  add
            field: categories
            data:
              - tshirts

As you can see, this is the first rule action to be able to manipulate both product values (multi select attributes) and product fields (categories). To avoid aving multiple type of rules actions with different capabilities we decided to introduce two more rule actions: `set` and `copy`. These actions are exact copies of the former `set_value` and `copy_value` actions and can manipulate both fields (enabled, families, etc) and values (sku, name, description, etc).

For backward compatibility issues we kept `set_value` and `copy_value` and you can still use them but they are deprecated and we planned to remove them in the future.

The rule import format for `set` and `copy` is exactly the same as for `set_value` and `copy_value` so the easiest way to migrate your old rules to the new format is to export them within Akeneo PIM with our standard connector, change the action type and reimport them.

### Internal changes

We decided to clean this bundle and respect our new architecture organisation. To migrate your custom action or rule engine customization, you can run the following commands at the root folder of your project (make sure that you use a versioning system before doing so).

    find ./src -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\CatalogRuleBundle\\Connector\\Processor\\Denormalization\\RuleDefinitionProcessor/PimEnterprise\\Component\\CatalogRule\\Connector\\Processor\\Denormalization\\RuleDefinitionProcessor/g'
    find ./src -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\CatalogRuleBundle\\Connector\\Processor\\Normalization\\RuleDefinitionProcessor/PimEnterprise\\Component\\CatalogRule\\Connector\\Processor\\Normalization\\RuleDefinitionProcessor/g'
    find ./src -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\CatalogRuleBundle\\Connector\\Writer\\Doctrine\\RuleDefinitionWriter/PimEnterprise\\Component\\CatalogRule\\Connector\\Writer\\Doctrine\\RuleDefinitionWriter/g'
    find ./src -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\CatalogRuleBundle\\Connector\\Writer\\YamlFile\\RuleDefinitionWriter/PimEnterprise\\Component\\CatalogRule\\Connector\\Writer\\YamlFile\\RuleDefinitionWriter/g'
    find ./src -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\CatalogRuleBundle\\Denormalizer\\ProductRule\\ConditionDenormalizer/PimEnterprise\\Component\\CatalogRule\\Denormalizer\\ProductRule\\ConditionDenormalizer/g'
    find ./src -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\CatalogRuleBundle\\Denormalizer\\ProductRule\\ContentDenormalizer/PimEnterprise\\Component\\CatalogRule\\Denormalizer\\ProductRule\\ContentDenormalizer/g'
    find ./src -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\CatalogRuleBundle\\Engine\\ProductRuleApplier/PimEnterprise\\Component\\CatalogRule\\Engine\\ProductRuleApplier/g'
    find ./src -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\CatalogRuleBundle\\Engine\\ProductRuleApplier\\ProductsSaver/PimEnterprise\\Component\\CatalogRule\\Engine\\ProductRuleApplier\\ProductsSaver/g'
    find ./src -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\CatalogRuleBundle\\Engine\\ProductRuleApplier\\ProductsUpdater/PimEnterprise\\Component\\CatalogRule\\Engine\\ProductRuleApplier\\ProductsUpdater/g'
    find ./src -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\CatalogRuleBundle\\Engine\\ProductRuleApplier\\ProductsValidator/PimEnterprise\\Component\\CatalogRule\\Engine\\ProductRuleApplier\\ProductsValidator/g'
    find ./src -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\CatalogRuleBundle\\Engine\\ProductRuleBuilder/PimEnterprise\\Component\\CatalogRule\\Engine\\ProductRuleBuilder/g'
    find ./src -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\CatalogRuleBundle\\Engine\\ProductRuleSelector/PimEnterprise\\Component\\CatalogRule\\Engine\\ProductRuleSelector/g'
    find ./src -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\CatalogRuleBundle\\Model\\FieldImpactActionInterface/PimEnterprise\\Component\\CatalogRule\\Model\\FieldImpactActionInterface/g'
    find ./src -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\CatalogRuleBundle\\Model\\ProductCondition/PimEnterprise\\Component\\CatalogRule\\Model\\ProductCondition/g'
    find ./src -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\CatalogRuleBundle\\Model\\ProductConditionInterface/PimEnterprise\\Component\\CatalogRule\\Model\\ProductConditionInterface/g'
    find ./src -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\CatalogRuleBundle\\Model\\RuleRelationInterface/PimEnterprise\\Component\\CatalogRule\\Model\\RuleRelationInterface/g'
    find ./src -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\CatalogRuleBundle\\Model\\RuleRelation/PimEnterprise\\Component\\CatalogRule\\Model\\RuleRelation/g'
    find ./src -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\CatalogRuleBundle\\Repository\\RuleRelationRepositoryInterface/PimEnterprise\\Component\\CatalogRule\\Repository\\RuleRelationRepositoryInterface/g'
    find ./src -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\CatalogRuleBundle\\Runner\\ProductRuleRunner/PimEnterprise\\Component\\CatalogRule\\Runner\\ProductRuleRunner/g'
    find ./src -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\CatalogRuleBundle\\Validator\\Constraints\\ExistingAddField/PimEnterprise\\Bundle\\CatalogRuleBundle\\Validator\\Constraint\\ExistingAddField/g'
    find ./src -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\CatalogRuleBundle\\Validator\\Constraints\\ExistingCopyFields/PimEnterprise\\Bundle\\CatalogRuleBundle\\Validator\\Constraint\\ExistingCopyFields/g'
    find ./src -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\CatalogRuleBundle\\Validator\\Constraints\\ExistingField/PimEnterprise\\Bundle\\CatalogRuleBundle\\Validator\\Constraint\\ExistingField/g'
    find ./src -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\CatalogRuleBundle\\Validator\\Constraints\\ExistingFieldValidator/PimEnterprise\\Component\\CatalogRule\\Validator\\ExistingFieldValidator/g'
    find ./src -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\CatalogRuleBundle\\Validator\\Constraints\\ExistingFilterField/PimEnterprise\\Bundle\\CatalogRuleBundle\\Validator\\Constraint\\ExistingFilterField/g'
    find ./src -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\CatalogRuleBundle\\Validator\\Constraints\\ExistingSetField/PimEnterprise\\Bundle\\CatalogRuleBundle\\Validator\\Constraint\\ExistingSetField/g'
    find ./src -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\CatalogRuleBundle\\Validator\\Constraints\\ProductRule\\NonEmptyValueCondition/PimEnterprise\\Bundle\\CatalogRuleBundle\\Validator\\Constraint\\NonEmptyValueCondition/g'
    find ./src -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\CatalogRuleBundle\\Validator\\Constraints\\ProductRule\\NonEmptyValueConditionValidator/PimEnterprise\\Component\\CatalogRule\\Validator\\NonEmptyValueConditionValidator/g'
    find ./src -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\CatalogRuleBundle\\Validator\\Constraints\\ProductRule\\PropertyAction/PimEnterprise\\Bundle\\CatalogRuleBundle\\Validator\\Constraint\\PropertyAction/g'
    find ./src -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\CatalogRuleBundle\\Validator\\Constraints\\ProductRule\\PropertyActionValidator/PimEnterprise\\Component\\CatalogRule\\Validator\\PropertyActionValidator/g'
    find ./src -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\CatalogRuleBundle\\Validator\\Constraints\\ProductRule\\SupportedOperatorCondition/PimEnterprise\\Bundle\\CatalogRuleBundle\\Validator\\Constraint\\SupportedOperatorCondition/g'
    find ./src -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\CatalogRuleBundle\\Validator\\Constraints\\ProductRule\\SupportedOperatorConditionValidator/PimEnterprise\\Component\\CatalogRule\\Validator\\SupportedOperatorConditionValidator/g'
    find ./src -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\CatalogRuleBundle\\Validator\\Constraints\\ProductRule\\ValueCondition/PimEnterprise\\Bundle\\CatalogRuleBundle\\Validator\\Constraint\\ValueCondition/g'
    find ./src -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\CatalogRuleBundle\\Validator\\Constraints\\ProductRule\\ValueConditionValidator/PimEnterprise\\Component\\CatalogRule\\Validator\\ValueConditionValidator/g'
    find ./src -type f -print0 | xargs -0 sed -i 's/Oro\\Bundle\\FilterBundle\\Form\\Type\\DateRangeType/Pim\\Bundle\\FilterBundle\\Form\\Type\\DateRangeType/g'
    find ./src -type f -print0 | xargs -0 sed -i 's/Oro\\Bundle\\FilterBundle\\Form\\Type\\DateTimeRangeType/Pim\\Bundle\\FilterBundle\\Form\\Type\\DateTimeRangeType/g'
    find ./src -type f -print0 | xargs -0 sed -i 's/Oro\\Bundle\\LocaleBundle\\DoctrineExtensions\\DBAL\\Types\\UTCDateTimeType/Akeneo\\Bundle\\StorageUtilsBundle\\Doctrine\\DBAL\\Types\\UTCDateTimeType/g'
    find ./src -type f -print0 | xargs -0 sed -i 's/oro_filter.form.type.datetime_range/pim_filter.form.type.datetime_range/g'
    find ./src -type f -print0 | xargs -0 sed -i 's/oro_filter.form.type.date_range/pim_filter.form.type.date_range/g'

## EnrichBundle

In v1.5, we've removed the following deprecated classes and services:

 - `Pim\Bundle\EnrichBundle\AbstractController\AbstractDoctrineController`
 - `Pim\Bundle\EnrichBundle\AbstractController\AbstractController`
