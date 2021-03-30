<?php
declare(strict_types=1);

namespace Akeneo\Platform\Bundle\DashboardBundle\Controller;

use Akeneo\Platform\VersionProviderInterface;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Symfony\Component\HttpFoundation\JsonResponse;

class VersionController
{
    private const GENERAL_AVAILABILITY_TAG_PATTERN = '~^\d\.\d\.\d~';

    private VersionProviderInterface $versionProvider;

    private ConfigManager $configManager;

    private string $analyticsUri;

    public function __construct(
        VersionProviderInterface $versionProvider,
        ConfigManager $configManager,
        string $analyticsUri
    ) {
        $this->versionProvider = $versionProvider;
        $this->configManager = $configManager;
        $this->analyticsUri = $analyticsUri;
    }

    public function __invoke(): JsonResponse
    {
        $dashboardData = [
            'version' => $this->versionProvider->getFullVersion(),
            'is_last_patch_displayed' => $this->isLastPatchDisplayed(),
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

    private function isLastPatchDisplayed(): bool
    {
        return boolval($this->configManager->get('pim_analytics.version_update')) &&
            1 === preg_match(self::GENERAL_AVAILABILITY_TAG_PATTERN, $this->versionProvider->getVersion());
    }
}
