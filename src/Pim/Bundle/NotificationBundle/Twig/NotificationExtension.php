<?php

namespace Pim\Bundle\NotificationBundle\Twig;

use Pim\Bundle\NotificationBundle\Manager\NotificationManagerInterface;
use Pim\Bundle\UserBundle\Context\UserContext;

/**
 * Twig extension to provide the number of unread user notifications
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class NotificationExtension extends \Twig_Extension
{
    /** @var NotificationManagerInterface */
    protected $manager;

    /** @var UserContext */
    protected $userContext;

    /**
     * Constructor
     *
     * @param NotificationManagerInterface $manager
     * @param UserContext                  $userContext
     */
    public function __construct(NotificationManagerInterface $manager, UserContext $userContext)
    {
        $this->manager     = $manager;
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
     * @return int
     */
    public function countNotifications()
    {
        $user = $this->userContext->getUser();

        if (null === $user) {
            return 0;
        }

        return $this->manager->countUnreadForUser($user);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pim_notification_extension';
    }
}
