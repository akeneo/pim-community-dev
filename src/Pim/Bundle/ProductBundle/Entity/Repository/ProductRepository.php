<?php

namespace Pim\Bundle\ProductBundle\Entity\Repository;

use Doctrine\ORM\QueryBuilder;

use Oro\Bundle\FlexibleEntityBundle\Entity\Repository\FlexibleEntityRepository;

/**
 * Product repository
 *
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductRepository extends FlexibleEntityRepository
{
    /**
     * Add join to values tables
     *
     * @param QueryBuilder $qb
     */
    protected function addJoinToValueTables(QueryBuilder $qb)
    {
        parent::addJoinToValueTables($qb);

        $qb->addSelect('ValueMetric')
            ->addSelect('ValuePrices')
            ->addSelect('ValueMedia')
            ->leftJoin('Value.prices', 'ValuePrices')
            ->leftJoin('Value.media', 'ValueMedia')
            ->leftJoin('Value.metric', 'ValueMetric');
    }
}
