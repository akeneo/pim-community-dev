<?php

namespace Pim\Bundle\DataGridBundle\Datasource;

use Oro\Bundle\DataGridBundle\Datasource\DatasourceInterface as OroDatasourceInterface;
use Pim\Bundle\DataGridBundle\Datasource\ResultRecord\HydratorInterface;
use Pim\Bundle\DataGridBundle\Doctrine\ORM\Repository\MassActionRepositoryInterface;

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
     * @return \Doctrine\ORM\QueryBuilder
     *
     * @deprecated you should avoid this method, it's a design flaw, still used by,
     *  `Pim\Bundle\DataGridBundle\Extension\MassAction\MassActionDispatcher`,
     *  `Pim\Bundle\DataGridBundle\Extension\Selector\OrmSelectorExtension`
     */
    public function getQueryBuilder();

    /**
     * Get repository
     *
     * @return mixed
     *
     * @deprecated you should avoid this method, it's a design flaw, still used by,
     *   `PimEnterprise\Bundle\DataGridBundle\EventListener\AddPermissionsToGridListener`,
     */
    public function getRepository();

    /**
     * Get repository
     *
     * @return mixed
     *
     * @deprecated you should avoid this method, it's a design flaw, still used by,
     *   `Pim\Bundle\DataGridBundle\Extension\MassAction\Handler\DeleteMassActionHandler`,
     *   `Pim\Bundle\DataGridBundle\Extension\MassAction\Handler\DeleteProductMassActionHandler`
     * It's used to get the relevant repository to finally call a method `applyMassActionParameters()` or
     * `deleteFromIds()` on this repository
     */
    public function getMassActionRepository();

    /**
     * Define mass action repository for datasource
     *
     * @param MassActionRepositoryInterface $massActionRepository
     *
     * @deprecated you should avoid this method, it's a design flaw, still used defining datasource services for
     * product, published product and rules
     */
    public function setMassActionRepository(MassActionRepositoryInterface $massActionRepository);

    /**
     * Set hydrator
     *
     * @param HydratorInterface $hydrator
     *
     * @return DatasourceInterface
     *
     * @deprecated you should avoid this method, it's a design flaw, it allows the change the hydration mode in several
     * actions, for instance, still in used by,
     *   `Pim\Bundle\DataGridBundle\Extension\MassAction\Handler\DeleteMassActionHandler`,
     *   `Pim\Bundle\DataGridBundle\Extension\MassAction\Handler\EditMassActionHandler`,
     *   `Pim\Bundle\DataGridBundle\Extension\MassAction\Handler\SequentialEditActionHandler`
     *   `Pim\Bundle\DataGridBundle\Extension\MassAction\Handler\ExportMassActionHandler`
     * Hydration mode may be passed (or not) as argument of $datasource->getResults(); to avoid to change the hydration
     * before to call the getResults() method
     */
    public function setHydrator(HydratorInterface $hydrator);
}
