<?php

namespace Akeneo\Platform\Bundle\NotificationBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * UserNotification entity repository
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UserNotificationRepository extends EntityRepository implements UserNotificationRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function countUnreadForUser(UserInterface $user)
    {
        $qb = $this->createQueryBuilder('n');

        return (int) $qb
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
     * {@inheritdoc}
     */
    public function markAsViewed(UserInterface $user, $id)
    {
        $qb = $this->_em->createQueryBuilder()
            ->update($this->_entityName, 'n')
            ->set('n.viewed', true)
            ->where('n.user = :user')
            ->setParameter('user', $user);

        if (null !== $id) {
            $qb
                ->andWhere('n.id = :id')
                ->setParameter('id', $id);
        }

        $qb->getQuery()->execute();
    }
}
