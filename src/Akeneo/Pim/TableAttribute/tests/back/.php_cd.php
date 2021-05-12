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

            // symfony dependencies
            'Symfony\Component\DependencyInjection',
            'Symfony\Component\HttpKernel\Bundle\Bundle',
            'Symfony\Component\Config\FileLocator',
            'Symfony\Component\Validator',
            'Symfony\Component\Serializer',

            // doctrine
            'Doctrine\Common\EventSubscriber',
            'Doctrine\ORM\Event\LifecycleEventArgs',
            'Doctrine\ORM\Events',

            // pim dependencies
            'Akeneo\Pim\Structure\Component',
            'Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface',



        ]
    )->in('Akeneo\Pim\TableAttribute\Infrastructure'),
];

return new Configuration($rules, $finder);
