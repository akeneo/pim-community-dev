<?php

namespace Pim\Bundle\AnalyticsBundle\DataCollector;

use Akeneo\Component\Analytics\DataCollectorInterface;
use Pim\Bundle\AnalyticsBundle\Provider\ServerVersionProvider;
use Pim\Bundle\AnalyticsBundle\Provider\StorageVersionProvider;

/**
 * Returns advanced data about the host server of the PIM
 * - MySQL or MariaDB used + version
 * - MongoDB version (if used)
 * - Apache or NGINX + version
 *
 * @author    Damien Carcel <damien.carcel@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AdvancedVersionDataCollector implements DataCollectorInterface
{
    /** @var ServerVersionProvider */
    protected $serverVersionProvider;

    /** @var StorageVersionProvider */
    protected $storageVersionProvider;

    /**
     * @param StorageVersionProvider $storageVersionProvider
     * @param ServerVersionProvider  $serverVersionProvider
     */
    public function __construct(
        StorageVersionProvider $storageVersionProvider,
        ServerVersionProvider $serverVersionProvider
    ) {
        $this->storageVersionProvider = $storageVersionProvider;
        $this->serverVersionProvider  = $serverVersionProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function collect()
    {
        return array_merge(
            $this->storageVersionProvider->provide(),
            $this->serverVersionProvider->provide()
        );
    }
}
