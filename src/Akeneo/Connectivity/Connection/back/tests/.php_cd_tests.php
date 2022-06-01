<?php

declare(strict_types=1);

use Akeneo\CouplingDetector\Configuration\Configuration;
use Akeneo\CouplingDetector\Configuration\DefaultFinder;
use Akeneo\CouplingDetector\RuleBuilder;

$finder = new DefaultFinder();
$finder->path('tests');
$builder = new RuleBuilder();

$rules = [
    $builder->only(
        [
            // Mandatory
            'PhpSpec',
            'Prophecy',
            'PHPUnit\Framework\Assert',
            'Psr',

            // Internal
            'Akeneo\Connectivity\Connection',
            'Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag',

            // Symfony
            'Symfony\Component',
            'Symfony\Contracts',

            // Dependencies
            'OAuth2',
            'FOS',
            'Doctrine\DBAL',
            'Lcobucci', // JWT
            'GuzzleHttp',
        ]
    )->in('spec\Akeneo\Connectivity\Connection'),

    $builder->only(
        [
            // Mandatory
            'PHPUnit',
            'Akeneo\Test',

            // Internal
            'Akeneo\Connectivity\Connection',
            'Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag',

            // Symfony
            'Symfony\Component',
            'Symfony\Contracts',

            // Dependencies
            'FOS',
            'Doctrine',
            'Ramsey\Uuid\Uuid',
        ]
    )->in('Akeneo\Connectivity\Connection\Tests\Integration'),
];

$config = new Configuration($rules, $finder);

return $config;
