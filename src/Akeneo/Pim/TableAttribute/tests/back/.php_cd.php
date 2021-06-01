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
            'Symfony\Component\HttpKernel\Bundle\Bundle',
            'Symfony\Component\Config\FileLocator',
            'Symfony\Component\Validator',
            'Symfony\Component\Serializer',
            'Symfony\Component\EventDispatcher\EventSubscriberInterface',
            'Symfony\Component\EventDispatcher\GenericEvent',

            // doctrine
            'Doctrine\Common\EventSubscriber',
            'Doctrine\DBAL\Connection',
            'Doctrine\ORM\Event\LifecycleEventArgs',
            'Doctrine\ORM\Events',

            // pim dependencies
            'Akeneo\Pim\Structure\Component',
            'Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface',
            'Akeneo\Channel\Component\Query\PublicApi',
            'Akeneo\Platform\Bundle\InstallerBundle\Event',
            'Akeneo\Platform\Bundle\UIBundle\Provider\Field\FieldProviderInterface',
            'Akeneo\Pim\Enrichment\Component\Product\Factory\Value\ValueFactory',
            'Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface',
            'Akeneo\Pim\Enrichment\Component\Product\Model\AbstractValue',
            'Akeneo\Pim\Enrichment\Component\Product\Comparator\ComparatorInterface',
            'Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\Value\AbstractProductValueNormalizer',
            'Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\Value\ValueCollectionNormalizer',
        ]
    )->in('Akeneo\Pim\TableAttribute\Infrastructure'),
];

return new Configuration($rules, $finder);
