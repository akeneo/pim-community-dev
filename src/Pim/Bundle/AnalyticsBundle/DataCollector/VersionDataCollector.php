<?php

namespace Pim\Bundle\AnalyticsBundle\DataCollector;

use Akeneo\Component\Analytics\DataCollectorInterface;
use Pim\Bundle\CatalogBundle\VersionProviderInterface;
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

    /** @var string */
    protected $installTime;

    /**
     * @param RequestStack             $requestStack
     * @param VersionProviderInterface $versionProvider
     * @param string                   $environment
     * @param string                   $installTime
     */
    public function __construct(
        RequestStack $requestStack,
        VersionProviderInterface $versionProvider,
        $environment,
        $installTime
    ) {
        $this->requestStack = $requestStack;
        $this->versionProvider = $versionProvider;
        $this->environment = $environment;
        $this->installTime = $installTime;
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
            'pim_install_time' => $this->installTime,
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
