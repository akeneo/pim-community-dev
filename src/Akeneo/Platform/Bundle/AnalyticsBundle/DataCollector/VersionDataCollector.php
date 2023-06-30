<?php

declare(strict_types=1);

namespace Akeneo\Platform\Bundle\AnalyticsBundle\DataCollector;

use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlags;
use Akeneo\Platform\Bundle\InstallerBundle\InstallStatusManager\InstallStatusManager;
use Akeneo\Platform\Bundle\PimVersionBundle\VersionProviderInterface;
use Akeneo\Tool\Component\Analytics\DataCollectorInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Collects basic data about the PIM and its host server:
 * - edition (CE or EE)
 * - version
 * - environment (prod, dev, test)
 * - date of installation
 * - Apache or NGINX + version
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class VersionDataCollector implements DataCollectorInterface
{
    public function __construct(
        protected RequestStack $requestStack,
        protected VersionProviderInterface $versionProvider,
        protected InstallStatusManager $installStatusManager,
        protected string $environment,
        private readonly FeatureFlags $featureFlags,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function collect()
    {
        $collectedData = [
            'pim_edition'      => $this->versionProvider->getEdition(),
            'pim_version'      => $this->versionProvider->getPatch(),
            'pim_environment'  => $this->environment,
            'pim_install_time' => $this->installStatusManager->getPimInstallDateTime()?->format(\DateTime::ATOM),
            'server_version'   => $this->getServerVersion(),
        ];

        return $this->appendResetData($collectedData);
    }

    protected function getServerVersion(): string
    {
        $version = '';
        $request = $this->requestStack->getCurrentRequest();

        if (null !== $request) {
            $version = $request->server->get('SERVER_SOFTWARE');
        }

        return $version;
    }

    private function appendResetData(array $collectedData): array
    {
        if ($this->featureFlags->isEnabled('reset_pim')) {
            $resetData = $this->installStatusManager->getPimResetData();

            if (null !== $resetData) {
                $collectedData = [...$collectedData, ...$resetData];
            }
        }

        return $collectedData;
    }
}
