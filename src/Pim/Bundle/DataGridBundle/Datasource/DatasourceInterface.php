<?php

namespace Pim\Bundle\DataGridBundle\Datasource;

use Doctrine\Common\Persistence\ObjectManager;
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
     * ORM default datasource
     *
     * @staticvar string
     */
    const DATASOURCE_DEFAULT = 'pim_datasource_default';

    /**
     * Datasource used for resources that are stored either via ORM or via Mongo DB ODM
     *
     * @staticvar string
     */
    const DATASOURCE_DUAL = 'pim_datasource_dual';

    /**
     * Product datasource (either ORM or Mongo DB ODM)
     *
     * @staticvar string
     */
    const DATASOURCE_PRODUCT = 'pim_datasource_product';

    /**
     * Get the query builder
     *
     * @return \Doctrine\ORM\QueryBuilder|\Doctrine\ODM\MongoDB\Query\Builder
     */
    public function getQueryBuilder();

    /**
     * @return ObjectManager
     */
    public function getObjectManager();

    /**
     * Get repository
     *
     * @return \Doctrine\Common\Persistence\ObjectRepository
     */
    public function getRepository();

    /**
     * Get repository
     *
     * @return mixed
     */
    public function getMassActionRepository();

    /**
     * Set hydrator
     *
     * @param HydratorInterface $hydrator
     *
     * @return DatasourceInterface
     */
    public function setHydrator(HydratorInterface $hydrator);
}
