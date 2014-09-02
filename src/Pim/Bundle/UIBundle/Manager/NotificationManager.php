<?php

namespace Pim\Bundle\UIBundle\Manager;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Oro\Bundle\UserBundle\Entity\User;
use Pim\Bundle\UIBundle\Entity\Notification;
use Pim\Bundle\UIBundle\Entity\NotificationEvent;
use Pim\Bundle\UIBundle\Factory\NotificationFactory;

/**
 * Notification manager
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class NotificationManager
{
    /** @var EntityManager */
    protected $entityManager;

    /** @var EntityRepository */
    protected $repository;

    /** @var NotificationFactory  */
    protected $factory;

    /**
     * @param EntityManager       $entityManager
     * @param EntityRepository    $repository
     * @param NotificationFactory $factory
     */
    public function __construct(
        EntityManager $entityManager,
        EntityRepository $repository,
        NotificationFactory $factory
    ) {
        $this->entityManager = $entityManager;
        $this->repository    = $repository;
        $this->factory       = $factory;
    }

    /**
     * Send a notification to the given users
     *
     * @param User[] $users   Users which have to be notified
     * @param string $message Message which has to be sent
     * @param string $type    Success by default
     * @param array  $options ['route' => '', 'routeParams' => [], 'messageParams' => [], 'context => '']
     *
     * @return NotificationManager
     */
    public function notify(array $users, $message, $type = 'success', array $options = [])
    {
        $notificationEvent = $this->factory->createNotificationEvent($message, $type, $options);

        foreach ($users as $user) {
            $notification = $this->factory->createNotification($notificationEvent, $user);
            $this->entityManager->persist($notification);
        }

        $this->entityManager->persist($notificationEvent);

        return $this;
    }

    /**
     * @param User $user
     *
     * @return Notification[]
     */
    public function getNotifications(User $user)
    {
        return $this->repository->findBy(['user' => $user]);
    }
}
