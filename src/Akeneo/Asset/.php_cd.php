<?php

use Akeneo\CouplingDetector\Configuration\Configuration;
use Akeneo\CouplingDetector\Configuration\DefaultFinder;
use Akeneo\CouplingDetector\RuleBuilder;

$finder = new DefaultFinder();
$finder->notPath('spec');
$finder->notPath('tests');

$builder = new RuleBuilder();

$rules = [
    $builder->only([
        'Doctrine',
        'Oro\Bundle\SecurityBundle\SecurityFacade',
        'Symfony\Component',
        'Akeneo\Tool',
        'Akeneo\Asset',
        'Akeneo\Tool\Bundle\VersioningBundle\UpdateGuesser\UpdateGuesserInterface', // Asset need to be versioned
        'Akeneo\UserManagement\Bundle\Repository\UserRepositoryInterface',
        'Akeneo\Tool\Component\Connector',
        'Symfony\Bundle\FrameworkBundle',
        'Sensio\Bundle\FrameworkExtraBundle',
        'Oro\Bundle\SecurityBundle\Annotation\AclAncestor',

        // TIP-949: Assets should not be Reference Data
        'Akeneo\Pim\Structure\Component\ReferenceData\ConfigurationRegistryInterface',
        'Akeneo\Pim\Enrichment\Component\Product\Model\ReferenceDataInterface',
        'Akeneo\Pim\Enrichment\Component\Product\Repository\ReferenceDataRepositoryInterface',

        // TIP-950: Asset should not be linked to Locale
        'Akeneo\Channel\Component\Model\LocaleInterface',
        'Akeneo\Channel\Component\Repository\LocaleRepositoryInterface',

        // TIP-951: Asset should not be linked to Channel
        'Akeneo\Channel\Component\Model\ChannelInterface',
        'Akeneo\Channel\Component\Repository\ChannelRepositoryInterface',

        // TIP-952: Asset should not be linked to User
        'Akeneo\UserManagement\Component\Model\UserInterface',

        // TIP-953: Compute product completeness from an Asset event
        'Akeneo\Pim\Enrichment\Asset\Component\Completeness\CompletenessRemoverInterface',

        // TIP-954: Move AssetCollectionType
        'Akeneo\Pim\Structure\Component\AttributeType\AbstractAttributeType',

        // TIP-955: Asset should not depend on Enrichment types
        'Akeneo\Pim\Enrichment\Bundle\Form\Type\CategoryType',

        // TIP-1013: Rework Notification system
        'Akeneo\Platform\Bundle\NotificationBundle',

        // TIP-1014: Do not use custom Flash Messages
        'Akeneo\Platform\Bundle\UIBundle\Flash\Message',

        // TIP-1016: Rework/Move CatalogVolumeMonitoring
        'Akeneo\Platform\Component\CatalogVolumeMonitoring',

        // TIP-1024: Drop UserContext
        'Akeneo\UserManagement\Bundle\Context\UserContext',

        // TIP-1028: Asset should not depend on Gedmo
        'Gedmo\Exception\UnexpectedValueException',

        // TIP-950: Asset should not be linked to Locale
        'Akeneo\Channel\Component\Model\LocaleInterface',

        // TIP-951: Asset should not be linked to Channel
        'Akeneo\Channel\Component\Model\ChannelInterface',

        // TIP-952: Asset should not be linked to User
        'Akeneo\UserManagement\Component\Model\UserInterface',

        // TODO: We should burn the Datagrid but this BC uses the datagrid.
        'Oro\Bundle\DataGridBundle',
        'Oro\Bundle\PimDataGridBundle',
        'Oro\Bundle\FilterBundle',
        'Oro\Bundle\PimFilterBundle',
        // TODO: we should rework permission to avoid this kind coupling (permissions are a sub part of PIM BC)
        'Akeneo\Pim\Permission\Bundle\Entity\Repository\CategoryAccessRepository',
        'Akeneo\Pim\Permission\Bundle\Persistence\ORM\Category\CategoryManager',
        'Akeneo\Pim\Permission\Component\Attributes',
        'Akeneo\Pim\Permission\Bundle\Form\Type\CategoryPermissionsType',
        'Akeneo\Pim\Permission\Bundle\Form\Type\GroupsType',
        'Akeneo\Pim\Permission\Bundle\Form\EventListener\CategoryPermissionsSubscriber',
        'Akeneo\Pim\Permission\Component\Exception\ResourceAccessDeniedException',

        // TIP-1024: Drop UserContext
        'Akeneo\Pim\Permission\Bundle\User\UserContext',

        // TIP-963: Define the Products public API
        'Akeneo\Pim\Enrichment\Component\Product\Completeness\CompletenessGeneratorInterface',
        'Akeneo\Pim\Enrichment\Asset\Component\Completeness\CompletenessRemoverInterface',
        'Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators', // Should we move them in Akeneo\Tool?
        'Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface',

        'Akeneo\Pim\Enrichment\Bundle\Form\Type\CategoryType', // Related to the front end (symfony form type)
        'Akeneo\Pim\Enrichment\Bundle\Controller\Ui\FileController', // We should not use public constant

        // TIP-1008: Clean Provider system of Platform
        'Pim\Bundle\EnrichBundle\Provider\Filter\FilterProviderInterface', // Related to the front end
        'Pim\Bundle\EnrichBundle\Provider\Field\FieldProviderInterface', // Related to the front end
        'Pim\Bundle\EnrichBundle\Provider\EmptyValue\EmptyValueProviderInterface', // Related to the front end

        // TODO: We should not rely structure
        'Akeneo\Pim\Structure\Component\Model\AttributeInterface', // We should not use public constant
        'Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface',

        // TIP-1005: Clean UI form types
        'Akeneo\Platform\Bundle\UIBundle\Form\Type\SwitchType', // Related to the front end (symfony form type)
        'Akeneo\Platform\Bundle\UIBundle\Form\Type\AjaxEntityType', // Related to the front end (symfony form type)
        'Akeneo\Platform\Bundle\UIBundle\Form\Transformer\AjaxCreatableEntityTransformerFactory', // Related to the front end (symfony form type)
        'Akeneo\Platform\Bundle\UIBundle\Form\Type\DateType', // Related to the front end (symfony form type)
        'Akeneo\Platform\Bundle\UIBundle\Form\Type\EntityIdentifierType', // Related to the front end (symfony form type)
        'Akeneo\Platform\Bundle\UIBundle\Form\Type\LightEntityType', // Related to the front end (symfony form type)
        'Akeneo\Platform\Bundle\UIBundle\Controller\AjaxOptionController',
        'Akeneo\Platform\Bundle\UIBundle\Controller\AjaxOptionController',

        // TIP-1008: Clean Provider system of Platform
        'Akeneo\Platform\Bundle\UIBundle\Provider\Field\FieldProviderInterface', // Related to the front end (symfony form type)
        'Akeneo\Platform\Bundle\UIBundle\Provider\EmptyValue\EmptyValueProviderInterface', // Related to the front end (symfony form type)
        'Akeneo\Platform\Bundle\UIBundle\Provider\Filter\FilterProviderInterface', // Related to the front end (symfony form type)
    ])->in('Akeneo\Asset\Bundle'),
    $builder->only([
        'Symfony\Component',
        'Akeneo\Tool\Component',
        'Doctrine\Common',
        'Akeneo\Asset\Component',

        // TIP-949: Assets should not be Reference Data
        'Akeneo\Pim\Enrichment\Component\Product\Model\ReferenceDataInterface',
        'Akeneo\Pim\Enrichment\Component\Product\Repository\ReferenceDataRepositoryInterface',
        'Akeneo\Pim\Enrichment\Component\Product\Value\ReferenceDataCollectionValue',

        // TIP-950: Asset should not be linked to Locale
        'Akeneo\Channel\Component\Model\LocaleInterface',
        'Akeneo\Channel\Component\Repository\LocaleRepositoryInterface',

        // TIP-951: Asset should not be linked to Channel
        'Akeneo\Channel\Component\Model\ChannelInterface',
        'Akeneo\Channel\Component\Repository\ChannelRepositoryInterface',

        // TIP-952: Asset should not be linked to User
        'Akeneo\UserManagement\Component\Model\UserInterface',
        'Akeneo\UserManagement\Component\Factory\DefaultProperty',

        // TIP-956: Move ImageNormalizer to Enrichment
        'Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface',
        'Akeneo\Pim\Enrichment\Component\Product\Normalizer\InternalApi\ImageNormalizer',
        'Akeneo\Pim\Enrichment\Component\Product\Normalizer\InternalApi\FileNormalizer',
        'Akeneo\Pim\Enrichment\Component\Product\Repository\ReferenceDataRepositoryResolverInterface',

        // TIP-957: Move Events to component
        'Akeneo\Asset\Bundle\Event\AssetEvent',

        // TODO: some repository return QueryBuidler object (datagrid)
        'Doctrine\ORM\QueryBuilder',

        // TIP-1029: Assets should not be Enrichment
        'Akeneo\Pim\Enrichment\Component\Product\Converter\ConverterInterface',
        'Oro\Bundle\SecurityBundle\SecurityFacade', // Used in ConverterInterface


        // TIP-999: Move managers to component
        'Akeneo\Pim\Permission\Bundle\Manager\CategoryAccessManager',

    ])->in('Akeneo\Asset\Component'),
];

$config = new Configuration($rules, $finder);

return $config;
