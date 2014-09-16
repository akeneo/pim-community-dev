<?php

namespace Pim\Bundle\NotificationBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Oro\Bundle\UserBundle\Entity\User;

/**
 * UserNotification entity repository
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UserNotificationRepository extends EntityRepository
{
    /**
     * Returns the number of user notifications the user hasn't viewed
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

    /**
     * Marks user notifications as viewed
     *
     * @param User           $user User
     * @param string|integer $id   Can be numeric or 'all'
     */
    public function markAsViewed(User $user, $id)
    {
        $qb = $this->_em->createQueryBuilder()
            ->update($this->_entityName, 'n')
            ->set('n.viewed', true)
            ->where('n.user = :user')
            ->setParameter('user', $user);

        if (is_numeric($id)) {
            $qb
                ->andWhere('n.id = :id')
                ->setParameter('id', $id);
        }

        $qb->getQuery()->execute();
    }
}
