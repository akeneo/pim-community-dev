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
use PimEnterprise\Bundle\SecurityBundle\Attributes;
use PimEnterprise\Bundle\SecurityBundle\Entity\Repository\CategoryAccessRepository;
use PimEnterprise\Bundle\WorkflowBundle\Event\ProductDraftEvents;
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

    /** @var CategoryAccessRepository */
    protected $categoryAccessRepo;

    /** @var UserRepositoryInterface */
    protected $userRepository;

    /**
     * @param NotificationManager      $notificationManager
     * @param CategoryAccessRepository $categoryAccessRepo
     * @param UserRepositoryInterface  $userRepository
     */
    public function __construct(
        NotificationManager $notificationManager,
        CategoryAccessRepository $categoryAccessRepo,
        UserRepositoryInterface $userRepository
    ) {
        $this->notificationManager = $notificationManager;
        $this->categoryAccessRepo  = $categoryAccessRepo;
        $this->userRepository      = $userRepository;
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
        $comment      = $event->getArgument('comment');
        $product      = $productDraft->getProduct();

        $ownerGroupsId = [];
        $ownerGroups   = $this->categoryAccessRepo->getGrantedUserGroupsForProduct($product, Attributes::OWN_PRODUCTS);
        foreach ($ownerGroups as $userGroup) {
            $ownerGroupsId[] = $userGroup['id'];
        }

        $users  = $this->userRepository->findByGroups($ownerGroupsId);
        $author = $this->userRepository->findOneBy(['username' => $productDraft->getAuthor()]);

        $this->notificationManager->notify(
            $users,
            'pimee_workflow.proposal.to_review',
            'add',
            [
                'route'         => 'pim_enrich_product_edit',
                'routeParams'   => [
                    'id'          => $product->getId(),
                    'redirectTab' => 'pim-product-edit-form-proposals'
                ],
                'comment'       => $comment,
                'messageParams' => [
                    '%product.label%'    => $product->getLabel(),
                    '%author.firstname%' => $author->getFirstName(),
                    '%author.lastname%'  => $author->getLastName()
                ],
                'context'       => [
                    'actionType'       => static::NOTIFICATION_TYPE,
                    'showReportButton' => false
                ]
            ]
        );
    }
}
