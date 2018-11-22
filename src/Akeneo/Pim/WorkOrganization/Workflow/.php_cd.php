<?php

use Akeneo\CouplingDetector\Configuration\Configuration;
use Akeneo\CouplingDetector\Configuration\DefaultFinder;
use Akeneo\CouplingDetector\RuleBuilder;

$builder = new RuleBuilder();
$finder = new DefaultFinder();

$rules = [
    $builder->only([
        'Symfony\Component',
    ])->in('Akeneo\Pim\WorkOrganization\Workflow\Bundle'),
    $builder->only([
        'Symfony\Component',
    ])->in('Akeneo\Pim\WorkOrganization\Workflow\Component'),
];

$config = new Configuration($rules, $finder);

return $config;

