<?php

namespace Pim\Bundle\UIBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Oro\Bundle\UserBundle\Entity\User;

/**
 * Notification entity repository
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class NotificationRepository extends EntityRepository
{
    /**
     * Returns the number of notifications the user hasn't viewed
     *
     * @param User $user
     *
     * @return integer
     */
    public function countUnreadForUser(User $user)
    {
        $qb = $this->createQueryBuilder('n');

        return $qb
            ->select(
                $qb->expr()->countDistinct('n.id')
            )
            ->where('n.user = :user')
            ->andWhere('n.viewed = false')
            ->setParameter('user', $user)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
