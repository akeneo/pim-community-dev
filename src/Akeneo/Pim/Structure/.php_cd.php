<?php

use Akeneo\CouplingDetector\Configuration\Configuration;
use Akeneo\CouplingDetector\Configuration\DefaultFinder;
use Akeneo\CouplingDetector\RuleBuilder;

$finder = new DefaultFinder();

$builder = new RuleBuilder();

$rules = [
    $builder->only([
        'Doctrine',
        'Symfony',
        'Sensio\Bundle\FrameworkExtraBundle',
        'Akeneo\Tool',
        'Akeneo\Pim\Structure\Component',
        'Oro\Bundle\SecurityBundle\Annotation\AclAncestor',
        'Oro\Bundle\SecurityBundle\SecurityFacade',
        'FOS\RestBundle\View',
        // Context integration through database (we ask data from another bounded context)
        'Akeneo\Pim\Enrichment\Bundle\Elasticsearch\ProductAndProductModelQueryBuilderFactory',

        // Coupling issues:
        'Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithAssociationsInterface', // TODO a repository method should be turned into a query function
        'Akeneo\UserManagement\Bundle\Context\UserContext', // TODO we need the current locale
        // TODO: linked by reference instead of id
        'Akeneo\Channel\Component\Model\ChannelInterface',
        // TODO form type inheritance/usage
        'Akeneo\Platform\Bundle\UIBundle\Form\Type\AsyncSelectType',
        'Akeneo\Platform\Bundle\UIBundle\Form\Type\LightEntityType',
        'Akeneo\Platform\Bundle\UIBundle\Provider\TranslatedLabelsProviderInterface',
        'Akeneo\Platform\Bundle\UIBundle\Form\Subscriber\DisableFieldSubscriber',
        'Akeneo\Platform\Bundle\UIBundle\Form\Type\TranslatableFieldType',
        // TODO: we should find another way to manage permission
        'Akeneo\Pim\Enrichment\Bundle\Filter\CollectionFilterInterface',
        'Akeneo\Pim\Enrichment\Bundle\Filter\ObjectFilterInterface'
    ])->in('Akeneo\Pim\Structure\Bundle'),
    $builder->only([
        'Symfony\Component',
        'Doctrine\Common',
        'Akeneo\Tool\Component',
        'Akeneo\Channel\Component\Repository\LocaleRepositoryInterface',
        'Akeneo\Channel\Component\Repository\ChannelRepositoryInterface',
        // Context integration through database (we ask data from another bounded context)
        'Akeneo\Pim\Enrichment\Component\Product\ProductAndProductModel\Query\CountProductsWithFamilyInterface',
        'Akeneo\Pim\Enrichment\Component\Product\ProductAndProductModel\Query\CountEntityWithFamilyVariantInterface',

        // Coupling issues:
        'Akeneo\Pim\Enrichment\Component\Product\Connector\Processor\MassEdit\AbstractProcessor', // TODO it should be duplicated
        'Doctrine\ORM\EntityManagerInterface', //TODO a validator need to access data from the unit of work
        'Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithAssociationsInterface', // TODO a repository method should be turned into a query function
        'Akeneo\Tool\Bundle\VersioningBundle\Manager\VersionManager', // TODO remove coupling
        'Doctrine\ORM\QueryBuilder', // TODO \Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface expose the QueryBuilder
        // TODO: linked by reference instead of id
        'Akeneo\Channel\Component\Model\ChannelInterface',
        'Akeneo\Channel\Component\Model\LocaleInterface',
        // TODO: we should find another way to manage permission
        'Akeneo\Pim\Enrichment\Bundle\Filter\CollectionFilterInterface',
        'Akeneo\Pim\Enrichment\Bundle\Filter\ObjectFilterInterface',
        // TODO related to the front stuff
        'Akeneo\Platform\Bundle\UIBundle\Provider\EmptyValue\EmptyValueProviderInterface',
        'Akeneo\Platform\Bundle\UIBundle\Provider\Field\FieldProviderInterface',
        'Akeneo\Platform\Bundle\UIBundle\Provider\Filter\FilterProviderInterface',
        'Akeneo\Platform\Bundle\UIBundle\Provider\StructureVersion\StructureVersionProviderInterface',
        // TODO \Akeneo\Pim\Structure\Component\Validator\Constraints\ValidMetricValidator should be split into two validator
        'Akeneo\Pim\Enrichment\Component\Product\Model\MetricInterface',
        'Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface',
        'Akeneo\Pim\Enrichment\Component\Product\Value\MetricValueInterface',
        // TODO we should not use public constant
        'Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\Product\ProductNormalizer',
        'Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\ProductAndProductModel',
        'Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\ProductModel',
    ])->in('Akeneo\Pim\Structure\Component'),
];

$config = new Configuration($rules, $finder);

return $config;
