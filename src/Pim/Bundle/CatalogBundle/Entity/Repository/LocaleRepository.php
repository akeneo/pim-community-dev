<?php

namespace Pim\Bundle\CatalogBundle\Entity\Repository;

/**
 * Locale repository
 * Define a default sort order by code
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class LocaleRepository extends UniqueCodeEntityRepository
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
     * Find locales that have a fallback locale defined
     *
     * @return array
     */
    public function findWithFallback()
    {
        $qb = $this->createQueryBuilder('l');
        $qb->where($qb->expr()->isNotNull('l.fallback'))
           ->orderBy('l.code');

        return $qb->getQuery()->getResult();
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
     * Returns a query builder that select the available fallbacks
     *
     * A fallback must be activated and not link on another fallback
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getAvailableFallbacksQB()
    {
        $qb = $this->getActivatedLocalesQB();
        $qb->andWhere($qb->expr()->isNull('l.fallback'));

        return $qb;
    }

    /**
     * Get a collection of available fallback locales
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getAvailableFallbacks()
    {
        return $this->getAvailableFallbacksQB()->getQuery()->getResult();
    }
}
