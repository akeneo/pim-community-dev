<?php

use Akeneo\CouplingDetector\Configuration\Configuration;
use Akeneo\CouplingDetector\Configuration\DefaultFinder;
use Akeneo\CouplingDetector\RuleBuilder;

$finder = new DefaultFinder();
$builder = new RuleBuilder();

$rules = [
    $builder->only([
        'Closure',
        'DateTimeImmutable',
        'DateTimeZone',
        'Akeneo\Tool\Component',
        'Webmozart\Assert\Assert',
        'Symfony\Component\EventDispatcher\Event',
        'Symfony\Contracts',
    ])->in('Akeneo\ReferenceEntity\Domain'),
    $builder->only([
        'Akeneo\ReferenceEntity\Domain',
        'Akeneo\Tool\Component',
        'Doctrine\Common',
        'Doctrine\Persistence',
        'Symfony\Component\EventDispatcher\EventSubscriberInterface',
        'Symfony\Contracts',
        'Webmozart\Assert\Assert',
    ])->in('Akeneo\ReferenceEntity\Application'),
    $builder->only([
        'Akeneo\ReferenceEntity\Application',
        'Akeneo\ReferenceEntity\Domain',
        'Akeneo\Tool\Component',
        'Akeneo\Tool\Bundle\ElasticsearchBundle',
        'Doctrine\DBAL',
        'Oro\Bundle\SecurityBundle\SecurityFacade',
        'Akeneo\Platform\Bundle\InstallerBundle',
        'Ramsey\Uuid\Uuid',
        'Symfony',
        'Webmozart\Assert\Assert',
        'JsonSchema\Validator',
        'PDO',
        'Akeneo\UserManagement\Component\Model\GroupInterface', // Because of an EventSubscriber on UserGroup deletion
        'Liip\ImagineBundle',
        'Psr\Log\LoggerInterface', //Use logger in command
        // TODO: reference entities should not depend on PIM
        'Akeneo\Pim\Enrichment\ReferenceEntity\Component',
        'Akeneo\Platform\Component\CatalogVolumeMonitoring\Volume\ReadModel\AverageMaxVolumes',
        'Akeneo\Platform\Component\CatalogVolumeMonitoring\Volume\Query\AverageMaxQuery',
    ])->in('Akeneo\ReferenceEntity\Infrastructure'),
];

$config = new Configuration($rules, $finder);

return $config;
