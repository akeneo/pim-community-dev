## Bug fixes

- PIM-9332: Bump resource's memory limits for flexibility environments
- PIM-9388: Fix product link rules for scopable/localizable asset collection attributes
- PIM-9389: Unfriendly page title in create rule page.
- PIM-9376: Duplicate button appears under variant products.
- PIM-9226: Fix error on channel deletion after migration from v3.2.
- Fixes memory leak when indexing product models with a lot of product models in the same family (see https://github.com/akeneo/pim-community-dev/pull/11742)
- PIM-9109: Fix SSO not working behind reverse proxy.
- PIM-9133: Fix product and product model save when the user has no permission on some attribute groups
- PIM-9149: Fix compare/translate on product 
- DAPI-947: The evaluation of the title formatting criterion should be apply only on text attributes that are localizable and are defined as main title
- PIM-9138: Rules import not working with asset manager
- PIM-9196: Allow the search on label and code on the rules grid
- PIM-9197: Fix the rule execution when attribute code is not in lower case
- PIM-9239: Fix proposal datagrid when there is a product model proposal with an empty value suggestion
- PIM-9202: Fix Asset Manager / Product link rules not working with multiple consumers
- PIM-9261: Fix API assets pagination
- PIM-9270: Fix assets family product-link-rule definition
- PIM-9295: Fix error when applying an "Add groups" action to a product model
- PIM-9309: Update mekras/php-speller dependency to fix Swedish spelling issues
- PIM-9316: Fix url encoding of media links in asset edit form
- PIM-9318: Add created_at & updated_at fields in RefEntity record table
- PIM-9334: Add error during rule import when a condition value contains null value
- PIM-9324: Fix cannot save product when simple reference entity linked to this product is deleted
- PIM-9243: Creation and update dates are not displayed on the asset page 
- PIM-9362: Fix missing "System information" translations for asset analytics
- PIM-9363: Fix API error 500 when import a picture with an incorrect extension
- PIM-9370: Fixes page freezing with a big number of attribute options
- PIM-9404: Fix incorrect cast of numeric attribute option codes
- PIM-9393: Add error message on job instance when permissions edit is empty
- PIM-9400: Fix asset linked products not refreshing when switching locale
- PIM-9412: Keep asset collection order when sort order is the same

## Improvements

- DAPI-834: Data quality - As Julia, when I'm overing the dashboard, I'd like to see the medium grade for a given column.
- DAPI-697: Data quality - As Julia, when I'm on the DQI page, I want to click the attributes that need improvements and land on the PEF.
- DAPI-830: Add more supported languages for data quality text checking
- DAPI-806: Improve criteria evaluations performance
- DAPI-943: Do not use prepare statement anymore
- DAPI-739: Add coefficients by criterion to the calculation of the axes rates
- DAPI-635: Add spellcheck on WYSIWG editors
- DAPI-798: Allow spelling suggestions after a title formatter check
- DAPI-895: As Julia, I'd like spell-check to be available for Norwegian
- DAPI-749: Improve Dashboard rates purge
- DAPI-863: Evaluate the applicability of the criterion title formatting as soon as possible
- RUL-20: Rule engine - As Julia, I would like to copy values from/to different attribute types
- RUL-49: Rule engine - As Peter, I would like to clear attribute values, associations, categories and groups
- RUL-77: Rule engine - As Peter, I would like to add labels to my rules
- CLOUD-1959: Use cloud-deployer 2.2 and terraform 0.12.25
- MET-207: Asset Manager - As Peter, I would like to manually re-execute naming conventions
- RUL-271: Rule engine - As Peter, I'd like to add a condition on a relative date for created/updated fields

## New features

- DAPI-854: Data quality - Variant products are also evaluated
- RUL-17: Rule engine - Add the concatenate action type to concatenate some attribute values into a single attribute value
- RUL-28: Rule engine - As Peter, I'd like to calculate attribute values
- AOB-277: Add an acl to allow a role member to view all job executions in last job execution grids, job tracker and last operations widget.
- RAC-54: Add a new type of associations: Association with quantity

## BC Breaks

- Change constructor of `Akeneo\Pim\Automation\RuleEngine\Component\Connector\Tasklet\ImpactedProductCountTasklet` to change last argument from `Akeneo\Tool\Component\StorageUtils\Detacher\BulkObjectDetacherInterface` to `Akeneo\Tool\Component\StorageUtils\Cache\EntityManagerClearerInterface`
- Change constructor of `Akeneo\Pim\Automation\RuleEngine\Component\ActionApplier\AdderActionApplier` to:
  - replace `Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface` by `Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes`
  - add `Symfony\Component\EventDispatcher\EventDispatcherInterface`
- Change constructor of `Akeneo\Pim\Automation\RuleEngine\Component\ActionApplier\CopierActionApplier` to: 
  - replace `Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface` by `Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes`
  - add `Symfony\Component\EventDispatcher\EventDispatcherInterface`
- Change constructor of `Akeneo\Pim\Automation\RuleEngine\Component\ActionApplier\SetterActionApplier` to:
  - replace `Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface` by `Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes`
  - add `Symfony\Component\EventDispatcher\EventDispatcherInterface`
- Change constructor of `Akeneo\Pim\Automation\RuleEngine\Component\ActionApplier\RemoverActionApplier` to:
  - replace `Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface` by `Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes`
  - add `Symfony\Component\EventDispatcher\EventDispatcherInterface`
- Change return type of `Akeneo\Tool\Component\RuleEngine\ActionApplier\ActionApplierInterface` from `void` to `array`
- Change return type of `Akeneo\Pim\Automation\RuleEngine\Component\Engine\ProductRuleApplier\ProductsUpdater` from `void` to `array`
- Add method `getType()` to `Akeneo\Tool\Bundle\RuleEngineBundle\Model\ActionInterface` interface
- Change constructor of `Akeneo\Pim\Automation\RuleEngine\Bundle\Twig\RuleExtension` to add `Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface`
- Remove class `Akeneo\Pim\Automation\RuleEngine\Bundle\Normalizer\RuleDefinitionNormalizer`
- Change constructor of `\Akeneo\Pim\Automation\RuleEngine\Component\Connector\Processor\Denormalization\RuleDefinitionProcessor` to
  - remove `Akeneo\Tool\Component\StorageUtils\Detacher\ObjectDetacherInterface`
  - add `Akeneo\Pim\Automation\RuleEngine\Component\Updater\RuleDefinitionUpdaterInterface`
- Change constructor of `Akeneo\Pim\WorkOrganization\Workflow\Bundle\Doctrine\Common\Saver\DelegatingProductSaver` to
  - remove `Symfony\Component\EventDispatcher\EventDispatcherInterface` and `Akeneo\Pim\Enrichment\Bundle\Doctrine\Common\Saver\ProductUniqueDataSynchronizer`
  - add `Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface` (twice) and `Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface`
