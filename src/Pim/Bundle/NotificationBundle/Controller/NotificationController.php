<?php

namespace Pim\Bundle\NotificationBundle\Controller;

use Oro\Bundle\UserBundle\Entity\User;
use Pim\Bundle\NotificationBundle\Manager\UserNotificationManager;
use Pim\Bundle\UserBundle\Context\UserContext;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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
     * @param EngineInterface         $templating
     * @param UserNotificationManager $manager
     * @param UserContext             $userContext
     */
    public function __construct(
        EngineInterface $templating,
        UserNotificationManager $manager,
        UserContext $userContext
    ) {
        $this->templating  = $templating;
        $this->manager     = $manager;
        $this->userContext = $userContext;
    }

    /**
     * It lists user notifications for the current user
     *
     * @param Request $request
     *
     * @return array ['userNotifications' => UserNotification[]]
     */
    public function listAction(Request $request)
    {
        $user = $this->userContext->getUser();

        return $this->templating->renderResponse(
            'PimNotificationBundle:Notification:list.json.twig',
            [
                'userNotifications'  => $this->manager->getUserNotifications($user, $request->get('skip', 0))
            ],
            new JsonResponse()
        );
    }

    /**
     * It marks current user notifications as viewed
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

    /**
     * It counts unread notifications for the current user
     *
     * @return JsonResponse
     */
    public function countUnreadAction()
    {
        $user = $this->userContext->getUser();

        return new JsonResponse($this->manager->countUnreadForUser($user));
    }

    /**
     * Remove a notification
     *
     * @param integer $id
     *
     * @return JsonResponse
     */
    public function removeAction($id)
    {
        $userId = $this->userContext->getUser()->getId();

        $this->manager->remove($userId, $id);

        return new JsonResponse();
    }
}
