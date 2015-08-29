<?php

namespace Pim\Bundle\NotificationBundle\Update;
use Pim\Bundle\CatalogBundle\Version;

/**
 * Class OperatingSystemDataCollector
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class VersionDataCollector implements DataCollectorInterface
{
    /** @var string */
    protected $catalogStorage;

    /** @param string $catalogStorage */
    public function __construct($catalogStorage)
    {
        $this->catalogStorage = $catalogStorage;
    }

    /**
     * {@inheritdoc}
     */
    public function collect()
    {
        return [
            'pim_edition' => Version::EDITION,
            'pim_version' => Version::VERSION,
            'pim_storage_driver' => $this->catalogStorage
        ];
    }
}