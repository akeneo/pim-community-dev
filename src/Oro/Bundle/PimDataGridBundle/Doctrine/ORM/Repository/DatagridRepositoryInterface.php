<?php

namespace Oro\Bundle\PimDataGridBundle\Doctrine\ORM\Repository;

use Doctrine\ORM\QueryBuilder;

/**
 * Build datagrid query builder, aims to extract this query building outside of business object repositories
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface DatagridRepositoryInterface
{
    /**
     * @return QueryBuilder
     */
    public function createDatagridQueryBuilder();
}
