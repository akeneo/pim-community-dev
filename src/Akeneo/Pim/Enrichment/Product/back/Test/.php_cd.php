<?php

use Akeneo\CouplingDetector\Configuration\Configuration;
use Akeneo\CouplingDetector\Configuration\DefaultFinder;
use Akeneo\CouplingDetector\RuleBuilder;

$finder = new DefaultFinder();
$builder = new RuleBuilder();

$rules = [
    $builder->only([
        // Libs
        'Webmozart\Assert\Assert',
        'Symfony\Component\Validator\ConstraintViolationList',
        'Symfony\Component\Messenger\Envelope',
        'Symfony\Component\Messenger\MessageBusInterface',
    ])->in('Akeneo\Pim\Enrichment\Product\API'),

    $builder->only([
        // Libs
        'Webmozart\Assert\Assert',
    ])->in('Akeneo\Pim\Enrichment\Product\Domain'),

    $builder->only([
        'Akeneo\Pim\Enrichment\Product\API',
        'Akeneo\Pim\Enrichment\Product\Domain',

        // Libs
        'Webmozart\Assert\Assert',
        'Symfony\Component\Validator',
        'Symfony\Component\EventDispatcher',

        // Legacy
        'Akeneo\Pim\Enrichment\Component',
        'Akeneo\Tool\Component\StorageUtils\Exception\PropertyException',
        'Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface',
        'Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface',
    ])->in('Akeneo\Pim\Enrichment\Product\Application'),

    $builder->only([
        'Akeneo\Pim\Enrichment\Product\API',
        'Akeneo\Pim\Enrichment\Product\Domain',

        // Public APIs
        'Akeneo\Pim\Enrichment\Category\API',
        'Akeneo\Channel\Infrastructure\Component\Query\PublicApi',
        'Akeneo\Pim\Structure\Component\Query\PublicApi',
        'Akeneo\Channel\Locale\API',

        // Non public APIs
        'Akeneo\UserManagement\Component\Repository\UserRepositoryInterface',

        // Symfony, Doctrine DBAL and other libs
        'Webmozart\Assert\Assert',
        'Symfony\Component\Validator',
        'Symfony\Component\DependencyInjection',
        'Symfony\Component\HttpKernel',
        'Symfony\Component\Config\FileLocator',
        'Doctrine\DBAL\Connection',
    ])->in('Akeneo\Pim\Enrichment\Product\Infrastructure'),
];

$config = new Configuration($rules, $finder);

return $config;
