<?php

namespace Pim\Bundle\CatalogBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Currency repository
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CurrencyRepository extends EntityRepository
{
    /**
     * Return a query builder for activated currencies
     *
     * @return QueryBuilder
     */
    public function getActivatedCurrenciesQB()
    {
        $qb = $this->createQueryBuilder('c');
        $qb->where($qb->expr()->eq('c.activated', true))
           ->orderBy('c.code');

        return $qb;
    }
}
