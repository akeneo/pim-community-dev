<?php

namespace Pim\Bundle\CatalogBundle\Entity\Repository;

use Pim\Bundle\CatalogBundle\Doctrine\ReferableEntityRepository;
use Pim\Bundle\CatalogBundle\Repository\CurrencyRepositoryInterface;

/**
 * Currency repository
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @deprecated will be moved to Pim\Bundle\CatalogBundle\Doctrine\ORM\Repository in 1.4
 */
class CurrencyRepository extends ReferableEntityRepository implements CurrencyRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function getActivatedCurrenciesCodes()
    {
        // TODO (JJ) useless var
        $criterias = array('activated' => true);
        $currencies = $this->findBy($criterias);

        // TODO (JJ) we could do that directly in the query :p
        return array_map(
            function ($value) {
                return $value->getCode();
            },
            $currencies
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getActivatedCurrencies()
    {
        $qb = $this->getActivatedCurrenciesQB();

        return $qb->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
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
     * {@inheritdoc}
     */
    public function getActivatedCurrenciesQB()
    {
        $qb = $this->createQueryBuilder('c');
        $qb->where($qb->expr()->eq('c.activated', true))
           ->orderBy('c.code');

        return $qb;
    }

    /**
     * {@inheritdoc}
     */
    public function createDatagridQueryBuilder()
    {
        $qb = $this->createQueryBuilder('c');
        $rootAlias = $qb->getRootAlias();

        $qb->addSelect($rootAlias);

        return $qb;
    }
}
