<?php

namespace Pim\Bundle\NotificationBundle\Update;

/**
 * Class DataCollectorRegistryInterface
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface DataCollectorRegistryInterface
{
    /**
     * @param DataCollectorInterface $dataCollector
     */
    public function register(DataCollectorInterface $dataCollector);
}
