<?php

namespace Pim\Bundle\NotificationBundle\Controller;

use Akeneo\Component\StorageUtils\Remover\RemoverInterface;
use Pim\Bundle\NotificationBundle\Entity\Repository\UserNotificationRepositoryInterface;
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

    /** @var UserContext */
    protected $userContext;

    /** @var UserNotificationRepositoryInterface */
    protected $userNotifRepository;

    /** @var RemoverInterface */
    protected $userNotifRemover;

    /**
     * @param EngineInterface                     $templating
     * @param UserContext                         $userContext
     * @param UserNotificationRepositoryInterface $userNotifRepository
     * @param RemoverInterface                    $userNotifRemover
     */
    public function __construct(
        EngineInterface $templating,
        UserContext $userContext,
        UserNotificationRepositoryInterface $userNotifRepository,
        RemoverInterface $userNotifRemover
    ) {
        $this->templating = $templating;
        $this->userContext = $userContext;
        $this->userNotifRepository = $userNotifRepository;
        $this->userNotifRemover = $userNotifRemover;
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
        $notifications = $this->userNotifRepository
            ->findBy(['user' => $user], ['id' => 'DESC'], 10, $request->get('skip', 0));

        return $this->templating->renderResponse(
            'PimNotificationBundle:Notification:list.json.twig',
            [
                'userNotifications' => $notifications
            ],
            new JsonResponse()
        );
    }

    /**
     * Return the number of unread notifications for the current user
     *
     * @return JsonResponse
     */
    public function countUnreadAction()
    {
        $user = $this->userContext->getUser();

        return new JsonResponse($this->userNotifRepository->countUnreadForUser($user));
    }

    /**
     * Mark user notifications as viewed
     *
     * @param int|null $id If null, all notifications will be marked as viewed
     *
     * @return JsonResponse
     */
    public function markAsViewedAction($id)
    {
        $user = $this->userContext->getUser();

        if (null !== $user) {
            $this->userNotifRepository->markAsViewed($user, $id);
        }

        return new JsonResponse();
    }

    /**
     * Remove a notification
     *
     * @param int $id
     *
     * @return JsonResponse
     */
    public function removeAction($id)
    {
        $user = $this->userContext->getUser();

        if (null !== $user) {
            $notification = $this->userNotifRepository->findOneBy(
                [
                    'id'   => $id,
                    'user' => $user
                ]
            );

            if ($notification) {
                $this->userNotifRemover->remove($notification);
            }
        }

        return new JsonResponse();
    }
}
