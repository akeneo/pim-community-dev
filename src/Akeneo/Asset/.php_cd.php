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
        'Pim\Component\ReferenceData\ConfigurationRegistryInterface', // Asset are reference data
        'Pim\Bundle\VersioningBundle\UpdateGuesser\UpdateGuesserInterface', // Asset need to be versioned
        'Akeneo\Channel\Component\Repository\LocaleRepositoryInterface',
        'Akeneo\Channel\Component\Repository\ChannelRepositoryInterface',
        'Akeneo\UserManagement\Bundle\Repository\UserRepositoryInterface',
        'Pim\Bundle\NotificationBundle', // TODO: this should be moved in Akeneo\Tool
        'Pim\Component\Connector', // TODO: Generic classes/interfaces should be moved to Akeneo/Tool
        'Pim\Component\CatalogVolumeMonitoring', // TODO: we should define where it should go and if CatalogVolumeMonitoring is a context
        'Akeneo\UserManagement\Bundle\Context\UserContext', // TODO: We should not dependend on this context
        // TODO: We should use id instead of reference
        'Akeneo\Channel\Component\Model\LocaleInterface',
        'Akeneo\Channel\Component\Model\ChannelInterface',
        'Pim\Component\Catalog\Model\ProductInterface',
        'Akeneo\UserManagement\Component\Model\UserInterface',
        'Akeneo\Channel\Component\Model\ChannelInterface',
        // TODO: Do we need these dependencies?
        'Symfony\Bundle\FrameworkBundle',
        'Sensio\Bundle\FrameworkExtraBundle',
        // TODO: We should burn the Datagrid but this BC uses the datagrid.
        'Oro\Bundle\DataGridBundle',
        'Pim\Bundle\DataGridBundle',
        'Oro\Bundle\FilterBundle',
        'Pim\Bundle\FilterBundle',
        // TODO: we should rework permission to avoid this kind coupling (permissions are a sub part of PIM BC)
        'PimEnterprise\Bundle\SecurityBundle\Entity\Repository\CategoryAccessRepository',
        'PimEnterprise\Bundle\SecurityBundle\Persistence\ORM\Category\CategoryManager',
        'PimEnterprise\Bundle\SecurityBundle\User\UserContext',
        'PimEnterprise\Component\Security\Attributes',
        'Oro\Bundle\SecurityBundle\Annotation\AclAncestor',
        'PimEnterprise\Bundle\SecurityBundle\Form\Type\CategoryPermissionsType',
        'PimEnterprise\Bundle\SecurityBundle\Form\Type\GroupsType',
        'PimEnterprise\Bundle\SecurityBundle\Form\EventListener\CategoryPermissionsSubscriber',
        'PimEnterprise\Component\Security\Exception\ResourceAccessDeniedException',
        // TODO: we must not depend on PIM BC
        'Pim\Component\Catalog\Query\Filter\Operators', // Should we move them in Akeneo\Tool?
        'Akeneo\Pim\Structure\Component\Model\AttributeInterface', // We should not use public constant
        'Pim\Bundle\EnrichBundle\Controller\FileController', // We should not use public constant
        'Pim\Bundle\EnrichBundle\Provider\Filter\FilterProviderInterface', // Related to the front end
        'Pim\Bundle\EnrichBundle\Provider\Field\FieldProviderInterface', // Related to the front end
        'Pim\Bundle\EnrichBundle\Provider\EmptyValue\EmptyValueProviderInterface', // Related to the front end
        'Pim\Bundle\EnrichBundle\Flash\Message', // Related to the front end
        'Pim\Bundle\CatalogBundle\AttributeType\AbstractAttributeType',
        'Pim\Bundle\EnrichBundle\Connector\Processor\AbstractProcessor', // it should go in Akeneo\Tool
        'Pim\Bundle\UIBundle\Form\Type\SwitchType', // Related to the front end (symfony form type)
        'Pim\Bundle\UIBundle\Form\Type\AjaxEntityType', // Related to the front end (symfony form type)
        'Pim\Bundle\UIBundle\Form\Transformer\AjaxCreatableEntityTransformerFactory', // Related to the front end (symfony form type)
        'Pim\Bundle\EnrichBundle\Form\Type\CategoryType', // Related to the front end (symfony form type)
        'Pim\Bundle\EnrichBundle\Form\Type\LightEntityType', // Related to the front end (symfony form type)
        'Pim\Bundle\EnrichBundle\Form\Type\EntityIdentifierType', // Related to the front end (symfony form type)
        'Pim\Bundle\UIBundle\Form\Type\DateType', // Related to the front end (symfony form type)
        'Pim\Component\Enrich\Provider\TranslatedLabelsProviderInterface', // Related to the front end (used to build form type)
        'Pim\Bundle\CatalogBundle\Elasticsearch\Indexer\ProductIndexer',
        'Pim\Component\Catalog\Query\ProductQueryBuilderFactoryInterface',
        'Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface',
    ])->in('Akeneo\Asset\Bundle'),
    $builder->only([
        'Symfony\Component',
        'Akeneo\Tool\Component',
        'Doctrine\Common',
        'Akeneo\Asset\Component',
        // Asset are reference data
        'Pim\Component\ReferenceData\Model\ReferenceDataInterface',
        'Pim\Component\ReferenceData\Repository\ReferenceDataRepositoryInterface',
        'Pim\Component\Connector', // TODO: Generic classes/interfaces like be moved to Akeneo/Tool
        'Pim\Component\Catalog\Completeness\Checker\ValueCompleteCheckerInterface', // TODO: AssetCollectionCompleteChecker should extract from this context
        'Doctrine\ORM\QueryBuilder', // TODO: some repository return QueryBuidler object.
        'PimEnterprise\Bundle\SecurityBundle\Manager\CategoryAccessManager', // TODO: security
        // TODO: we should not use public constant
        'Akeneo\Asset\Bundle\Event\AssetEvent',
        'Akeneo\Asset\Bundle\AttributeType\AttributeTypes',
        // TODO: We should use id instead of reference
        'Akeneo\Channel\Component\Model\LocaleInterface',
        'Akeneo\Channel\Component\Model\ChannelInterface',
        'Pim\Component\Catalog\Model\ValueInterface',
        'Akeneo\UserManagement\Component\Model\UserInterface',
        // TODO: we should not repository from the other BC
        'Akeneo\Channel\Component\Repository\LocaleRepositoryInterface',
        'Akeneo\Channel\Component\Repository\ChannelRepositoryInterface',
    ])->in('Akeneo\Asset\Component'),
];

$config = new Configuration($rules, $finder);

return $config;
