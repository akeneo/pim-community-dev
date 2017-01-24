<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2017 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\ActivityManagerBundle\Doctrine\ORM\Repository;

use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use PimEnterprise\Component\ActivityManager\Repository\NotificationHistoryRepositoryInterface;

/**
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
class NotificationHistoryRepository extends EntityRepository implements NotificationHistoryRepositoryInterface
{
    /**
     * @param EntityManager $em
     * @param ClassMetadata $class
     */
    public function __construct(EntityManager $em, $class)
    {
        parent::__construct($em, $em->getClassMetadata($class));
    }

    /**
     * {@inheritdoc}
     */
    public function findNotificationHistory($project, $user)
    {
        return $this->findOneBy(['project' => $project, 'user' => $user]);
    }

    /**
     * {@inheritdoc}
     */
    public function hasBeenNotifiedForProjectCreation($project, $user)
    {
        $notificationHistory = $this->findOneBy(['project' => $project, 'user' => $user]);

        if (null === $notificationHistory) {
            return false;
        }

        return true === $notificationHistory->isNotificationProjectCreation();
    }

    /**
     * {@inheritdoc}
     */
    public function hasBeenNotifiedForProjectFinished($project, $user)
    {
        $notificationHistory = $this->findOneBy(['project' => $project, 'user' => $user]);

        if (null === $notificationHistory) {
            return false;
        }

        return true === $notificationHistory->isNotificationProjectFinished();
    }
}
