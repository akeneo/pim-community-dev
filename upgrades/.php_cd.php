<?php

declare(strict_types=1);

use Akeneo\CouplingDetector\Configuration\Configuration;
use Akeneo\CouplingDetector\Configuration\DefaultFinder;
use Akeneo\CouplingDetector\RuleBuilder;

$finder = new DefaultFinder();
$builder = new RuleBuilder();

$rules = [
    $builder->only(
        [
            // Expected dependencies for DBAL:
            'Doctrine\DBAL',
            'Doctrine\Migrations',
            // Expected dependencies for ES:
            'Elastic\Elasticsearch\ClientBuilder',
            'Elastic\Elasticsearch\Client',
            'Akeneo\Tool\Bundle\ElasticsearchBundle\Client',
            // Required for accessing DBAL & ES services:
            'Symfony\Component\DependencyInjection\ContainerAwareInterface',
            'Symfony\Component\DependencyInjection\ContainerInterface',
            'Symfony\Component\DependencyInjection\ContainerAwareTrait',
            // ZDD migrations
            'Akeneo\Pim\Enrichment\Bundle\Command\ZddMigrations',
            'Akeneo\Tool\Component\StorageUtils\Migration\V20230622175500OptimizeTableWithInstantColsMigration',

            // Dangerous dependencies, migrations shouldn't rely on services
            'Akeneo\Connectivity\Connection\Domain\Apps\DTO\AsymmetricKeys',
            'Akeneo\Connectivity\Connection\Infrastructure\Apps\Persistence\SaveAsymmetricKeysQuery',
            'Akeneo\Pim\Enrichment\Bundle\Command\MigrateToUuid\MigrateToUuidStep',
            'Akeneo\Tool\Bundle\ElasticsearchBundle\IndexConfiguration\Loader',
            'Akeneo\Tool\Component\Batch\Job',
            'Akeneo\Tool\Component\Batch\Job\BatchStatus',
            'Akeneo\Tool\Component\Batch\Job\ExitStatus',
            'Akeneo\Platform\Bundle\PimVersionBundle\VersionProviderInterface',
            'Oro\Bundle\SecurityBundle\Acl\Persistence\AclManager',
            'phpseclib3\Crypt\RSA',
            'phpseclib3\File\X509',
            'Symfony\Bundle\FrameworkBundle\Console\Application',
            'Symfony\Component\Console\Command\Command',
            'Symfony\Component\Console\Input\ArrayInput',
            'Symfony\Component\Console\Output\BufferedOutput',
            'Symfony\Component\DependencyInjection\ParameterBag\ParameterBag',
            'Symfony\Component\Yaml\Yaml',
            'Webmozart\Assert\Assert',
        ]
    )->in('Pim\Upgrade\Schema'),
];

$config = new Configuration($rules, $finder);

return $config;
