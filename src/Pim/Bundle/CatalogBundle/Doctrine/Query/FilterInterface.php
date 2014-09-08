<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\Query;

/**
 * Filter interface
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface FilterInterface
{
    /**
     * Inject the query builder
     * TODO : QueryBuilderAwareInterface ?
     *
     * @param mixed $queryBuilder
     */
    public function setQueryBuilder($queryBuilder);

    /**
     * This filter supports the operator
     *
     * @param string $operator
     *
     * @return boolean
     */
    public function supportsOperator($field);
}
