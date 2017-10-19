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

use Akeneo\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Pim\Bundle\NotificationBundle\NotifierInterface;
use Pim\Bundle\UserBundle\Repository\UserRepositoryInterface;
use PimEnterprise\Bundle\WorkflowBundle\Provider\OwnerGroupsProvider;
use PimEnterprise\Bundle\WorkflowBundle\Provider\UsersToNotifyProvider;
use PimEnterprise\Component\Workflow\Event\ProductDraftEvents;
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

    /** @var NotifierInterface */
    protected $notifier;

    /** @var UserRepositoryInterface */
    protected $userRepository;

    /** @var OwnerGroupsProvider */
    protected $ownerGroupsProvider;

    /** @var UsersToNotifyProvider */
    protected $usersProvider;

    /** @var SimpleFactoryInterface */
    protected $notificationFactory;

    /**
     * @param NotifierInterface       $notifier
     * @param UserRepositoryInterface $userRepository
     * @param OwnerGroupsProvider     $ownerGroupsProvider
     * @param UsersToNotifyProvider   $usersProvider
     * @param SimpleFactoryInterface  $notificationFactory
     */
    public function __construct(
        NotifierInterface $notifier,
        UserRepositoryInterface $userRepository,
        OwnerGroupsProvider $ownerGroupsProvider,
        UsersToNotifyProvider $usersProvider,
        SimpleFactoryInterface $notificationFactory
    ) {
        $this->notifier = $notifier;
        $this->userRepository = $userRepository;
        $this->ownerGroupsProvider = $ownerGroupsProvider;
        $this->usersProvider = $usersProvider;
        $this->notificationFactory = $notificationFactory;
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
        $productDraft = $event->getSubject();
        $product = $productDraft->getProduct();

        $groupsToNotify = $this->ownerGroupsProvider->getOwnerGroupIds($product);
        if (empty($groupsToNotify)) {
            return;
        }

        $usersToNotify = $this->usersProvider->getUsersToNotify($groupsToNotify);
        if (empty($usersToNotify)) {
            return;
        }

        $author = $this->userRepository->findOneBy(['username' => $productDraft->getAuthor()]);
        $authorCatalogLocale = $author->getCatalogLocale()->getCode();

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

        $notification = $this->notificationFactory->create();
        $notification
            ->setMessage('pimee_workflow.proposal.to_review')
            ->setMessageParams(
                [
                    '%product.label%'    => $product->getLabel($authorCatalogLocale),
                    '%author.firstname%' => $author->getFirstName(),
                    '%author.lastname%'  => $author->getLastName()
                ]
            )
            ->setType('add')
            ->setRoute('pimee_workflow_proposal_index')
            ->setComment($event->getArgument('comment'))
            ->setContext(
                [
                    'actionType'       => static::NOTIFICATION_TYPE,
                    'showReportButton' => false,
                    'gridParameters'   => http_build_query($gridParameters, 'flags_')
                ]
            );

        $this->notifier->notify($notification, $usersToNotify);
    }
}
