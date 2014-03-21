<?php

namespace Pim\Bundle\DataGridBundle\Datasource;

use Oro\Bundle\DataGridBundle\Datasource\DatasourceInterface as OroDatasourceInterface;
use Pim\Bundle\DataGridBundle\Datasource\ResultRecord\HydratorInterface;

/**
 * Override of Oro datasource implementation
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface DatasourceInterface extends OroDatasourceInterface
{
    /**
     * Get repository
     *
     * @return ObjectRepository
     */
    public function getRepository();

    /**
     * Set hydrator
     *
     * @param HydratorInterface $hydrator
     *
     * @return DatasourceInterface
     */
    public function setHydrator(HydratorInterface $hydrator);
}
