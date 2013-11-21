<?php

namespace Pim\Bundle\CustomEntityBundle\Manager;

/**
 * Interface for ORM custom entity managers
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface OrmManagerInterface extends ManagerInterface
{
    /**
     * Returns a query builder for the datagrid
     *
     * @param string $entityClass
     * @param array  $options
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function createQueryBuilder($entityClass, array $options = array());
}
