<?php
namespace Pim\Bundle\ConfigBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Locale repository
 * Define a default sort order by code
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class LocaleRepository extends EntityRepository
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
}
