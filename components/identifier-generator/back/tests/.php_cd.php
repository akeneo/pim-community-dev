<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Automation\IdentifierGenerator;

use Akeneo\CouplingDetector\Configuration\Configuration;
use Akeneo\CouplingDetector\Configuration\DefaultFinder;
use Akeneo\CouplingDetector\RuleBuilder;

$finder = new DefaultFinder();
$finder->in('components/identifier-generator/back/src');
$builder = new RuleBuilder();

$rules = [
    // Domain layer should only use classes from itself and Assert
    $builder->only(
        [
            'Akeneo\Pim\Automation\IdentifierGenerator\Domain',
            'Webmozart\Assert\Assert'
        ]
    )->in('Akeneo\Pim\Automation\IdentifierGenerator\Domain'),

    $builder->only(
        [
            'Akeneo\Pim\Automation\IdentifierGenerator\Domain',
            'Akeneo\Pim\Automation\IdentifierGenerator\Application',
            'Webmozart\Assert\Assert'
        ]
    )->in('Akeneo\Pim\Automation\IdentifierGenerator\Application'),

    $builder->only(
        [
            'Akeneo\Pim\Automation\IdentifierGenerator\Domain',
            'Akeneo\Platform\Bundle\InstallerBundle\Event\InstallerEvents',

            'Akeneo\Pim\Structure\Component\AttributeTypes',
            'Akeneo\Pim\Structure\Component\Query\PublicApi\Attribute\FindFlattenAttributesInterface',
            'Akeneo\Pim\Structure\Component\Query\PublicApi\Attribute\FlattenAttribute',

            'Akeneo\UserManagement\Bundle\Context\UserContext',

            'Symfony\Component\Config\FileLocator',
            'Symfony\Component\DependencyInjection\ContainerBuilder',
            'Symfony\Component\DependencyInjection\Loader\YamlFileLoader',
            'Symfony\Component\HttpFoundation\JsonResponse',
            'Symfony\Component\HttpFoundation\RedirectResponse',
            'Symfony\Component\HttpFoundation\Request',
            'Symfony\Component\HttpFoundation\Response',
            'Ramsey\Uuid\Uuid',
            'Doctrine\DBAL\Exception',
            'Doctrine\DBAL\Connection',
            'Symfony\Component\HttpKernel\Bundle\Bundle',
            'Symfony\Component\HttpKernel\DependencyInjection\Extension',
            'Symfony\Component\EventDispatcher\EventSubscriberInterface',
        ]
    )->in('Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure'),
];

$config = new Configuration($rules, $finder);

return $config;
