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
        'Symfony\Contracts',
    ])->in('Akeneo\AssetManager\Domain'),
    $builder->only([
        'Akeneo\AssetManager\Domain',
        'Akeneo\Tool\Component',
        'Symfony\Component\EventDispatcher\EventSubscriberInterface',
        'Symfony\Component\Validator',
        'Webmozart\Assert\Assert',
        'Akeneo\Pim\Structure\Component\Query\PublicApi\Attribute\GetAttributeTranslations',
        'Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute',
        'Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes',
    ])->in('Akeneo\AssetManager\Application'),
    $builder->only([
        'Akeneo\AssetManager\Application',
        'Akeneo\AssetManager\Domain',

        'Akeneo\Channel\API',
        'Akeneo\Connectivity\Connection\Infrastructure\Apps\Security\ScopeMapperInterface',
        'Akeneo\Pim\Automation\RuleEngine',
        'Akeneo\Pim\Enrichment\AssetManager\Component',
        'Akeneo\Pim\Structure\Component\Model\AttributeInterface',
        'Akeneo\Pim\Structure\Component\Query\PublicApi',
        'Akeneo\Platform\Bundle\FrameworkBundle\Security\SecurityFacadeInterface',
        'Akeneo\Platform\Bundle\InstallerBundle',
        'Akeneo\Platform\Component\CatalogVolumeMonitoring\Volume\Query\AverageMaxQuery',
        'Akeneo\Platform\Component\CatalogVolumeMonitoring\Volume\ReadModel\AverageMaxVolumes',
        'Akeneo\Tool\Bundle\ElasticsearchBundle',
        'Akeneo\Tool\Bundle\RuleEngineBundle\Model',
        'Akeneo\Tool\Bundle\RuleEngineBundle\Runner\RunnerInterface',
        'Akeneo\Tool\Component',
        'Akeneo\UserManagement\Component\Model\GroupInterface', // Because of an EventSubscriber on UserGroup deletion

        'Doctrine\DBAL',
        'GuzzleHttp',
        'Imagine\Exception',
        'Imagine\Image',
        'JsonSchema\Validator',
        'League\Flysystem\FilesystemException',
        'League\Flysystem\FilesystemReader',
        'Liip\ImagineBundle',
        'Psr\Http\Message\RequestInterface',
        'Psr\Http\Message\ResponseInterface',
        'Psr\Http\Message\UriInterface',
        'Psr\Log\LoggerInterface',
        'Ramsey\Uuid\Uuid',
        'Symfony\Component',
        'Symfony\Contracts',
        'Webmozart\Assert\Assert',
        'Opis\JsonSchema',

        // TODO: Remove when MigrationPam folder is removed (RAB-521)
        'Akeneo\Pim\Enrichment\Component\Product',
        'Doctrine\Bundle\DoctrineBundle\ConnectionFactory',
    ])->in('Akeneo\AssetManager\Infrastructure'),
];

$config = new Configuration($rules, $finder);

return $config;
