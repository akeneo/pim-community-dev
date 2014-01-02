<?php

namespace Pim\Bundle\DataGridBundle\Model;

/**
 * Repository interface to implement to define custom datagrid query builder
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface DataGridRepositoryInterface
{
    /**
     * Create datagrid query builder
     *
     * @return mixed Doctrine/ODM/MongoDB/Query/Builder or Doctrine/ORM/QueryBuilder
     */
    public function createDatagridQueryBuilder();
}
