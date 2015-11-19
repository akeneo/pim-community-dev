# 1.5.x

## Bug fixes

## BC breaks

- Change constructor of `Pim\Bundle\CatalogBundle\Builder\ProductTemplateBuilder`. Add `Pim\Component\Localization\LocaleResolver` as the fourth argument.
- Change constructor of `Pim\Bundle\EnrichBundle\Connector\Processor\MassEdit\Product\EditCommonAttributesProcessor`. Add argument `Pim\Component\Localization\Localizer\LocalizerRegistryInterface`.
- Change constructor of `Pim\Bundle\EnrichBundle\Controller\Rest\ProductController`. Add argument `Pim\Component\Localization\Localizer\LocalizedAttributeConverterInterface`.
- Change constructor of `Pim\Bundle\EnrichBundle\Form\Handler\GroupHandler`. Add argument `Pim\Component\Localization\Localizer\LocalizedAttributeConverterInterface`.
- Change constructor of `Pim\Bundle\EnrichBundle\Form\Subscriber\TransformProductTemplateValuesSubscriber`. Add argument `Pim\Component\Localization\LocaleResolver`.
- Change constructor of `Pim\Bundle\UIBundle\Form\Type\NumberType`. Add arguments `Pim\Component\Localization\LocaleResolver` and `Pim\Component\Localization\Localizer\LocalizerInterface`.
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
- Changed constructor of `PimEnterprise\Bundle\WorkflowBundle\Rendering\PhpDiffRenderer`, we now inject two renderer instead of one.
- Changed constructor of `PimEnterprise\Bundle\EnrichBundle\Twig\AttributeExtension`, added AttributeRepositoryInterface as argument.
- Changed constructor of `PimEnterprise\Bundle\DataGridBundle\Datagrid\Configuration\Proposal\GridHelper`, added ProductDraftGrantedAttributeProvider and RequestStack as arguments.
- Interface `PimEnterprise\Bundle\WorkflowBundle\Model\ProductDraftInterface` have changed in order to add `hasChanges`, `getChangeForAttribute` and `removeChangeForAttribute` functions.
- Interface `PimEnterprise\Bundle\WorkflowBundle\Repository\ProductDraftRepositoryInterface` have changed in order to add method `findApprovableByUserAndProductId`.
- Added an argument to the `PimEnterprise\Bundle\WorkflowBundle\Controller\Rest\ProductDraftController` constructor.
- Updated `PimEnterprise\Bundle\WorkflowBundle\Presenter\PresenterInterface`, removed present method, added presentOriginal and presentNew.
- Updated `PimEnterprise\Bundle\WorkflowBundle\Rendering\RendererInterface`, removed renderDiff method, added renderOriginalDiff and renderNewDiff.
- Replaced renderer `pimee_workflow.renderer.html.simple_list` by `pimee_workflow.renderer.html.base_only` and `pimee_workflow.renderer.html.changed_only`.
- Change constructor of `PimEnterprise\Bundle\UserBundle\Form\Type`. Add argument `PimEnterprise\Bundle\UserBundle\Form\Subscriber\UserPreferencesSubscriber`.
- Remove ProductValue repository from container
- Remove ProductValue repository from the PimEnterprise\Bundle\WorkflowBundle\Twig\ProductDraftChangesExtension
