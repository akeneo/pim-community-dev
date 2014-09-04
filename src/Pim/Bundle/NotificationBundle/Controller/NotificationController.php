<?php

namespace Pim\Bundle\NotificationBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Oro\Bundle\UserBundle\Entity\User;
use Pim\Bundle\NotificationBundle\Manager\UserNotificationManager;

/**
 * Notification controller
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class NotificationController
{
    /** @var UserNotificationManager */
    protected $manager;

    /**
     * @param UserNotificationManager $manager
     */
    public function __construct(UserNotificationManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * It lists user notifications for a given user
     *
     * @param User    $user
     * @param Request $request
     *
     * @Template
     *
     * @return array ['notification' => UserNotification[]]
     */
    public function listAction(User $user, Request $request)
    {
        return ['notifications' => $this->manager->getUserNotifications($user, $request->get('skip', 0))];
    }

    /**
     * It marks given user notifications as viewed
     *
     * @param string $userId User id
     * @param string $ids    Has to be numeric or 'all'
     *
     * @return Response
     */
    public function markAsViewedAction($userId, $ids)
    {
        $this->manager->markAsViewed($userId, $ids);

        return new Response();
    }
}
