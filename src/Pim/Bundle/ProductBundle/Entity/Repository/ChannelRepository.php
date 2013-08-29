<?php

namespace Pim\Bundle\ProductBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Channel repository
 * Define a default sort order by name
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ChannelRepository extends EntityRepository
{
    /**
     * {@inheritdoc}
     */
    public function findBy(array $criteria, array $orderBy = array('name' => 'ASC'), $limit = null, $offset = null)
    {
        return parent::findBy($criteria, $orderBy, $limit, $offset);
    }

    /**
     * {@inheritdoc}
     */
    public function findOneBy(array $criteria, array $orderBy = array('name' =>'ASC'))
    {
        return parent::findOneBy($criteria, $orderBy);
    }

    public function findPendingCompleteness()
    {
        $qb = $this->createQueryBuilder('f');
        $qb->innerJoin('f.pendingCompleteness', 'pc');

        return $qb->getQuery()->getResult();
    }
}
