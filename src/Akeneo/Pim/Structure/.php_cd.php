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

        // TIP-906: Functional problem to query products before removing AttributeOption
        'Akeneo\Pim\Enrichment\Bundle\Elasticsearch\ProductAndProductModelQueryBuilderFactory',

        // TIP-907: Functionnal problem we should not create empty associations
        'Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithAssociationsInterface',

        // TODO: Contextual information shouldn't be injected but a parameter method (current Locale for instance)
        'Akeneo\UserManagement\Bundle\Context\UserContext',

        // TIP-910: PIM/Structure should not be linked to Channel
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

        // TIP-911: ImmutableVariantAxesValidator should not be tied to Doctrine
        'Doctrine\ORM\EntityManagerInterface',

        // TIP-912: AttributeRepositoryInterface should be tied to Doctrine
        'Doctrine\ORM\QueryBuilder',

        // TIP-910: PIM/Structure should not be linked to Channel
        'Akeneo\Channel\Component\Repository\ChannelRepositoryInterface',
        'Akeneo\Channel\Component\Model\ChannelInterface',

        // TIP-908: Entities should not be linked directly to Locale for translation purposes
        // TIP-909: PIM/Structure should not be linked to Locale
        'Akeneo\Channel\Component\Repository\LocaleRepositoryInterface',
        'Akeneo\Channel\Component\Model\LocaleInterface',

        //TIP-906: Functionnal problem -> used to check if we can remove a family / family variant
        'Akeneo\Pim\Enrichment\Component\Product\ProductAndProductModel\Query\CountProductsWithFamilyInterface',
        'Akeneo\Pim\Enrichment\Component\Product\ProductAndProductModel\Query\CountEntityWithFamilyVariantInterface',

        // Coupling issues:
        // TODO it should be duplicated
        'Akeneo\Pim\Enrichment\Component\Product\Connector\Processor\MassEdit\AbstractProcessor',

        // TIP-907: Functionnal problem we should not create empty associations
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

        // TIP-914 FamilyNormalizer should not use PIM/Enrichment
        'Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\Product\ProductNormalizer',
        'Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\ProductAndProductModel',
        'Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\ProductModel',
    ])->in('Akeneo\Pim\Structure\Component'),
];

$config = new Configuration($rules, $finder);

return $config;
