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
            'Elasticsearch\Client',
            'Akeneo\Tool\Bundle\ElasticsearchBundle\Client',
            // Required for accessing DBAL & ES services:
            'Symfony\Component\DependencyInjection\ContainerAwareInterface',
            'Symfony\Component\DependencyInjection\ContainerInterface',

            // Dangerous dependencies, migrations shouldn't rely on services
            'Akeneo\Platform\VersionProvider',
            'Akeneo\Tool\Bundle\ElasticsearchBundle\IndexConfiguration\UpdateIndexMappingWrapper',
            'Akeneo\Tool\Component\Batch\Job\BatchStatus',
            'Akeneo\Tool\Component\Batch\Job\ExitStatus',
            'Oro\Bundle\SecurityBundle\Acl\Persistence\AclManager',
            'Psr\Container\ContainerInterface',
            'Webmozart\Assert\Assert',
            'Psr\Log\LoggerInterface',
            'phpseclib3\Crypt\RSA',
        ]
    )->in('Pim\Upgrade\Schema'),
];

$config = new Configuration($rules, $finder);

return $config;
