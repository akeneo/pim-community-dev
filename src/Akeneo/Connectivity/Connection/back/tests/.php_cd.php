<?php

declare(strict_types=1);

use Akeneo\CouplingDetector\Configuration\Configuration;
use Akeneo\CouplingDetector\Configuration\DefaultFinder;
use Akeneo\CouplingDetector\RuleBuilder;

$finder = new DefaultFinder();
$finder->notPath('tests');
$builder = new RuleBuilder();

$rules = [
    $builder->only([
        'Akeneo\Connectivity\Connection\Domain',
    ])->in('Akeneo\Connectivity\Connection\Domain'),

    $builder->only([
        'Akeneo\Connectivity\Connection\Domain',
        'Akeneo\Connectivity\Connection\Application',
    ])->in('Akeneo\Connectivity\Connection\Application'),

    $builder->only([
        'Akeneo\Connectivity\Connection\Domain',
        'Akeneo\Connectivity\Connection\Application',
        'Akeneo\Connectivity\Connection\Infrastructure',

        'Doctrine\DBAL',
        'Symfony\Component\Console',
        'Symfony\Component\EventDispatcher',
        'Symfony\Component\HttpFoundation',
        'Symfony\Component\HttpKernel',
        'Symfony\Component\Routing',
        'Symfony\Component\Validator',
    ])->in('Akeneo\Connectivity\Connection\Infrastructure'),
];

$config = new Configuration($rules, $finder);

return $config;
