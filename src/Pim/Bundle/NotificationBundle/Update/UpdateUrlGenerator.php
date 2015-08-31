<?php

namespace Pim\Bundle\NotificationBundle\Update;

use Pim\Bundle\CatalogBundle\VersionProviderInterface;

/**
 * Generates the url to call to fetch the last available versions from the update server
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UpdateUrlGenerator implements UpdateUrlGeneratorInterface
{
    /** @var DataCollectorInterface */
    protected $dataCollector;

    /** @var VersionProviderInterface */
    protected $versionProvider;

    /** @var string */
    protected $updateServerHost;

    /**
     * @param DataCollectorInterface   $dataCollector
     * @param VersionProviderInterface $versionProvider
     * @param string                   $updateServerHost
     */
    public function __construct(
        DataCollectorInterface $dataCollector,
        VersionProviderInterface $versionProvider,
        $updateServerHost
    ) {
        $this->dataCollector    = $dataCollector;
        $this->versionProvider  = $versionProvider;
        $this->updateServerHost = $updateServerHost;
    }

    /**
     * {@inheritdoc}
     */
    public function generateAvailablePatchsUrl()
    {
        $data            = $this->dataCollector->collect();
        $minorVersionKey = sprintf('%s-%s', $this->versionProvider->getEdition(), $this->versionProvider->getMinor());
        $queryParams     = http_build_query($data);
        $updateUrl       = sprintf('%s/%s?%s', $this->updateServerHost, $minorVersionKey, $queryParams);

        return $updateUrl;
    }
}
