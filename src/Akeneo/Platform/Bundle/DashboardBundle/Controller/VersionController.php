<?php
declare(strict_types=1);

namespace Akeneo\Platform\Bundle\DashboardBundle\Controller;

use Akeneo\Platform\VersionProviderInterface;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Symfony\Component\HttpFoundation\JsonResponse;

final class VersionController
{
    private VersionProviderInterface $versionProvider;

    private ConfigManager $configManager;

    private string $analyticsUri;

    public function __construct(
        VersionProviderInterface $versionProvider,
        ConfigManager $configManager,
        string $analyticsUri
    )
    {
        $this->versionProvider = $versionProvider;
        $this->configManager = $configManager;
        $this->analyticsUri = $analyticsUri;
    }

    public function __invoke(): JsonResponse
    {
        $dashboardData = [
            'version' => $this->versionProvider->getFullVersion(),
            'is_last_patch_displayed' => boolval($this->configManager->get('pim_analytics.version_update')),
            'analytics_url' => $this->getAnalyticsUrl(),
            'is_analytics_wanted' => ($this->versionProvider->getVersion() !== 'master')
        ];

        return new JsonResponse($dashboardData);
    }

    private function getAnalyticsUrl(): string
    {
        return sprintf(
            '%s/%s-%s.json',
            rtrim($this->analyticsUri, '/'),
            $this->versionProvider->getEdition(),
            $this->versionProvider->getMinorVersion()
        );
    }
}
