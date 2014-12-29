<?php

namespace Pim\Bundle\CatalogBundle\Entity\Repository;

use Pim\Bundle\CatalogBundle\Doctrine\ReferableEntityRepository;

/**
 * Currency repository
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CurrencyRepository extends ReferableEntityRepository
{
    /**
     * Return an array of activated currencies codes
     *
     * @return array
     */
    public function getActivatedCurrenciesCodes()
    {
        $criterias = array('activated' => true);
        $currencies = $this->findBy($criterias);

        return array_map(
            function ($value) {
                return $value->getCode();
            },
            $currencies
        );
    }

    /**
     * Return an array of activated currencies
     *
     * @return array
     */
    public function getActivatedCurrencies()
    {
        $qb = $this->getActivatedCurrenciesQB();

        return $qb->getQuery()->getResult();
    }

    /**
     * Return an array of currency codes
     *
     * @return array
     */
    public function getActivatedCurrencyCodes()
    {
        $qb = $this->getActivatedCurrenciesQB();
        $qb->select('c.code');

        $res = $qb->getQuery()->getScalarResult();

        $codes = [];
        foreach ($res as $row) {
            $codes[] = $row['code'];
        }

        return $codes;
    }

    /**
     * Return a query builder for activated currencies
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getActivatedCurrenciesQB()
    {
        $qb = $this->createQueryBuilder('c');
        $qb->where($qb->expr()->eq('c.activated', true))
           ->orderBy('c.code');

        return $qb;
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function createDatagridQueryBuilder()
    {
        $qb = $this->createQueryBuilder('c');
        $rootAlias = $qb->getRootAlias();

        $qb->addSelect($rootAlias);

        return $qb;
    }
}
