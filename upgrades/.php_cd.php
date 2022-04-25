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
            'Elasticsearch\ClientBuilder',
            'Elasticsearch\Namespaces\IndicesNamespace',
            'Akeneo\Tool\Bundle\ElasticsearchBundle\Client',
            // Required for accessing DBAL & ES services:
            'Symfony\Component\DependencyInjection\ContainerAwareInterface',
            'Symfony\Component\DependencyInjection\ContainerInterface',
            // Onboarder Serenity dependencies:
            'Akeneo\OnboarderSerenity\Infrastructure\Install\InstallOnboarderSerenityTables',

            // Dangerous dependencies, migrations shouldn't rely on services
            'Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId',
            'Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Install\Query\InitDataQualityInsightsSchema',
            'Akeneo\Pim\WorkOrganization\Workflow\Component\Factory\PimUserDraftSourceFactory',
            'Akeneo\Platform\VersionProvider',
            'Akeneo\Tool\Bundle\ElasticsearchBundle\IndexConfiguration\UpdateIndexMappingWrapper',
            'Akeneo\Tool\Component\Batch\Job\BatchStatus',
            'Akeneo\Tool\Component\Batch\Job\ExitStatus',
            'Doctrine\Common\Collections\ArrayCollection',
            'Oro\Bundle\SecurityBundle\Acl\Persistence\AclManager',
            'Oro\Bundle\SecurityBundle\Model\AclPermission',
            'Oro\Bundle\SecurityBundle\Model\AclPrivilege',
            'Oro\Bundle\SecurityBundle\Model\AclPrivilegeIdentity',
            'Psr\Container\ContainerInterface',
            'Symfony\Component\Security\Acl\Domain\RoleSecurityIdentity',
            'Webmozart\Assert\Assert',
            'Psr\Log\LoggerInterface',
        ]
    )->in('Pim\Upgrade\Schema'),
];

$config = new Configuration($rules, $finder);

return $config;
