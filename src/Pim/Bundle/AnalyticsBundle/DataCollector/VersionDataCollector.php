<?php

namespace Pim\Bundle\AnalyticsBundle\DataCollector;

use Akeneo\Component\Analytics\DataCollectorInterface;
use Pim\Bundle\CatalogBundle\VersionProviderInterface;

/**
 * Returns basic data about the PIM and its host server
 * - edition (CE or EE)
 * - version
 * - storage (ORM or MongoDB)
 * - environment (prod, dev, test)
 * - date of installation
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class VersionDataCollector implements DataCollectorInterface
{
    /** @var string */
    protected $catalogStorage;

    /** @var string */
    protected $environment;

    /** @var string */
    protected $installTime;

    /** @var VersionProviderInterface */
    protected $versionProvider;

    /**
     * @param VersionProviderInterface $versionProvider
     * @param string                   $catalogStorage
     * @param string                   $environment
     * @param string                   $installTime
     */
    public function __construct(VersionProviderInterface $versionProvider, $catalogStorage, $environment, $installTime)
    {
        $this->versionProvider = $versionProvider;
        $this->catalogStorage  = $catalogStorage;
        $this->environment     = $environment;
        $this->installTime     = $installTime;
    }

    /**
     * {@inheritdoc}
     */
    public function collect()
    {
        return [
            'pim_edition'        => $this->versionProvider->getEdition(),
            'pim_version'        => $this->versionProvider->getPatch(),
            'pim_storage_driver' => $this->catalogStorage,
            'pim_environment'    => $this->environment,
            'pim_install_time'   => $this->installTime,
        ];
    }
}
