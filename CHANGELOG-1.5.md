# 1.5.7 (2016-07-19)

## Bug fixes

- Fix published product value attribute class

# 1.5.6 (2016-07-06)

# 1.5.5 (2016-06-16)

## Bug fixes

- PIM-5825: Fix proposals page when an attribute related to a proposal is deleted

# 1.5.4 (2016-06-01)

## Bug fixes

- PIM-5796: Add ACL on revert button

# 1.5.3 (2016-05-13)

## Bug fixes

- PIM-5762: Fix category permission issues on datagrids

# 1.5.2 (2016-04-25)

## Bug fixes

- PIM-5695: Fix prices proposal when data is null
- PIM-5545: Fix flash message when a proposal is approved/rejected in the proposal grid

# 1.5.1 (2016-03-09)

# 1.5.0 (2016-03-08)

## BC breaks

- Change constructor of `Pim\Bundle\EnrichBundle\Normalizer\VersionNormalizer`. Add `Pim\Component\Catalog\Localization\Presenter\PresenterRegistryInterface`.
- Change constructor of `Pim\Bundle\TransformBundle\Denormalizer\Structured\ProductValue\MetricDenormalizer`. Add `Akeneo\Component\Localization\Localizer\LocalizerInterface`.
- Change constructor of `Pim\Bundle\TransformBundle\Denormalizer\Structured\ProductValue\PricesDenormalizer`. Add `Akeneo\Component\Localization\Localizer\LocalizerInterface`.
- Change constructor of `Pim\Bundle\TransformBundle\Normalizer\Structured\ProductValue\ProductValueNormalizer`. Add `Akeneo\Component\Localization\Localizer\LocalizerInterface`.
- Change constructor of `Pim\Bundle\TransformBundle\Normalizer\Flat\ProductValueNormalizer`. Add `Akeneo\Component\Localization\Localizer\LocalizerInterface`.

# 1.5.0-BETA1 (2016-02-22)

## Bug fixes

- PIM-5508: Variant group edition fix

## BC breaks

- Change constructor of `Pim\Bundle\EnrichBundle\Normalizer\VersionNormalizer`. Add `Pim\Component\Catalog\Localization\Presenter\PresenterRegistryInterface`.
- Change constructor of `Pim\Bundle\TransformBundle\Denormalizer\Structured\ProductValue\MetricDenormalizer`. Add `Akeneo\Component\Localization\Localizer\LocalizerInterface`.
- Change constructor of `Pim\Bundle\TransformBundle\Denormalizer\Structured\ProductValue\PricesDenormalizer`. Add `Akeneo\Component\Localization\Localizer\LocalizerInterface`.
- Change constructor of `Pim\Bundle\TransformBundle\Normalizer\Structured\ProductValue\ProductValueNormalizer`. Add `Akeneo\Component\Localization\Localizer\LocalizerInterface`.
- Change constructor of `Pim\Bundle\TransformBundle\Normalizer\Flat\ProductValueNormalizer`. Add `Akeneo\Component\Localization\Localizer\LocalizerInterface`.
- Change interface `PimEnterprise\Bundle\WorkflowBundle\Applier\ProductDraftApplierInterface` to change apply method to applyAllChanges and add applyToReviewChanges
- Change interface `PimEnterprise\Bundle\WorkflowBundle\Model\ProductDraftInterface` : change getChangeForAttribute method to getChange, change removeChangeForAttribute to removeChange, remove setStatus, add getChangesToReview, getReviewStatusForChange, setReviewStatusForChange, removeReviewStatusForChange, setAllReviewStatuses, areAllReviewStatusesTo, markAsInProgress and markAsReady
- Change constructor of `PimEnterprise\Bundle\DashboardBundle\Widget\ProposalWidget`. Add `Symfony\Component\Routing\RouterInterface`.
- Change constructor of `PimEnterprise\Bundle\DataGridBundle\Datagrid\Configuration\ProductDraft\GridHelper`. Add `PimEnterprise\Bundle\WorkflowBundle\Helper\ProductDraftChangesPermissionHelper`.
- Change constructor of `PimEnterprise\Bundle\DataGridBundle\Datagrid\Configuration\Proposal\GridHelper`. Add `PimEnterprise\Bundle\WorkflowBundle\Helper\ProductDraftChangesPermissionHelper`.
- Remove constructor of `PimEnterprise\Bundle\SecurityBundle\Voter\ProductDraftVoter` which now takes no argument at all.
- Change constructor of `PimEnterprise\Bundle\WorkflowBundle\Connector\Tasklet\AbstractReviewTasklet`. Add `PimEnterprise\Bundle\WorkflowBundle\Helper\ProductDraftChangesPermissionHelper`.
- Change constructor of `PimEnterprise\Bundle\WorkflowBundle\Controller\ProductDraftController`. Add `Pim\Bundle\CatalogBundle\Filter\CollectionFilterInterface`.
- Update `PimEnterprise\Bundle\WorkflowBundle\Controller\ProductDraftController`, remove method `approveAction` and `refuseAction` to a unique method `reviewAction`.
- Change constructor of `PimEnterprise\Bundle\WorkflowBundle\Controller\Rest\ProductDraftController`. Add `Pim\Bundle\CatalogBundle\Filter\CollectionFilterInterface`.
- Update `PimEnterprise\Bundle\WorkflowBundle\Controller\Rest\ProductDraftController`, remove method `approveAction` and `rejectAction` to a unique method `reviewAction`.
- Change constructor of `PimEnterprise\Bundle\WorkflowBundle\Manager\ProductDraftManager`. Add `Pim\Bundle\CatalogBundle\Filter\CollectionFilterInterface`.
- Change constructor of `Pim\Bundle\CommentBundle\Normalizer\Structured\CommentNormalizer` to add `Akeneo\Component\Localization\Presenter\PresenterInterface` and `Pim\Bundle\EnrichBundle\Resolver\LocaleResolver`
- Change constructor of `Pim\Bundle\EnrichBundle\Normalizer\VersionNormalizer` to add `Akeneo\Component\Localization\Presenter\PresenterInterface`
- Change constructor of `Pim\Bundle\DashboardBundle\Widget\LastOperationsWidget` to add `Akeneo\Component\Localization\Presenter\PresenterInterface` and `Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface`
- Removed `Pim\Bundle\EnrichBundle\AbstractController\AbstractDoctrineController` and `Pim\Bundle\EnrichBundle\AbstractController\AbstractController`.
- Change constructor of `Pim\Bundle\EnrichBundle\Filter\ProductEditDataFilter` to add `Pim\Bundle\CatalogBundle\Filter\CollectionFilterInterface` and to remove `Oro\Bundle\SecurityBundle\SecurityFacade`, `Pim\Bundle\CatalogBundle\Filter\ObjectFilterInterface`, `Pim\Component\Catalog\Repository\AttributeRepositoryInterface`, `Pim\Component\Catalog\Repository\LocaleRepositoryInterface` and `Pim\Component\Catalog\Repository\ChannelRepositoryInterface`
- Change constructor of `Pim\Bundle\EnrichBundle\MassEditAction\Operation\EditCommonAttributes` to add `Pim\Bundle\CatalogBundle\Filter\CollectionFilterInterface`
- Update `PimEnterprise\Bundle\WorkflowBundle\Manager\ProductDraftManager`. Renamed method `approveValue` to `approveChange` and `refuseValue` to `refuseChange`.
- Change constructor of `PimEnterprise\Bundle\WorkflowBundle\EventSubscriber\ProductDraft\AbstractProposalStateNotificationSubscriber`. Add `PimEnterprise\Bundle\WorkflowBundle\Repository\ProductDraftRepositoryInterface`.

# 1.5.0-ALPHA1 (2016-01-26)

## Technical improvements
- PIM-5450: MongoDb ODM bundle in dev requirements 

## Bug fixes

## BC breaks

- Change constructor of `PimEnterprise\Bundle\DashboardBundle\Widget\ProposalWidget`. Add `Akeneo\Component\Localization\Presenter\PresenterInterface`.
- Service `oro_filter.form.type.date_range` is removed and replaced by `pim_filter.form.type.date_range`
- Service `oro_filter.form.type.datetime_range` is removed and replaced by `pim_filter.form.type.datetime_range`
- Remove class `PimEnterprise\Bundle\EnrichBundle\Form\Type\AvailableAttributesType`
- Change constructor of `Pim\Bundle\CatalogBundle\Builder\ProductTemplateBuilder`. Add `Pim\Bundle\EnrichBundle\Resolver\LocaleResolver` as the fourth argument.
- Change constructor of `Pim\Bundle\CatalogRuleBundle\Controller\RuleController`. Kept only the repository and the remover as arguments.
- Change interface RuleDefinitionRepositoryInterface to add a createDatagridQueryBuilder method
- Change interface RuleDefinitionInterface to add a relation mapping to RuleRelation
- Move RuleRelation model and repository to the Akeneo\RuleBundle
- Change constructor of `Pim\Bundle\EnrichBundle\Controller\Rest\ProductController`. Add argument `Pim\Component\Catalog\Localization\Localizer\AttributeConverterInterface`.
- Change constructor of `Pim\Bundle\EnrichBundle\Form\Handler\GroupHandler`. Add argument `Pim\Component\Catalog\Localization\Localizer\AttributeConverterInterface`.
- Change constructor of `Pim\Bundle\EnrichBundle\Form\Subscriber\TransformProductTemplateValuesSubscriber`. Add argument `Pim\Bundle\EnrichBundle\Resolver\LocaleResolver`.
- Change constructor of `Pim\Bundle\UIBundle\Form\Type\NumberType`. Add arguments `Pim\Bundle\EnrichBundle\Resolver\LocaleResolver`, `Pim\Component\Localization\Localizer\LocalizerInterface`, `Akeneo\Component\Localization\Validator\Constraints\NumberFormatValidator` and `Akeneo\Component\Localization\Factory\NumberFactory`.
- Change constructor of `Pim\Bundle\UIBundle\Form\Type\DateType`. Add arguments `Pim\Bundle\EnrichBundle\Resolver\LocaleResolver` and `Akeneo\Component\Localization\Factory\DateFactory`.
- Rename service `pimee_product_asset.extension.formatter.property.product_value.product_asset_property` to `pimee_product_asset.datagrid.extension.formatter.property.product_value.product_asset_property`
- Column 'comment' has been added on the `pim_notification_notification` table.
- Columns 'proposalsToReviewNotification' and 'proposalsStateNotification' has been added on the `oro_user` table (only EE).
- PropertySetterInterface and PropertyCopierInterface were removed from the PimEnterprise\Bundle\CatalogRuleBundle\Engine\ProductRuleApplier\ProductsUpdater and replaced by Akeneo\Component\RuleEngine\ActionApplier\ActionApplierRegistryInterface
- Removed $actionClasses from the PimEnterprise\Bundle\CatalogRuleBundle\Denormalizer\ProductRule\ContentDenormalizer constructor
- Moved PimEnterprise\Bundle\CatalogRuleBundle\Validator\Constraints\ProductRule\ValueAction to PimEnterprise\Bundle\CatalogRuleBundle\Validator\Constraints\ProductRule\PropertyAction
- Change constructor of `PimEnterprise\Bundle\CatalogRuleBundle\Connector\Processor\Normalization\RuleDefinitionProcessor`. Removed argument `Pim\Bundle\CatalogBundle\Manager\LocaleManager` and add `Pim\Bundle\CatalogBundle\Repository\LocaleRepositoryInterface`.
- Change constructor of `PimEnterprise\Component\ProductAsset\Connector\Processor\Normalization\ChannelConfigurationProcessor`. Removed argument `Pim\Bundle\CatalogBundle\Manager\LocaleManager` and add `Pim\Bundle\CatalogBundle\Repository\LocaleRepositoryInterface`.
- Change constructor of `PimEnterprise\Component\ProductAsset\Connector\Processor\Normalization\VariationProcessor`. Removed argument `Pim\Bundle\CatalogBundle\Manager\LocaleManager` and add `Pim\Bundle\CatalogBundle\Repository\LocaleRepositoryInterface`..
- Change constructor of `PimEnterprise\Bundle\CatalogBundle\Filter\ProductValueLocaleRightFilter`. Removed argument `Pim\Bundle\CatalogBundle\Manager\LocaleManager` and add `Pim\Bundle\CatalogBundle\Repository\LocaleRepositoryInterface`.
- Change constructor of `PimEnterprise\Bundle\DataGridBundle\Extension\MassAction\Util\ProductFieldsBuilder`. Removed argument `Pim\Bundle\CatalogBundle\Manager\LocaleManager` and add `Pim\Bundle\CatalogBundle\Repository\LocaleRepositoryInterface`.
- Change constructor of `PimEnterprise\Component\ProductAsset\Upload\UploadChecker`. Removed argument `Pim\Bundle\CatalogBundle\Manager\LocaleManager` and add `Pim\Bundle\CatalogBundle\Repository\LocaleRepositoryInterface`.
- Change constructor of `PimEnterprise\Bundle\DataGridBundle\Datagrid\Configuration\Proposal\GridHelper`. Added argument `Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface`
- Changed constructor of `PimEnterprise\Bundle\EnrichBundle\Twig\AttributeExtension`, added AttributeRepositoryInterface as argument.
- Changed constructor of `PimEnterprise\Bundle\DataGridBundle\Datagrid\Configuration\Proposal\GridHelper`, added ProductDraftGrantedAttributeProvider and RequestStack as arguments.
- Interface `PimEnterprise\Bundle\WorkflowBundle\Model\ProductDraftInterface` have changed in order to add `hasChanges`, `getChangeForAttribute` and `removeChangeForAttribute` functions.
- Interface `PimEnterprise\Bundle\WorkflowBundle\Repository\ProductDraftRepositoryInterface` have changed in order to add method `findApprovableByUserAndProductId`.
- Added an argument to the `PimEnterprise\Bundle\WorkflowBundle\Controller\Rest\ProductDraftController` constructor.
- Change constructor of `PimEnterprise\Bundle\UserBundle\Form\Type`. Add argument `PimEnterprise\Bundle\UserBundle\Form\Subscriber\UserPreferencesSubscriber`.
- Remove ProductValue repository from container
- Remove ProductValue repository from the PimEnterprise\Bundle\WorkflowBundle\Twig\ProductDraftChangesExtension
- Change constructor of `PimEnterprise\Bundle\WorkflowBundle\Presenter\MetricPresenter`. Add `Pim\Component\Catalog\Localization\Presenter\MetricPresenter` and `Pim\Bundle\EnrichBundle\Resolver\LocaleResolver`.
- Change constructor of `PimEnterprise\Bundle\WorkflowBundle\Presenter\PricesPresenter`. Add `Pim\Component\Catalog\Localization\Presenter\PricesPresenter` and `Pim\Bundle\EnrichBundle\Resolver\LocaleResolver`.
- Change constructor of `PimEnterprise\Bundle\WorkflowBundle\Presenter\DatePresenter`. Add `Pim\Component\Catalog\Localization\Presenter\DatePresenter` and `Pim\Bundle\EnrichBundle\Resolver\LocaleResolver`.
- Change constructor of `PimEnterprise\Bundle\WorkflowBundle\Presenter\NumberPresenter`. Add `Pim\Component\Catalog\Localization\Presenter\NumberPresenter` and `Pim\Bundle\EnrichBundle\Resolver\LocaleResolver`.
- Change constructor of `PimEnterprise\Bundle\DataGridBundle\Extension\MassAction\Util\ProductFieldsBuilder` to inject ProductRepositoryInterface an AttributeRepositoryInterface
- Moved PimEnterprise\Bundle\CatalogRuleBundle\Connector\Processor\Denormalization\RuleDefinitionProcessor to PimEnterprise\Component\CatalogRule\Connector\Processor\Denormalization\RuleDefinitionProcessor
- Moved PimEnterprise\Bundle\CatalogRuleBundle\Connector\Processor\Normalization\RuleDefinitionProcessor to PimEnterprise\Component\CatalogRule\Connector\Processor\Normalization\RuleDefinitionProcessor
- Moved PimEnterprise\Bundle\CatalogRuleBundle\Connector\Writer\Doctrine\RuleDefinitionWriter to PimEnterprise\Component\CatalogRule\Connector\Writer\Doctrine\RuleDefinitionWriter
- Moved PimEnterprise\Bundle\CatalogRuleBundle\Connector\Writer\YamlFile\RuleDefinitionWriter to PimEnterprise\Component\CatalogRule\Connector\Writer\YamlFile\RuleDefinitionWriter
- Moved PimEnterprise\Bundle\CatalogRuleBundle\Denormalizer\ProductRule\ConditionDenormalizer to PimEnterprise\Component\CatalogRule\Denormalizer\ProductRule\ConditionDenormalizer
- Moved PimEnterprise\Bundle\CatalogRuleBundle\Denormalizer\ProductRule\ContentDenormalizer to PimEnterprise\Component\CatalogRule\Denormalizer\ProductRule\ContentDenormalizer
- Moved PimEnterprise\Bundle\CatalogRuleBundle\Engine\ProductRuleApplier to PimEnterprise\Component\CatalogRule\Engine\ProductRuleApplier
- Moved PimEnterprise\Bundle\CatalogRuleBundle\Engine\ProductRuleApplier\ProductsSaver to PimEnterprise\Component\CatalogRule\Engine\ProductRuleApplier\ProductsSaver
- Moved PimEnterprise\Bundle\CatalogRuleBundle\Engine\ProductRuleApplier\ProductsUpdater to PimEnterprise\Component\CatalogRule\Engine\ProductRuleApplier\ProductsUpdater
- Moved PimEnterprise\Bundle\CatalogRuleBundle\Engine\ProductRuleApplier\ProductsValidator to PimEnterprise\Component\CatalogRule\Engine\ProductRuleApplier\ProductsValidator
- Moved PimEnterprise\Bundle\CatalogRuleBundle\Engine\ProductRuleBuilder to PimEnterprise\Component\CatalogRule\Engine\ProductRuleBuilder
- Moved PimEnterprise\Bundle\CatalogRuleBundle\Engine\ProductRuleSelector to PimEnterprise\Component\CatalogRule\Engine\ProductRuleSelector
- Moved PimEnterprise\Bundle\CatalogRuleBundle\Model\FieldImpactActionInterface to PimEnterprise\Component\CatalogRule\Model\FieldImpactActionInterface
- Moved PimEnterprise\Bundle\CatalogRuleBundle\Model\ProductCondition to PimEnterprise\Component\CatalogRule\Model\ProductCondition
- Moved PimEnterprise\Bundle\CatalogRuleBundle\Model\ProductConditionInterface to PimEnterprise\Component\CatalogRule\Model\ProductConditionInterface
- Moved PimEnterprise\Bundle\CatalogRuleBundle\Model\RuleRelationInterface to PimEnterprise\Component\CatalogRule\Model\RuleRelationInterface
- Moved PimEnterprise\Bundle\CatalogRuleBundle\Model\RuleRelation to PimEnterprise\Component\CatalogRule\Model\RuleRelation
- Moved PimEnterprise\Bundle\CatalogRuleBundle\Repository\RuleRelationRepositoryInterface to PimEnterprise\Component\CatalogRule\Repository\RuleRelationRepositoryInterface
- Moved PimEnterprise\Bundle\CatalogRuleBundle\Runner\ProductRuleRunner to PimEnterprise\Component\CatalogRule\Runner\ProductRuleRunner
- Moved PimEnterprise\Bundle\CatalogRuleBundle\Validator\Constraints\ExistingAddField to PimEnterprise\Bundle\CatalogRuleBundle\Validator\Constraint\ExistingAddField
- Moved PimEnterprise\Bundle\CatalogRuleBundle\Validator\Constraints\ExistingCopyFields to PimEnterprise\Bundle\CatalogRuleBundle\Validator\Constraint\ExistingCopyFields
- Moved PimEnterprise\Bundle\CatalogRuleBundle\Validator\Constraints\ExistingField to PimEnterprise\Bundle\CatalogRuleBundle\Validator\Constraint\ExistingField
- Moved PimEnterprise\Bundle\CatalogRuleBundle\Validator\Constraints\ExistingFieldValidator to PimEnterprise\Component\CatalogRule\Validator\ExistingFieldValidator
- Moved PimEnterprise\Bundle\CatalogRuleBundle\Validator\Constraints\ExistingFilterField to PimEnterprise\Bundle\CatalogRuleBundle\Validator\Constraint\ExistingFilterField
- Moved PimEnterprise\Bundle\CatalogRuleBundle\Validator\Constraints\ExistingSetField to PimEnterprise\Bundle\CatalogRuleBundle\Validator\Constraint\ExistingSetField
- Moved PimEnterprise\Bundle\CatalogRuleBundle\Validator\Constraints\ProductRule\NonEmptyValueCondition to PimEnterprise\Bundle\CatalogRuleBundle\Validator\Constraint\NonEmptyValueCondition
- Moved PimEnterprise\Bundle\CatalogRuleBundle\Validator\Constraints\ProductRule\NonEmptyValueConditionValidator to PimEnterprise\Component\CatalogRule\Validator\NonEmptyValueConditionValidator
- Moved PimEnterprise\Bundle\CatalogRuleBundle\Validator\Constraints\ProductRule\PropertyAction to PimEnterprise\Bundle\CatalogRuleBundle\Validator\Constraint\PropertyAction
- Moved PimEnterprise\Bundle\CatalogRuleBundle\Validator\Constraints\ProductRule\PropertyActionValidator to PimEnterprise\Component\CatalogRule\Validator\PropertyActionValidator
- Moved PimEnterprise\Bundle\CatalogRuleBundle\Validator\Constraints\ProductRule\SupportedOperatorCondition to PimEnterprise\Bundle\CatalogRuleBundle\Validator\Constraint\SupportedOperatorCondition
- Moved PimEnterprise\Bundle\CatalogRuleBundle\Validator\Constraints\ProductRule\SupportedOperatorConditionValidator to PimEnterprise\Component\CatalogRule\Validator\SupportedOperatorConditionValidator
- Moved PimEnterprise\Bundle\CatalogRuleBundle\Validator\Constraints\ProductRule\ValueCondition to PimEnterprise\Bundle\CatalogRuleBundle\Validator\Constraint\ValueCondition
- Moved PimEnterprise\Bundle\CatalogRuleBundle\Validator\Constraints\ProductRule\ValueConditionValidator to PimEnterprise\Component\CatalogRule\Validator\ValueConditionValidator
- Update schema of `PimEnterprise\Bundle\WorkflowBundle\Model\PublishedProductMetric`. Increase precision of data and baseData.
- Change constructor of `PimEnterprise\Component\Workflow\Connector\Processor\Denormalization\ProductDraftProcessor`. Add argument `Pim\Component\Catalog\Localization\Localizer\AttributeConverterInterface`.
- Change constructor of `PimEnterprise\Bundle\WorkflowBundle\Controller\Rest\ProductDraftController`. Add argument `PimEnterprise\Bundle\UserBundle\Context\UserContext`.
