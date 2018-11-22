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
        'Symfony\Component',
        'Akeneo\Tool',
        'Akeneo\Asset',
        'Akeneo\Pim\Structure\Component\ReferenceData\ConfigurationRegistryInterface', // Asset are reference data
        'Akeneo\Pim\Enrichment\Component\Product\Model\ReferenceDataInterface', // Asset are reference data
        'Akeneo\Pim\Enrichment\Component\Product\Repository\ReferenceDataRepositoryInterface', // Asset are reference data
        'Akeneo\Tool\Bundle\VersioningBundle\UpdateGuesser\UpdateGuesserInterface', // Asset need to be versioned
        'Akeneo\Channel\Component\Repository\LocaleRepositoryInterface',
        'Akeneo\Channel\Component\Repository\ChannelRepositoryInterface',
        'Akeneo\UserManagement\Bundle\Repository\UserRepositoryInterface',
        'Akeneo\Tool\Component\Connector',
        'Akeneo\Platform\Bundle\NotificationBundle', // TODO: you should find a way to push notifications to the platform instead of coupling asset to the platform
        'Akeneo\Platform\Component\CatalogVolumeMonitoring', // TODO: we should define where it should go and if CatalogVolumeMonitoring is a context
        'Akeneo\UserManagement\Bundle\Context\UserContext', // TODO: We should not dependend on this context
        'Gedmo\Exception\UnexpectedValueException', // TODO Remove it
        // TODO: We should use id instead of reference
        'Akeneo\Channel\Component\Model\LocaleInterface',
        'Akeneo\Channel\Component\Model\ChannelInterface',
        'Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface',
        'Akeneo\UserManagement\Component\Model\UserInterface',
        'Akeneo\Channel\Component\Model\ChannelInterface',
        // TODO: Do we need these dependencies?
        'Symfony\Bundle\FrameworkBundle',
        'Sensio\Bundle\FrameworkExtraBundle',
        // TODO: We should burn the Datagrid but this BC uses the datagrid.
        'Oro\Bundle\DataGridBundle',
        'Oro\Bundle\PimDataGridBundle',
        'Oro\Bundle\FilterBundle',
        'Oro\Bundle\PimFilterBundle',
        // TODO: we should rework permission to avoid this kind coupling (permissions are a sub part of PIM BC)
        'Akeneo\Pim\Permission\Bundle\Entity\Repository\CategoryAccessRepository',
        'Akeneo\Pim\Permission\Bundle\Persistence\ORM\Category\CategoryManager',
        'Akeneo\Pim\Permission\Bundle\User\UserContext',
        'Akeneo\Pim\Permission\Component\Attributes',
        'Oro\Bundle\SecurityBundle\Annotation\AclAncestor',
        'Akeneo\Pim\Permission\Bundle\Form\Type\CategoryPermissionsType',
        'Akeneo\Pim\Permission\Bundle\Form\Type\GroupsType',
        'Akeneo\Pim\Permission\Bundle\Form\EventListener\CategoryPermissionsSubscriber',
        'Akeneo\Pim\Permission\Component\Exception\ResourceAccessDeniedException',
        // TODO: we must not depend on PIM BC
        'Akeneo\Pim\Asset\Component\Completeness\CompletenessRemoverInterface',
        'Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators', // Should we move them in Akeneo\Tool?
        'Akeneo\Pim\Structure\Component\Model\AttributeInterface', // We should not use public constant
        'Akeneo\Pim\Enrichment\Bundle\Controller\Ui\FileController', // We should not use public constant
        'Pim\Bundle\EnrichBundle\Provider\Filter\FilterProviderInterface', // Related to the front end
        'Pim\Bundle\EnrichBundle\Provider\Field\FieldProviderInterface', // Related to the front end
        'Pim\Bundle\EnrichBundle\Provider\EmptyValue\EmptyValueProviderInterface', // Related to the front end
        'Akeneo\Platform\Bundle\UIBundle\Flash\Message', // Related to the front end
        'Akeneo\Pim\Structure\Component\AttributeType\AbstractAttributeType',
        'Pim\Bundle\EnrichBundle\Connector\Processor\AbstractProcessor', // it should go in Akeneo\Tool
        'Akeneo\Pim\Enrichment\Bundle\Form\Type\CategoryType', // Related to the front end (symfony form type)
        'Pim\Bundle\EnrichBundle\Form\Type\LightEntityType', // Related to the front end (symfony form type)
        'Pim\Bundle\EnrichBundle\Form\Type\EntityIdentifierType', // Related to the front end (symfony form type)
        'Akeneo\Platform\Bundle\UIBundle\Provider\TranslatedLabelsProviderInterface', // Related to the front end (used to build form type)
        'Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Indexer\ProductIndexer',
        // TODO: we must not depend on Platefom
        'Akeneo\Platform\Bundle\UIBundle\Controller\AjaxOptionController',
        'Akeneo\Platform\Bundle\UIBundle\Form\Type\SwitchType', // Related to the front end (symfony form type)
        'Akeneo\Platform\Bundle\UIBundle\Form\Type\AjaxEntityType', // Related to the front end (symfony form type)
        'Akeneo\Platform\Bundle\UIBundle\Form\Transformer\AjaxCreatableEntityTransformerFactory', // Related to the front end (symfony form type)
        'Akeneo\Platform\Bundle\UIBundle\Form\Type\DateType', // Related to the front end (symfony form type)
        'Akeneo\Platform\Bundle\UIBundle\Form\Type\EntityIdentifierType', // Related to the front end (symfony form type)
        'Akeneo\Platform\Bundle\UIBundle\Form\Type\LightEntityType', // Related to the front end (symfony form type)
        'Akeneo\Platform\Bundle\UIBundle\Provider\Field\FieldProviderInterface', // Related to the front end (symfony form type)
        'Akeneo\Platform\Bundle\UIBundle\Provider\EmptyValue\EmptyValueProviderInterface', // Related to the front end (symfony form type)
        'Akeneo\Platform\Bundle\UIBundle\Provider\Filter\FilterProviderInterface', // Related to the front end (symfony form type)
    ])->in('Akeneo\Asset\Bundle'),
    $builder->only([
        'Symfony\Component',
        'Akeneo\Tool\Component',
        'Doctrine\Common',
        'Akeneo\Asset\Component',
        // Asset are reference data
        'Pim\Component\ReferenceData\Model\ReferenceDataInterface',
        'Pim\Component\ReferenceData\Repository\ReferenceDataRepositoryInterface',
        'Akeneo\Pim\Enrichment\Component\Product\Model\ReferenceDataInterface',
        'Akeneo\Pim\Enrichment\Component\Product\Repository\ReferenceDataRepositoryInterface',
        'Akeneo\Pim\Enrichment\Component\Product\Value\ReferenceDataCollectionValue',
        'Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface',
        'Akeneo\Pim\Enrichment\Component\Product\Normalizer\InternalApi\ImageNormalizer',
        'Akeneo\Pim\Enrichment\Component\Product\Normalizer\InternalApi\FileNormalizer',
        'Akeneo\UserManagement\Component\Factory\DefaultProperty',
        // Todo: Remove pim dependencies
        'Doctrine\ORM\QueryBuilder', // TODO: some repository return QueryBuidler object.
        'Akeneo\Pim\Permission\Bundle\Manager\CategoryAccessManager', // TODO: security
        // TODO: we should not use public constant
        'Akeneo\Asset\Bundle\Event\AssetEvent',
        // TODO: We should use id instead of reference
        'Akeneo\Channel\Component\Model\LocaleInterface',
        'Akeneo\Channel\Component\Model\ChannelInterface',
        'Akeneo\UserManagement\Component\Model\UserInterface',
        // TODO: we should not repository from the other BC
        'Akeneo\Channel\Component\Repository\LocaleRepositoryInterface',
        'Akeneo\Channel\Component\Repository\ChannelRepositoryInterface',
        // TODO: we should rework permission to avoid this kind coupling (permissions are a sub part of PIM BC)
        'Oro\Bundle\SecurityBundle\SecurityFacade',
        // TODO: we must not depend on PIM BC
        'Akeneo\Pim\Enrichment\Component\Product\Converter\ConverterInterface',
    ])->in('Akeneo\Asset\Component'),
];

$config = new Configuration($rules, $finder);

return $config;
