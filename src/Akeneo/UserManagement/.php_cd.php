<?php

use Akeneo\CouplingDetector\Configuration\Configuration;
use Akeneo\CouplingDetector\Configuration\DefaultFinder;
use Akeneo\CouplingDetector\RuleBuilder;

$finder = new DefaultFinder();
$finder->notPath('spec');
$finder->notPath('tests');

$builder = new RuleBuilder();

$rules = [
    $builder->forbids(['Akeneo\Pim', 'Akeneo\Channel\Bundle', 'Acme', 'Behat'])->in('Akeneo\UserManagement\Bundle'),
    $builder->forbids(['Akeneo\Pim', 'Akeneo\Channel\Bundle', 'Acme', 'Behat'])->in('Akeneo\UserManagement\Component'),
];

$config = new Configuration($rules, $finder);

return $config;
