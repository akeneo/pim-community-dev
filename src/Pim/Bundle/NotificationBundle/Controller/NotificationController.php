<?php

namespace Pim\Bundle\NotificationBundle\Controller;

use Pim\Bundle\NotificationBundle\Manager\UserNotificationManager;
use Pim\Bundle\UserBundle\Context\UserContext;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Notification controller
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class NotificationController
{
    /** @var EngineInterface */
    protected $templating;

    /** @var UserNotificationManager */
    protected $manager;

    /** @var UserContext */
    protected $userContext;

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
     * List user notifications for the current user
     *
     * @param Request $request
     *
     * @return JsonResponse
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
     * Mark user notifications as viewed
     *
     * @param string|integer $id Has to be numeric or 'all'
     *
     * @return JsonResponse
     */
    public function markAsViewedAction($id)
    {
        $user = $this->userContext->getUser();

        if (null !== $user) {
            $this->manager->markAsViewed($user, $id);
        }

        return new JsonResponse();
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
        $user = $this->userContext->getUser();

        if (null !== $user) {
            $this->manager->remove($user, $id);
        }

        return new JsonResponse();
    }
}
