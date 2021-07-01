<?php

use Akeneo\CouplingDetector\Configuration\Configuration;
use Akeneo\CouplingDetector\Configuration\DefaultFinder;
use Akeneo\CouplingDetector\RuleBuilder;

$finder = new DefaultFinder();
$builder = new RuleBuilder();

$rules = [
    $builder->only(
        [
            'Webmozart\Assert',
        ]
    )->in('Akeneo\Pim\TableAttribute\Domain'),
    $builder->only(
        [
            'Webmozart\Assert',
            'Akeneo\Pim\TableAttribute\Domain',
            'Ramsey\Uuid\Uuid',

            // symfony dependencies
            'Symfony\Component\DependencyInjection',
            'Symfony\Component\HttpKernel',
            'Symfony\Component\Config\FileLocator',
            'Symfony\Component\Validator',
            'Symfony\Component\Serializer',
            'Symfony\Component\EventDispatcher\EventSubscriberInterface',
            'Symfony\Component\EventDispatcher\GenericEvent',
            'Symfony\Component\HttpFoundation',
            'Symfony\Component\Security\Core\Exception\AccessDeniedException',

            // doctrine
            'Doctrine\Common\EventSubscriber',
            'Doctrine\DBAL\Connection',
            'Doctrine\DBAL\FetchMode',
            'Doctrine\ORM\Event\LifecycleEventArgs',
            'Doctrine\ORM\Events',

            // pim dependencies
            'Akeneo\Pim\Structure\Component',
            'Akeneo\Tool\Component\StorageUtils\Cache',
            'Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface',
            'Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException',
            'Akeneo\Tool\Component\Connector\Exception',
            'Akeneo\Tool\Component\Connector\ArrayConverter\ArrayConverterInterface',
            'Akeneo\Channel\Component\Query\PublicApi',
            'Akeneo\Platform\Bundle\InstallerBundle\Event',
            'Akeneo\Platform\Bundle\UIBundle\Provider\Field\FieldProviderInterface',
            'Akeneo\Pim\Enrichment\Component\Product\Factory\Value\ValueFactory',
            'Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface',
            'Akeneo\Pim\Enrichment\Component\Product\Model\AbstractValue',
            'Akeneo\Pim\Enrichment\Component\Product\Comparator\ComparatorInterface',
            'Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\Value\AbstractProductValueNormalizer',
            'Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\Value\ValueCollectionNormalizer',
            'Akeneo\Pim\Enrichment\Component\Product\Completeness\MaskItemGenerator\MaskItemGeneratorForAttributeType',
            'Akeneo\Pim\Enrichment\Component\Product\Validator\ConstraintGuesserInterface',
            'Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\StandardToFlat\Product\ValueConverter\AbstractValueConverter',
            'Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\ValueConverter\ValueConverterInterface',
            'Oro\Bundle\SecurityBundle\SecurityFacade',
        ]
    )->in('Akeneo\Pim\TableAttribute\Infrastructure'),
];

return new Configuration($rules, $finder);
