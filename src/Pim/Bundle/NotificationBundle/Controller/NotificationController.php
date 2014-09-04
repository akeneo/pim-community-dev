<?php

namespace Pim\Bundle\NotificationBundle\Controller;

use Pim\Bundle\UserBundle\Context\UserContext;
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

    /** @var UserContext */
    private $userContext;

    /**
     * @param UserNotificationManager $manager
     * @param UserContext             $userContext
     */
    public function __construct(UserNotificationManager $manager, UserContext $userContext)
    {
        $this->manager     = $manager;
        $this->userContext = $userContext;
    }

    /**
     * It lists user notifications for a given user
     *
     * @param Request $request
     *
     * @Template
     *
     * @return array ['userNotifications' => UserNotification[]]
     */
    public function listAction(Request $request)
    {
        $user = $this->userContext->getUser();

        return ['userNotifications' => $this->manager->getUserNotifications($user, $request->get('skip', 0))];
    }

    /**
     * It marks given user notifications as viewed
     *
     * @param string|integer $id Has to be numeric or 'all'
     *
     * @return Response
     */
    public function markAsViewedAction($id)
    {
        $user = $this->userContext->getUser();
        $this->manager->markAsViewed($user, $id);

        return new Response();
    }
}
