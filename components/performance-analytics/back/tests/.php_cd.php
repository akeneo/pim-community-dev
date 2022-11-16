<?php

use Akeneo\CouplingDetector\Configuration\Configuration;
use Akeneo\CouplingDetector\Configuration\DefaultFinder;
use Akeneo\CouplingDetector\RuleBuilder;

$finder = new DefaultFinder();
$builder = new RuleBuilder();

$rules = [
    $builder->only(
        [
            'Webmozart\Assert',
            'Ramsey\Uuid\UuidInterface',
        ]
    )->in('Akeneo\PerformanceAnalytics\Domain'),

    $builder->only(
        [
            'Webmozart\Assert',
            'Akeneo\PerformanceAnalytics\Domain',
            'Psr\Log\LoggerInterface',
            'Ramsey\Uuid\UuidInterface',
        ]
    )->in('Akeneo\PerformanceAnalytics\Application'),

    $builder->only(
        [
            'Webmozart\Assert',
            'Psr\Log\LoggerInterface',
            'Ramsey\Uuid\Uuid',
            'Ramsey\Uuid\UuidInterface',

            'Akeneo\PerformanceAnalytics\Domain',
            'Akeneo\PerformanceAnalytics\Application',

            // Akeneo dependencies
            'Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag',
            'Akeneo\Tool\Component\Messenger\Tenant\TenantAwareInterface',
            'Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProduct',
            'Akeneo\Pim\Enrichment\Component\Product\Query\GetConnectorProducts',
            'Akeneo\Pim\Enrichment\Product\API\Event\Completeness\ProductWasCompletedOnChannelLocale',
            'Akeneo\Pim\Enrichment\Product\API\Event\Completeness\ProductWasCompletedOnChannelLocaleCollection',

            // symfony dependencies
            'Symfony\Component\Config\FileLocator',
            'Symfony\Component\DependencyInjection\ContainerBuilder',
            'Symfony\Component\DependencyInjection\Extension\Extension',
            'Symfony\Component\DependencyInjection\Loader\YamlFileLoader',
            'Symfony\Component\EventDispatcher\EventSubscriberInterface',
            'Symfony\Component\HttpFoundation',
            'Symfony\Component\HttpKernel\Bundle\Bundle',
            'Symfony\Component\HttpKernel\Exception\BadRequestHttpException',

            // Google Cloud
            'Google\Cloud\PubSub\Message',
            'Google\Cloud\PubSub\PubSubClient',
            'Google\Cloud\PubSub\Topic',

            // DBAL
            'Doctrine\DBAL\Connection',
        ]
    )->in('Akeneo\PerformanceAnalytics\Infrastructure'),
];

return new Configuration($rules, $finder);
