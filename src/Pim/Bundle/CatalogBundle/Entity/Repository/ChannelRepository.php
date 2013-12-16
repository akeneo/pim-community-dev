<?php

namespace Pim\Bundle\CatalogBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Channel repository
 * Define a default sort order by label
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
    public function findBy(array $criteria, array $orderBy = array('label' => 'ASC'), $limit = null, $offset = null)
    {
        return parent::findBy($criteria, $orderBy, $limit, $offset);
    }

    /**
     * {@inheritdoc}
     */
    public function findOneBy(array $criteria, array $orderBy = array('label' =>'ASC'))
    {
        return parent::findOneBy($criteria, $orderBy);
    }

    /**
     * Return the number of existing channels
     *
     * @return interger
     */
    public function countAll()
    {
        $qb = $this->createQueryBuilder('c');

        return $qb
            ->select('count(c.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }
}
