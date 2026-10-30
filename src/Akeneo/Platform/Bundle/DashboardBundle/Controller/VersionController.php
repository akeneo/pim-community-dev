<?php
declare(strict_types=1);

namespace Akeneo\Platform\Bundle\DashboardBundle\Controller;

use Akeneo\Platform\Bundle\PimVersionBundle\VersionProviderInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

class VersionController
{
    /**
     * Version used in the tracker URL, as the update server only serves "<edition>-7.0.json" so far.
     * The real version is still sent to the tracker in the collected data.
     * Remove it once a version-free "<edition>.json" file exists on the update server, so releases need no new file.
     */
    private const TRACKER_VERSION = '7.0';

    private VersionProviderInterface $versionProvider;

    private string $analyticsUri;

    public function __construct(
        VersionProviderInterface $versionProvider,
        string $analyticsUri
    ) {
        $this->versionProvider = $versionProvider;
        $this->analyticsUri = $analyticsUri;
    }

    public function __invoke(): JsonResponse
    {
        $dashboardData = [
            'version' => $this->versionProvider->getFullVersion(),
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
            self::TRACKER_VERSION
        );
    }
}
