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
            'Elasticsearch\ClientBuilder',
            'Elasticsearch\Namespaces\IndicesNamespace',
            'Akeneo\Tool\Bundle\ElasticsearchBundle\Client',
            // Required for accessing DBAL & ES services:
            'Symfony\Component\DependencyInjection\ContainerAwareInterface',
            'Symfony\Component\DependencyInjection\ContainerInterface',
            'Symfony\Component\DependencyInjection\ContainerAwareTrait',

            // Dangerous dependencies, migrations shouldn't rely on services
            'Akeneo\Connectivity\Connection\Application\Settings\Command\CreateConnectionCommand',
            'Akeneo\Connectivity\Connection\Application\Settings\Command\CreateConnectionHandler',
            'Akeneo\Connectivity\Connection\Domain\Apps\DTO\AsymmetricKeys',
            'Akeneo\Connectivity\Connection\Domain\Settings\Exception\ConstraintViolationListException',
            'Akeneo\Connectivity\Connection\Domain\Settings\Model\Read\ConnectionWithCredentials',
            'Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType',
            'Akeneo\Connectivity\Connection\Infrastructure\Apps\Persistence\SaveAsymmetricKeysQuery',
            'Akeneo\Pim\Enrichment\Bundle\Command\MigrateToUuid\MigrateToUuidStep',
            'Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Indexer\ProductIndexer',
            'Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Indexer\ProductModelDescendantsAndAncestorsIndexer',
            'Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Indexer\ProductModelIndexer',
            'Akeneo\Pim\Enrichment\Component\Product\Factory\EmptyValuesCleaner',
            'Akeneo\Tool\Bundle\ElasticsearchBundle\IndexConfiguration\Loader',
            'Akeneo\Tool\Component\Batch\Job',
            'Akeneo\Tool\Component\Batch\Job\BatchStatus',
            'Akeneo\Tool\Component\Batch\Job\ExitStatus',
            'Akeneo\Platform\Bundle\PimVersionBundle\VersionProviderInterface',
            'Doctrine\Common\Collections\ArrayCollection',
            'Oro\Bundle\SecurityBundle\Acl\Persistence\AclManager',
            'Oro\Bundle\SecurityBundle\Model\AclPermission',
            'Oro\Bundle\SecurityBundle\Model\AclPrivilege',
            'Oro\Bundle\SecurityBundle\Model\AclPrivilegeIdentity',
            'phpseclib\Crypt\RSA',
            'phpseclib\File\X509',
            'Symfony\Bundle\FrameworkBundle\Console\Application',
            'Symfony\Component\Console\Command\Command',
            'Symfony\Component\Console\Input\ArrayInput',
            'Symfony\Component\Console\Output\BufferedOutput',
            'Symfony\Component\Console\Output\NullOutput',
            'Symfony\Component\DependencyInjection\ParameterBag\ParameterBag',
            'Symfony\Component\Security\Acl\Domain\RoleSecurityIdentity',
            'Webmozart\Assert\Assert',
        ]
    )->in('Pim\Upgrade\Schema'),
];

$config = new Configuration($rules, $finder);

return $config;
