<?php

namespace Pim\Bundle\AnalyticsBundle\DataCollector;

use Akeneo\Component\Analytics\DataCollectorInterface;
use Pim\Bundle\CatalogBundle\VersionProviderInterface;
use Pim\Bundle\InstallerBundle\InstallStatusChecker\InstallStatusChecker;
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
    /** @var RequestStack */
    protected $requestStack;

    /** @var VersionProviderInterface */
    protected $versionProvider;

    /** @var string */
    protected $environment;

    /** @var InstallStatusChecker */
    protected $installStatusChecker;

    /**
     * @param RequestStack $requestStack
     * @param VersionProviderInterface $versionProvider
     * @param InstallStatusChecker $installStatusChecker
     * @param string $environment
     */
    public function __construct(
        RequestStack $requestStack,
        VersionProviderInterface $versionProvider,
        InstallStatusChecker $installStatusChecker,
        $environment
    ) {
        $this->requestStack = $requestStack;
        $this->versionProvider = $versionProvider;
        $this->installStatusChecker = $installStatusChecker;
        $this->environment = $environment;
    }

    /**
     * {@inheritdoc}
     */
    public function collect()
    {
        return [
            'pim_edition'      => $this->versionProvider->getEdition(),
            'pim_version'      => $this->versionProvider->getPatch(),
            'pim_environment'  => $this->environment,
            'pim_install_time' => $this->installStatusChecker->getInstalledFlag(),
            'server_version'   => $this->getServerVersion(),
        ];
    }

    /**
     * Returns the server version.
     *
     * @return string
     */
    protected function getServerVersion()
    {
        $version = '';
        $request = $this->requestStack->getCurrentRequest();

        if (null !== $request) {
            $version = $request->server->get('SERVER_SOFTWARE');
        }

        return $version;
    }
}
