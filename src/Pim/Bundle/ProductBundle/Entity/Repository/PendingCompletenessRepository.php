<?php

namespace Pim\Bundle\ProductBundle\Entity\Repository;

use Doctrine\ORM\AbstractQuery;

use Doctrine\ORM\EntityRepository;

/**
 *
 * Enter description here ...
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class PendingCompletenessRepository extends EntityRepository
{
    /**
     * Find a collection where field type is not null
     * Allow to find all pending completeness
     * @param string $fieldType
     * @throws \Exception
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
//     public function findByType($fieldType)
//     {
//         $expectedFields = array('channel', 'locale', 'family');
//         if (in_array($fieldType, $expectedFields)) {
//             $qb = $this->createQueryBuilder('pc');
//             $field = sprintf('pc.%s', $fieldType);
//             $qb
//                 ->where($qb->expr()->isNotNull($field))
//                 ->groupBy($field);

//             return $qb->getQuery()->getResult();
//         } else {
//             throw new \Exception(sprintf('Unexpected field type %s', $fieldType));
//         }
//     }

    public function findPendingChannels()
    {
        $qb = $this->createQueryBuilder('pc');
        $qb
            ->where($qb->expr()->isNotNull('pc.channel'));
//             ->andWhere($qb->expr()->isNull('pc.locale'))
//             ->groupBy('pc.channel');

        return $qb->getQuery()->getResult();
    }

    public function findPendingLocales()
    {
        $qb = $this->createQueryBuilder('pc');
        $qb->where($qb->expr()->isNotNull('pc.locale'));

        return $qb->getQuery()->getResult();
    }
}
