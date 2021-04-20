<?php

return [
    // Symfony bundles
    Doctrine\Bundle\DoctrineBundle\DoctrineBundle::class => ['all' => true],
    Doctrine\Bundle\DoctrineCacheBundle\DoctrineCacheBundle::class => ['all' => true],
    Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle::class => ['all' => true],
    Symfony\Bundle\FrameworkBundle\FrameworkBundle::class => ['all' => true],
    Symfony\Bundle\MonologBundle\MonologBundle::class => ['all' => true],
    Symfony\Bundle\SecurityBundle\SecurityBundle::class => ['all' => true],
    Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle::class => ['all' => true],
    Symfony\Bundle\TwigBundle\TwigBundle::class => ['all' => true],
    Symfony\Bundle\AclBundle\AclBundle::class => ['all' => true],

    // ORO dependencies
    Doctrine\Bundle\FixturesBundle\DoctrineFixturesBundle::class => ['all' => true],
    FOS\JsRoutingBundle\FOSJsRoutingBundle::class => ['all' => true],
    FOS\RestBundle\FOSRestBundle::class => ['all' => true],
    Liip\ImagineBundle\LiipImagineBundle::class => ['all' => true],

    // ORO bundle
    Oro\Bundle\ConfigBundle\OroConfigBundle::class => ['all' => true],
    Oro\Bundle\DataGridBundle\OroDataGridBundle::class => ['all' => true],
    Oro\Bundle\FilterBundle\OroFilterBundle::class => ['all' => true],
    Oro\Bundle\SecurityBundle\OroSecurityBundle::class => ['all' => true],
    Oro\Bundle\TranslationBundle\OroTranslationBundle::class => ['all' => true],

    // PIM dependencies
    Akeneo\Tool\Bundle\ElasticsearchBundle\AkeneoElasticsearchBundle::class => ['all' => true],
    Akeneo\Tool\Bundle\BatchBundle\AkeneoBatchBundle::class => ['all' => true],
    Akeneo\Tool\Bundle\BatchQueueBundle\AkeneoBatchQueueBundle::class => ['all' => true],
    Akeneo\Tool\Bundle\FileStorageBundle\AkeneoFileStorageBundle::class => ['all' => true],
    Akeneo\Tool\Bundle\MeasureBundle\AkeneoMeasureBundle::class => ['all' => true],
    Akeneo\Tool\Bundle\StorageUtilsBundle\AkeneoStorageUtilsBundle::class => ['all' => true],
    Doctrine\Bundle\MigrationsBundle\DoctrineMigrationsBundle::class => ['all' => true],
    FOS\OAuthServerBundle\FOSOAuthServerBundle::class => ['all' => true],
    Oneup\FlysystemBundle\OneupFlysystemBundle::class => ['all' => true],

    // PIM bundles
    Oro\Bundle\PimFilterBundle\PimFilterBundle::class => ['all' => true],
    Akeneo\UserManagement\Bundle\PimUserBundle::class => ['all' => true],
    Akeneo\Channel\Bundle\AkeneoChannelBundle::class => ['all' => true],
    Akeneo\Pim\Enrichment\Bundle\AkeneoPimEnrichmentBundle::class => ['all' => true],
    Akeneo\Pim\Structure\Bundle\AkeneoPimStructureBundle::class => ['all' => true],
    Akeneo\Tool\Bundle\ClassificationBundle\AkeneoClassificationBundle::class => ['all' => true],
    Akeneo\Platform\Bundle\AnalyticsBundle\PimAnalyticsBundle::class => ['all' => true],
    Akeneo\Tool\Bundle\ApiBundle\PimApiBundle::class => ['all' => true],
    Akeneo\Platform\Bundle\CatalogVolumeMonitoringBundle\PimCatalogVolumeMonitoringBundle::class => ['all' => true],
    Akeneo\Tool\Bundle\ConnectorBundle\PimConnectorBundle::class => ['all' => true],
    Akeneo\Platform\Bundle\DashboardBundle\PimDashboardBundle::class => ['all' => true],
    Oro\Bundle\PimDataGridBundle\PimDataGridBundle::class => ['all' => true],
    Akeneo\Platform\Bundle\ImportExportBundle\PimImportExportBundle::class => ['all' => true],
    Akeneo\Platform\Bundle\InstallerBundle\PimInstallerBundle::class => ['all' => true],
    Akeneo\Platform\Bundle\NotificationBundle\PimNotificationBundle::class => ['all' => true],
    Akeneo\Platform\Bundle\UIBundle\PimUIBundle::class => ['all' => true],
    Akeneo\Tool\Bundle\VersioningBundle\AkeneoVersioningBundle::class => ['all' => true],
    Akeneo\Connectivity\Connection\Infrastructure\Symfony\AkeneoConnectivityConnectionBundle::class => ['all' => true],

    // PIM Enterprise bundle
    Akeneo\Tool\Bundle\RuleEngineBundle\AkeneoRuleEngineBundle::class => ['all' => true],
    Akeneo\Pim\Automation\RuleEngine\Bundle\AkeneoPimRuleEngineBundle::class => ['all' => true],
    Akeneo\Tool\Bundle\FileMetadataBundle\AkeneoFileMetadataBundle::class => ['all' => true],
    Akeneo\ReferenceEntity\Infrastructure\Symfony\AkeneoReferenceEntityBundle::class => ['all' => true],
    Akeneo\Pim\Permission\Bundle\AkeneoPimPermissionBundle::class => ['all' => true],
    Akeneo\Platform\Bundle\InstallerBundle\PimEnterpriseInstallerBundle::class => ['all' => true],
    Akeneo\Pim\WorkOrganization\TeamworkAssistant\Bundle\AkeneoPimTeamworkAssistantBundle::class => ['all' => true],
    Akeneo\Pim\WorkOrganization\ProductRevert\AkeneoPimProductRevertBundle::class => ['all' => true],
    Akeneo\Pim\WorkOrganization\Workflow\Bundle\AkeneoPimWorkflowBundle::class => ['all' => true],
    Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Symfony\AkeneoFranklinInsightsBundle::class => ['all' => true],
    Akeneo\Platform\Bundle\UIBundle\PimEnterpriseUIBundle::class => ['all' => true],
    Hslavich\OneloginSamlBundle\HslavichOneloginSamlBundle::class => ['all' => true],
    Akeneo\Platform\Bundle\AuthenticationBundle\AkeneoAuthenticationBundle::class => ['all' => true],
    Akeneo\AssetManager\Infrastructure\Symfony\AkeneoAssetManagerBundle::class => ['all' => true],
    Akeneo\Pim\Enrichment\AssetManager\Bundle\AkeneoPimEnrichmentAssetManagerBundle::class => ['all' => true],
    Akeneo\Platform\Bundle\MonitoringBundle\AkeneoMonitoringBundle::class => ['all' => true],
    Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Symfony\AkeneoDataQualityInsightsBundle::class => ['all' => true],
    Akeneo\SharedCatalog\AkeneoSharedCatalogBundle::class =>  ['all' => true],

    // Dev related bundles
    Akeneo\Tool\Bundle\DatabaseMetadataBundle\AkeneoDatabaseMetadataBundle::class => ['dev' => true, 'test' => true],
    Symfony\Bundle\DebugBundle\DebugBundle::class => ['dev' => true, 'test' => true, 'behat' => true],
    Symfony\Bundle\WebProfilerBundle\WebProfilerBundle::class => ['dev' => true, 'test' => true, 'behat' => true],
    Symfony\Bundle\WebServerBundle\WebServerBundle::class => ['dev' => true, 'test' => true, 'behat' => true],
];
