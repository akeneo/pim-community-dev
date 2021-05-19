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

            // doctrine
            'Doctrine\Common\EventSubscriber',
            'Doctrine\ORM\Event\LifecycleEventArgs',
            'Doctrine\ORM\Events',
            'Doctrine\DBAL\Connection',

            // pim dependencies
            'Akeneo\Pim\Structure\Component',
            'Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface',
            'Akeneo\Platform\Bundle\InstallerBundle\Event\InstallerEvent',
            'Akeneo\Platform\Bundle\InstallerBundle\Event\InstallerEvents',
            'Akeneo\Channel\Component\Query\PublicApi\ChannelExistsWithLocaleInterface',
        ]
    )->in('Akeneo\Pim\TableAttribute\Infrastructure'),
];

return new Configuration($rules, $finder);
