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
            'Webmozart\Assert\Assert'
        ]
    )->in('Akeneo\Pim\Automation\IdentifierGenerator\Domain'),

    $builder->only(
        [
            'Akeneo\Pim\Automation\IdentifierGenerator\Domain',
            'Webmozart\Assert\Assert',

            // TODO CPM-756
            'Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Exception\ViolationsException',
            'Symfony\Component\Validator\Validator\ValidatorInterface'
        ]
    )->in('Akeneo\Pim\Automation\IdentifierGenerator\Application'),

    $builder->only(
        [
            'Akeneo\Pim\Automation\IdentifierGenerator\Domain',
            'Akeneo\Pim\Automation\IdentifierGenerator\Application',

            'Akeneo\Pim\Structure\Component\AttributeTypes',
            'Akeneo\Pim\Structure\Component\Query\PublicApi',
            'Akeneo\Platform\Bundle\FrameworkBundle\Security\SecurityFacadeInterface',
            'Akeneo\UserManagement\Bundle\Context\UserContext',

            'Symfony\Component\HttpKernel\Bundle\Bundle',
            'Symfony\Component\Config\FileLocator',
            'Symfony\Component\DependencyInjection\ContainerBuilder',
            'Symfony\Component\DependencyInjection\Loader\YamlFileLoader',
            'Symfony\Component\HttpKernel\DependencyInjection\Extension',
            'Symfony\Component\Validator\Constraint',
            'Symfony\Component\Validator\ConstraintViolationList',
            'Symfony\Component\Validator\ConstraintViolationListInterface',
            'Symfony\Component\Validator\Validator\ValidatorInterface',
            'Symfony\Component\HttpFoundation\JsonResponse',
            'Symfony\Component\HttpFoundation\RedirectResponse',
            'Symfony\Component\HttpFoundation\Request',
            'Symfony\Component\HttpFoundation\Response',
            'Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException',
            'Webmozart\Assert\Assert'
        ]
    )->in('Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure'),
];

$config = new Configuration($rules, $finder);

return $config;
