<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\WorkflowBundle\EventSubscriber\ProductDraft;

use Pim\Bundle\NotificationBundle\Manager\NotificationManager;
use Pim\Bundle\UserBundle\Entity\Repository\UserRepositoryInterface;
use PimEnterprise\Bundle\WorkflowBundle\Event\ProductDraftEvents;
use PimEnterprise\Bundle\WorkflowBundle\Provider\OwnerGroupsProvider;
use PimEnterprise\Bundle\WorkflowBundle\Provider\UsersToNotifyProvider;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * This subscriber listens to product draft submission for approval.
 * This way, we can send notifications to the right users.
 *
 * @author Adrien Pétremann <adrien.petremann@akeneo.com>
 */
class SendForApprovalSubscriber implements EventSubscriberInterface
{
    const NOTIFICATION_TYPE = 'pimee_workflow_product_draft_notification_new_proposal';

    /** @var NotificationManager */
    protected $notificationManager;

    /** @var UserRepositoryInterface */
    protected $userRepository;

    /** @var OwnerGroupsProvider */
    protected $ownerGroupsProvider;

    /** @var UsersToNotifyProvider */
    protected $usersProvider;

    /**
     * @param NotificationManager     $notificationManager
     * @param UserRepositoryInterface $userRepository
     * @param OwnerGroupsProvider     $ownerGroupsProvider
     * @param UsersToNotifyProvider   $usersProvider
     */
    public function __construct(
        NotificationManager $notificationManager,
        UserRepositoryInterface $userRepository,
        OwnerGroupsProvider $ownerGroupsProvider,
        UsersToNotifyProvider $usersProvider
    ) {
        $this->notificationManager = $notificationManager;
        $this->userRepository      = $userRepository;
        $this->ownerGroupsProvider = $ownerGroupsProvider;
        $this->usersProvider       = $usersProvider;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            ProductDraftEvents::POST_READY => ['sendNotificationToOwners'],
        ];
    }

    /**
     * Send notifications to all owners of product the draft is attached to.
     *
     * @param GenericEvent $event
     */
    public function sendNotificationToOwners(GenericEvent $event)
    {
        $productDraft  = $event->getSubject();
        $comment       = $event->getArgument('comment');
        $product       = $productDraft->getProduct();
        $author        = $this->userRepository->findOneBy(['username' => $productDraft->getAuthor()]);

        $usersToNotify = $this->usersProvider->getUsersToNotify(
            $this->ownerGroupsProvider->getOwnerGroupIds($product)
        );

        $gridParameters = [
            'f' => [
                'author' => [
                    'value' => [
                        $author->getUsername()
                    ]
                ],
                'product' => [
                    'value' => [
                        $product->getId()
                    ]
                ]
            ],
        ];

        if (!empty($usersToNotify)) {
            $this->notificationManager->notify(
                $usersToNotify,
                'pimee_workflow.proposal.to_review',
                'add',
                [
                    'route'         => 'pimee_workflow_proposal_index',
                    'comment'       => $comment,
                    'messageParams' => [
                        '%product.label%'    => $product->getLabel(),
                        '%author.firstname%' => $author->getFirstName(),
                        '%author.lastname%'  => $author->getLastName()
                    ],
                    'context'       => [
                        'actionType'       => static::NOTIFICATION_TYPE,
                        'showReportButton' => false,
                        'gridParameters'   => http_build_query($gridParameters, 'flags_')
                    ]
                ]
            );
        }
    }
}
