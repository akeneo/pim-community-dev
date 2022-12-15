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
            'Webmozart\Assert\Assert',
        ]
    )->in('Akeneo\Pim\Automation\IdentifierGenerator\Domain'),

    $builder->only(
        [
            'Akeneo\Pim\Automation\IdentifierGenerator\Domain',
            'Webmozart\Assert\Assert',
        ]
    )->in('Akeneo\Pim\Automation\IdentifierGenerator\Application'),

    $builder->only(
        [
            'Akeneo\Pim\Automation\IdentifierGenerator\API',
            'Akeneo\Pim\Automation\IdentifierGenerator\Domain',
            'Akeneo\Pim\Automation\IdentifierGenerator\Application',
            'Akeneo\Pim\Structure\Family\ServiceAPI',
            'Akeneo\Pim\Structure\Component\Query\PublicApi',

            'Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface',
            'Akeneo\Pim\Enrichment\Component\Product\Value\ScalarValue',
            'Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\Product\UniqueProductEntity',
            'Akeneo\Pim\Structure\Component\AttributeTypes',

            'Akeneo\Platform\Bundle\InstallerBundle\Event\InstallerEvents',
            'Akeneo\UserManagement\Bundle\Context\UserContext',
            'Akeneo\Tool\Component\Batch\Event\EventInterface',
            'Akeneo\Tool\Component\Batch\Event\StepExecutionEvent',
            'Akeneo\Tool\Component\Batch\Job\JobRepositoryInterface',
            'Akeneo\Tool\Component\Batch\Model\Warning',
            'Akeneo\Tool\Component\StorageUtils\StorageEvents',

            'Symfony\Component\Config\FileLocator',
            'Symfony\Component\DependencyInjection\ContainerBuilder',
            'Symfony\Component\DependencyInjection\Loader\YamlFileLoader',
            'Symfony\Component\EventDispatcher\GenericEvent',
            'Symfony\Component\HttpKernel\Bundle\Bundle',
            'Symfony\Component\HttpKernel\DependencyInjection\Extension',
            'Symfony\Component\HttpKernel\Exception\BadRequestHttpException',
            'Symfony\Component\HttpKernel\Exception\NotFoundHttpException',
            'Symfony\Component\EventDispatcher\EventSubscriberInterface',
            'Symfony\Component\EventDispatcher\EventDispatcherInterface',
            'Symfony\Component\HttpFoundation',
            'Symfony\Component\Validator',
            'Symfony\Contracts\Translation\TranslatorInterface',

            'Doctrine\DBAL',

            'Ramsey\Uuid\Uuid',
            'Webmozart\Assert\Assert',
        ]
    )->in('Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure'),
];

$config = new Configuration($rules, $finder);

return $config;
