<?php

namespace Pim\Bundle\DataGridBundle\Datasource;

use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\DataGridBundle\Datasource\DatasourceInterface as OroDatasourceInterface;
use Pim\Bundle\CatalogBundle\Repository\MassActionRepositoryInterface;
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
     * Define mass action repository for datasource
     *
     * @param MassActionRepositoryInterface $massActionRepository
     */
    public function setMassActionRepository(MassActionRepositoryInterface $massActionRepository);

    /**
     * Set hydrator
     *
     * @param HydratorInterface $hydrator
     *
     * @return DatasourceInterface
     */
    public function setHydrator(HydratorInterface $hydrator);
}
