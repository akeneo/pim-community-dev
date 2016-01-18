<?php

namespace Akeneo\Component\Analytics;

/**
 * Class ChainedDataCollectorInterface
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface ChainedDataCollectorInterface extends DataCollectorInterface
{
    /**
     * @param DataCollectorInterface $dataCollector
     */
    public function addCollector(DataCollectorInterface $dataCollector);
}
