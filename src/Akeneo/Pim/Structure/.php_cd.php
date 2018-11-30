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

        // TODO: Functional problem to query products before removing AttributeOption
        'Akeneo\Pim\Enrichment\Bundle\Elasticsearch\ProductAndProductModelQueryBuilderFactory',

        // TODO: Functionnal problem we should not create empty associations
        'Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithAssociationsInterface',

        // TODO: Contextual information shouldn't be injected but a parameter method (current Locale for instance)
        'Akeneo\UserManagement\Bundle\Context\UserContext',

        // TODO: linked by reference instead of id + repo + ACL
        'Akeneo\Channel\Component\Model\ChannelInterface',

        // TODO form type inheritance/usage
        // TODO: The forms are probably not used anymore
        'Akeneo\Platform\Bundle\UIBundle\Form\Type\AsyncSelectType',
        'Akeneo\Platform\Bundle\UIBundle\Form\Type\LightEntityType',
        'Akeneo\Platform\Bundle\UIBundle\Provider\TranslatedLabelsProviderInterface',
        'Akeneo\Platform\Bundle\UIBundle\Form\Subscriber\DisableFieldSubscriber',
        'Akeneo\Platform\Bundle\UIBundle\Form\Type\TranslatableFieldType',

        // TODO: Used to filter in search/get action, enrichment shouldn't call something else than `/enrichment`
        'Akeneo\Pim\Enrichment\Bundle\Filter\CollectionFilterInterface',
        'Akeneo\Pim\Enrichment\Bundle\Filter\ObjectFilterInterface'
    ])->in('Akeneo\Pim\Structure\Bundle'),
    $builder->only([
        'Symfony\Component',
        'Doctrine\Common',
        'Akeneo\Tool\Component',

        // TODO a validator should not query the unit of work
        'Doctrine\ORM\EntityManagerInterface',
        // TODO \Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface expose the QueryBuilder
        'Doctrine\ORM\QueryBuilder',

        //TODO: Context integration through database (we ask data from another bounded context)
        //TODO: Link by reference instead of id
        'Akeneo\Channel\Component\Repository\LocaleRepositoryInterface',
        'Akeneo\Channel\Component\Repository\ChannelRepositoryInterface',
        'Akeneo\Channel\Component\Model\ChannelInterface',
        'Akeneo\Channel\Component\Model\LocaleInterface',

        //TODO: Functionnal problem -> used to check if we can remove a family / family variant
        'Akeneo\Pim\Enrichment\Component\Product\ProductAndProductModel\Query\CountProductsWithFamilyInterface',
        'Akeneo\Pim\Enrichment\Component\Product\ProductAndProductModel\Query\CountEntityWithFamilyVariantInterface',

        // Coupling issues:
        // TODO it should be duplicated
        'Akeneo\Pim\Enrichment\Component\Product\Connector\Processor\MassEdit\AbstractProcessor',

        // TODO: Functionnal problem we should not create empty associations
        'Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithAssociationsInterface',

        // TODO remove coupling
        'Akeneo\Tool\Bundle\VersioningBundle\Manager\VersionManager',

        // TODO: we should find another way to manage permission
        'Akeneo\Pim\Enrichment\Bundle\Filter\CollectionFilterInterface',
        'Akeneo\Pim\Enrichment\Bundle\Filter\ObjectFilterInterface',

        // TODO related to the front stuff
        'Akeneo\Platform\Bundle\UIBundle\Provider\EmptyValue\EmptyValueProviderInterface',
        'Akeneo\Platform\Bundle\UIBundle\Provider\Field\FieldProviderInterface',
        'Akeneo\Platform\Bundle\UIBundle\Provider\Filter\FilterProviderInterface',
        'Akeneo\Platform\Bundle\UIBundle\Provider\StructureVersion\StructureVersionProviderInterface',

        // TODO: Akeneo\Pim\Structure\Component\Validator\Constraints\ValidMetricValidator should be split into two validator
        'Akeneo\Pim\Enrichment\Component\Product\Model\MetricInterface',
        'Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface',
        'Akeneo\Pim\Enrichment\Component\Product\Value\MetricValueInterface',

        // TODO used for enrichment purpose in FamilyNormalizer check if it used for other purpose
        'Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\Product\ProductNormalizer',
        'Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\ProductAndProductModel',
        'Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\ProductModel',
    ])->in('Akeneo\Pim\Structure\Component'),
];

$config = new Configuration($rules, $finder);

return $config;
