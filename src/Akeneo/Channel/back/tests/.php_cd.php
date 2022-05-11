<?php

use Akeneo\CouplingDetector\Configuration\Configuration;
use Akeneo\CouplingDetector\Configuration\DefaultFinder;
use Akeneo\CouplingDetector\RuleBuilder;

$finder = new DefaultFinder();
$finder->notPath('tests');
$builder = new RuleBuilder();

$rules = [
    $builder->only([
        'Symfony\Component',
        'Doctrine\DBAL\Connection',

        'Akeneo\Channel',
        'Akeneo\Tool\Component\StorageUtils\Cache\LRUCache',
    ])->in('AkeneoEnterprise\Channel\Infrastructure'),
];

$config = new Configuration($rules, $finder);

return $config;
