<?php

namespace Pim\Bundle\CatalogBundle\Entity\Repository;

use Pim\Bundle\CatalogBundle\Doctrine\ReferableEntityRepository;
use Pim\Bundle\CatalogBundle\Entity\Channel;

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
        $qb = $this->createQueryBuilder('l');
        $qb->where($qb->expr()->eq('l.activated', true))
            ->orderBy('l.code');

        return $qb;
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

    /**
     * Get the deleted locales of a channel (the channel is updated but not flushed yet).
     *
     * @param Channel $channel
     *
     * @return array the list of deleted locales
     */
    public function getDeletedLocalesForChannel(Channel $channel)
    {
        $currentLocaleIds = array_map(
            function ($locale) {
                return $locale->getId();
            },
            $channel->getLocales()->toArray()
        );

        $dql = <<<DQL
            SELECT l
            JOIN l.channels c
            WHERE c.id = :channel_id
              AND l.id NOT IN (:current_locale_ids)
DQL;

        $query = $this
            ->getEntityManager()
            ->createQuery($dql)
            ->setParameter(':channel_id', $channel->getId())
            ->setParameter(':current_locale_ids', implode(',', $currentLocaleIds));

        return $query->getResult();
    }
}
