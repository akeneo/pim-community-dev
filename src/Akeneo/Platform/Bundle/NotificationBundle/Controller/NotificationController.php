<?php

namespace Akeneo\Platform\Bundle\NotificationBundle\Controller;

use Akeneo\Platform\Bundle\NotificationBundle\Entity\Repository\UserNotificationRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Remover\RemoverInterface;
use Akeneo\UserManagement\Bundle\Context\UserContext;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

/**
 * Notification controller
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class NotificationController
{
    protected Environment $templating;
    protected UserContext $userContext;
    protected UserNotificationRepositoryInterface $userNotifRepository;
    protected RemoverInterface $userNotifRemover;

    public function __construct(
        Environment $templating,
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
     */
    public function listAction(Request $request): JsonResponse
    {
        $user = $this->userContext->getUser();
        $notifications = $this->userNotifRepository
            ->findBy(['user' => $user], ['id' => 'DESC'], 10, $request->get('skip', 0));

        return (new JsonResponse())->setContent(
            $this->templating->render(
                'PimNotificationBundle:Notification:list.json.twig',
                [
                    'userNotifications' => $notifications,
                    'userTimezone' => $this->userContext->getUserTimezone(),
                ]
            )
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
     * @param Request $request
     * @param int $id
     *
     * @return Response
     */
    public function removeAction(Request $request, $id)
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

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
