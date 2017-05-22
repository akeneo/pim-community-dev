<?php

namespace Pim\Bundle\ApiBundle\Controller;

use Akeneo\Component\StorageUtils\Remover\RemoverInterface;
use Pim\Bundle\NotificationBundle\Entity\Repository\UserNotificationRepositoryInterface;
use Pim\Bundle\UserBundle\Context\UserContext;
use Pim\Component\Catalog\Repository\ProductRepositoryInterface;
use PimEnterprise\Bundle\WorkflowBundle\Doctrine\ORM\Repository\ProductDraftRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class NotificationController
{
    /** @var UserContext */
    protected $userContext;

    /** @var UserNotificationRepositoryInterface */
    protected $userNotifRepository;

    /** @var RemoverInterface */
    protected $userNotifRemover;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;
    /**
     * @var ProductDraftRepository
     */
    private $productDraftRepository;

    /**
     * @param UserContext                         $userContext
     * @param UserNotificationRepositoryInterface $userNotifRepository
     * @param RemoverInterface                    $userNotifRemover
     * @param ProductRepositoryInterface          $productRepository
     * @param ProductDraftRepository              $productDraftRepository
     */
    public function __construct(
        UserContext $userContext,
        UserNotificationRepositoryInterface $userNotifRepository,
        RemoverInterface $userNotifRemover,
        ProductRepositoryInterface $productRepository,
        ProductDraftRepository $productDraftRepository
    ) {
        $this->userContext = $userContext;
        $this->userNotifRepository = $userNotifRepository;
        $this->userNotifRemover = $userNotifRemover;
        $this->productRepository = $productRepository;
        $this->productDraftRepository = $productDraftRepository;
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

        $result = [];
        foreach ($notifications as $notificationEntity) {
            $notification = $notificationEntity->getNotification();

            $notifType = 'pimee_workflow.product_draft.notification';
            if (substr($notification->getMessage(), 0, strlen($notifType)) === $notifType) {
                $productId = (int) $notification->getRouteParams()['id'];
                $product = $this->productRepository->findOneBy(['id' => $productId]);
                $productIdentifier = (string) $product->getIdentifier();
                $routeParams = $notification->getRouteParams();
                $routeParams['identifier'] = $productIdentifier;

                $result[] = [
                    'message'       => $notification->getMessage(),
                    'type'          => $notification->getType(),
                    'route'         => $notification->getRoute(),
                    'routeParams'   => $routeParams,
                    'messageParams' => $notification->getMessageParams(),
                    'context'       => $notification->getContext(),
                    'comment'       => $notification->getComment(),
                ];

                $this->userNotifRemover->remove($notificationEntity);
            }
        }

        return new JsonResponse($result);
    }

    public function draftsAction(Request $request)
    {
        $drafts = $this->productDraftRepository->findAll();

        $result = [];
        foreach ($drafts as $draftEntity) {
                $result[] = [
                    'product_code' => $draftEntity->getProduct()->getIdentifier()->getData(),
                    'changes' => $draftEntity->getChanges(),
                    'author' => $draftEntity->getAuthor(),
                    'status' => $draftEntity->getStatus(),
                ];
        }

        return new JsonResponse($result);
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
