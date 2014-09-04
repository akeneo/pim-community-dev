<?php

namespace Pim\Bundle\NotificationBundle\Twig;

use Pim\Bundle\UserBundle\Context\UserContext;
use Pim\Bundle\NotificationBundle\Entity\Repository\UserNotificationRepository;

/**
 * Twig extension to provide the number of unread notifications
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class NotificationExtension extends \Twig_Extension
{
    /** @var UserNotificationRepository */
    protected $repository;

    /** @var UserContext */
    protected $userContext;

    /**
     * Constructor
     *
     * @param UserNotificationRepository $repository
     * @param UserContext                $userContext
     */
    public function __construct(UserNotificationRepository $repository, UserContext $userContext)
    {
        $this->repository  = $repository;
        $this->userContext = $userContext;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('notification_count', [$this, 'countNotifications'])
        ];
    }

    /**
     * Return the number of unread notifications for the currently logged in user
     *
     * @return integer
     */
    public function countNotifications()
    {
        $user = $this->userContext->getUser();

        if (null === $user) {
            return 0;
        }

        return $this->repository->countUnreadForUser($user);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pim_notification_extension';
    }
}
