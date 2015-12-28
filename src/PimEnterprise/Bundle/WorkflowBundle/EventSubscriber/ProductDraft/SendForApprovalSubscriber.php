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
use PimEnterprise\Bundle\UserBundle\Entity\UserInterface;
use PimEnterprise\Bundle\WorkflowBundle\Event\ProductDraftEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * This subscriber listens to product draft submission for approval.
 * This way, we can send notifications to the right users.
 *
 * @author Adrien Pétremann <adrien.petremann@akeneo.com>
 */
class SendForApprovalSubscriber extends AbstractProposalSubscriber
{
    const NOTIFICATION_TYPE = 'pimee_workflow_product_draft_notification_new_proposal';

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

        $ownerGroupIds = $this->getOwnerGroupIds($product);

        $users         = $this->userRepository->findByGroups($ownerGroupIds);
        $usersToNotify = $this->getUsersToNotify($users);
        $author        = $this->userRepository->findOneBy(['username' => $productDraft->getAuthor()]);

        if (!empty($usersToNotify)) {
            $this->notificationManager->notify(
                $usersToNotify,
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
}
