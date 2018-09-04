<?php

namespace Akeneo\Platform\Bundle\AnalyticsBundle\DataCollector;

use Akeneo\Tool\Component\Analytics\DataCollectorInterface;

/**
 * Collects the list of all active bundles used by the PIM.
 *
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class BundlesDataCollector implements DataCollectorInterface
{
    /** @var array */
    protected $bundles;

    /**
     * @param array $bundles
     */
    public function __construct(array $bundles)
    {
        $this->bundles = $bundles;
    }

    /**
     * {@inheritdoc}
     *
     * Collect the list of registered bundles
     */
    public function collect()
    {
        $bundles = $this->bundles;
        natsort($bundles);

        return ['registered_bundles' => array_values($bundles)];
    }
}
