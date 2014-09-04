<?php

namespace Pim\Bundle\UIBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Response;
use Oro\Bundle\UserBundle\Entity\User;
use Pim\Bundle\UIBundle\Manager\NotificationManager;

/**
 * Notification controller
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class NotificationController
{
    /** @var NotificationManager */
    protected $manager;

    /**
     * @param NotificationManager $manager
     */
    public function __construct(NotificationManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * It lists notifications for a given user
     *
     * @param User $user
     *
     * @Template
     *
     * @return array ['notification' => Notification[]]
     */
    public function listAction(User $user)
    {
        return ['notifications' => $this->manager->getNotifications($user)];
    }

    /**
     * It marks given notifications as viewed for a user
     *
     * @param string $userId User id
     * @param string $ids    Has to be numeric or 'all'
     *
     * @return Response
     */
    public function markAsViewedAction($userId, $ids)
    {
        $this->manager->markNotificationsAsViewed($userId, $ids);

        return new Response();
    }
}
