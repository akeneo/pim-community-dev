<?php

namespace Akeneo\Pim\Enrichment\Bundle\Doctrine\ORM;

use Doctrine\ORM\QueryBuilder;

/**
 * Provides util methods to ease the query building
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class QueryBuilderUtility
{
    /**
     * We update the query to count, get ids and fetch data, so, we can lost expected query builder parameters,
     * and we have to remove them
     *
     * @param QueryBuilder $qb
     */
    public static function removeExtraParameters(QueryBuilder $qb)
    {
        $parameters = $qb->getParameters();
        $dql = $qb->getDQL();
        foreach ($parameters as $parameter) {
            if (strpos($dql, ':'.$parameter->getName()) === false) {
                $parameters->removeElement($parameter);
            }
        }
        $qb->setParameters($parameters);
    }
}
