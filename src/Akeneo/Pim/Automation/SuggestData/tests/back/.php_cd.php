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
        'Akeneo\Pim\Automation\SuggestData\Domain',

        // Akeneo external bounded contexts
        'Akeneo\Pim\Structure\Component',
        'Akeneo\Pim\Enrichment\Component'
    ])->in('Akeneo\Pim\Automation\SuggestData\Domain'),
    $builder->only([
        'Akeneo\Pim\Automation\SuggestData\Domain',
        'Akeneo\Pim\Automation\SuggestData\Application',

        // Akeneo external bounded contexts
        'Akeneo\Pim\Structure\Component',
        'Akeneo\Pim\Enrichment\Component'
    ])->in('Akeneo\Pim\Automation\SuggestData\Application'),
    $builder->only([
        'Akeneo\Pim\Automation\SuggestData\Infrastructure',
        'Akeneo\Pim\Automation\SuggestData\Application',
        'Akeneo\Pim\Automation\SuggestData\Domain',

        // Akeneo external bounded contexts
        'Akeneo\Pim\Structure\Component',
        'Akeneo\Pim\Enrichment\Component',
        'Akeneo\Pim\WorkOrganization\Workflow\Component',
        'Akeneo\Platform\Bundle\InstallerBundle\Event',
        'Akeneo\Tool\Component\StorageUtils',

        // External dependencies
        'Oro\Bundle\ConfigBundle\Entity\Config',
        'Doctrine',
        'Symfony',
        'Guzzle'
    ])->in('Akeneo\Pim\Automation\SuggestData\Infrastructure'),
    $builder->only([
        
    ])
];

$config = new Configuration($rules, $finder);

return $config;
