<?php

namespace Pim\Bundle\CatalogBundle\Entity\Repository;

use Pim\Bundle\CatalogBundle\Doctrine\ReferableEntityRepository;

/**
 * Locale repository
 * Define a default sort order by code
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class LocaleRepository extends ReferableEntityRepository
{
    /**
     * {@inheritdoc}
     */
    public function findBy(array $criteria, array $orderBy = array('code' => 'ASC'), $limit = null, $offset = null)
    {
        return parent::findBy($criteria, $orderBy, $limit, $offset);
    }

    /**
     * {@inheritdoc}
     */
    public function findOneBy(array $criteria, array $orderBy = array('code' =>'ASC'))
    {
        return parent::findOneBy($criteria, $orderBy);
    }

    /**
     * Return an array of activated locales
     *
     * @return array
     */
    public function getActivatedLocales()
    {
        $qb = $this->getActivatedLocalesQB();

        return $qb->getQuery()->getResult();
    }

    /**
     * Return a query builder for activated locales
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getActivatedLocalesQB()
    {
        return $this->createQueryBuilder('l')
            ->innerJoin('l.channels', 'channels')
            ->orderBy('l.code');
    }

    /**
     * Return a query builder for all locales
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getLocalesQB()
    {
        $qb = $this->createQueryBuilder('l');
        $qb->orderBy('l.code');

        return $qb;
    }

    /**
     * @return QueryBuilder
     */
    public function createDatagridQueryBuilder()
    {
        $qb = $this->createQueryBuilder('l');
        $rootAlias = $qb->getRootAlias();

        $qb->addSelect($rootAlias);

        return $qb;
    }
}
