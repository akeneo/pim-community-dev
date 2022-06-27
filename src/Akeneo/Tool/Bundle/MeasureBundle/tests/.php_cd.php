<?php

declare(strict_types=1);

use Akeneo\CouplingDetector\Configuration\Configuration;
use Akeneo\CouplingDetector\Configuration\DefaultFinder;
use Akeneo\CouplingDetector\RuleBuilder;

$finder = new DefaultFinder();
$finder->notPath(['tests', 'spec']);
$builder = new RuleBuilder();

$rules = [
    $builder->only(
        [
            'Akeneo\Tool',
            'Akeneo\Platform\Bundle\FrameworkBundle\Security\SecurityFacadeInterface',
            'Akeneo\Platform\Bundle\InstallerBundle\Event\InstallerEvents',
            'Akeneo\Pim\Structure\Bundle\Query\PublicApi\Attribute\Sql\IsThereAtLeastOneAttributeConfiguredWithMeasurementFamily',

            'Doctrine\DBAL\Connection',
            'Doctrine\DBAL\Types\Type',
            'Doctrine\DBAL\Types\Types',

            'JsonSchema\Validator',
            'Psr\EventDispatcher\EventDispatcherInterface',
            'Symfony\Contracts\EventDispatcher\Event',
            'Symfony\Component',
            'Webmozart\Assert\Assert',
        ],
    )->in('Akeneo\Tool\Bundle\MeasureBundle'),
];

return new Configuration($rules, $finder);
