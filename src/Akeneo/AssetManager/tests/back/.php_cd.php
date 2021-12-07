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
        'Symfony\Contracts',
        'Traversable'
    ])->in('Akeneo\AssetManager\Domain'),
    $builder->only([
        'Akeneo\AssetManager\Domain',
        'Akeneo\Tool\Component',
        'Doctrine\Common',
        'Doctrine\Persistence',
        'Symfony\Component\EventDispatcher\EventSubscriberInterface',
        'Symfony\Component\EventDispatcher\Event',
        'Symfony\Component\Validator',
        'Symfony\Contracts',
        'Webmozart\Assert\Assert',

        //TODO: use a proper image storer interface for EditAssetFamilyHandler
        'Akeneo\AssetManager\Infrastructure\Filesystem\Storage'
    ])->in('Akeneo\AssetManager\Application'),
    $builder->only([
        'Akeneo\AssetManager\Application',
        'Akeneo\AssetManager\Domain',
        'Akeneo\AssetManager\Common',
        'Akeneo\Tool\Component',
        'Akeneo\Tool\Bundle\RuleEngineBundle\Model',
        'Akeneo\Tool\Bundle\RuleEngineBundle\Runner\RunnerInterface',
        'Akeneo\Tool\Bundle\ElasticsearchBundle',
        'Elasticsearch\Client',
        'Elasticsearch\ClientBuilder',
        'Doctrine',
        'Oro\Bundle\SecurityBundle\SecurityFacade',
        'Akeneo\Platform\Bundle\InstallerBundle',
        'Ramsey\Uuid\Uuid',
        'Symfony',
        'Webmozart\Assert\Assert',
        'JsonSchema\Validator',
        'PDO',
        'Akeneo\UserManagement\Component\Model\GroupInterface', // Because of an EventSubscriber on UserGroup deletion
        'Akeneo\UserManagement\Component\Model\Group', // For constant usage
        'Liip\ImagineBundle',
        'Akeneo\Pim\Automation\RuleEngine',
        'Psr\Log\LoggerInterface',
        'Imagine\Image',
        'Imagine\Imagick',
        'Imagine\Exception',
        'League\Flysystem\FilesystemReader',
        'League\Flysystem\FilesystemException',
        'GuzzleHttp',
        'Psr\Http\Message\RequestInterface',
        'Psr\Http\Message\ResponseInterface',
        'Psr\Http\Message\UriInterface',
        'Akeneo\Connectivity\Connection\Infrastructure\Apps\Security\ScopeMapperInterface',

        // TODO: asset families should not depend on PIM
        'Akeneo\Pim\Enrichment\AssetManager\Component',
        'Akeneo\Pim\Enrichment\Component\Product', // because of migration
        'Akeneo\Pim\Structure\Component',
        'Akeneo\Platform\Component\CatalogVolumeMonitoring\Volume\ReadModel\AverageMaxVolumes',
        'Akeneo\Platform\Component\CatalogVolumeMonitoring\Volume\Query\AverageMaxQuery',
        'Akeneo\Connectivity\Connection\Infrastructure\Apps\Security\ScopeMapperInterface',
    ])->in('Akeneo\AssetManager\Infrastructure'),
];

$config = new Configuration($rules, $finder);

return $config;
