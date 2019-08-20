<?php

use Akeneo\CouplingDetector\Configuration\Configuration;
use Akeneo\CouplingDetector\Configuration\DefaultFinder;
use Akeneo\CouplingDetector\RuleBuilder;

$finder = new DefaultFinder();
$builder = new RuleBuilder();

$rules = [
    $builder->only([
        'Akeneo\Tool\Component',
        'Webmozart\Assert\Assert',
        'Symfony\Component\EventDispatcher\Event',
        'Traversable'
    ])->in('Akeneo\AssetManager\Domain'),
    $builder->only([
        'Akeneo\AssetManager\Domain',
        'Akeneo\Tool\Component',
        'Doctrine\Common',
        'Symfony\Component\EventDispatcher\EventSubscriberInterface',
        'Symfony\Component\EventDispatcher\Event',
        'Webmozart\Assert\Assert',
    ])->in('Akeneo\AssetManager\Application'),
    $builder->only([
        'Akeneo\AssetManager\Application',
        'Akeneo\AssetManager\Domain',
        'Akeneo\AssetManager\Common',
        'Akeneo\Tool\Component',
        'Akeneo\Tool\Bundle\RuleEngineBundle\Model',
        'Akeneo\Tool\Bundle\RuleEngineBundle\Runner\RunnerInterface',
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
        'Akeneo\Pim\Automation\RuleEngine',

        // TODO: asset families should not depend on PIM
        'Akeneo\Pim\Enrichment\AssetManager\Component'
    ])->in('Akeneo\AssetManager\Infrastructure'),
];

$config = new Configuration($rules, $finder);

return $config;
