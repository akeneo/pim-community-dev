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
     * Send a notification to given users
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
        $this->entityManager->flush();

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

    /**
     * It marks given notifications as viewed for the given user
     *
     * @param string $userId       User id
     * @param string $notifsToMark Can be numeric or 'all'
     *
     * return void
     */
    public function markNotificationsAsViewed($userId, $notifsToMark)
    {
        // TODO: use a direct query in repository to mark notifications as viewed directly can be faster
        if (is_numeric($notifsToMark)) {
            $findParams = ['user' => $userId, 'id' => $notifsToMark];
        } elseif ('all' === $notifsToMark) {
            $findParams = ['user' => $userId, 'viewed' => false];
        }
        $notifications = $this->repository->findBy($findParams);

        foreach ($notifications as $notification) {
            $notification->setViewed(true);
            $this->entityManager->persist($notification);
        }

        $this->entityManager->flush();
    }
}
