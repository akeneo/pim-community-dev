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
use Pim\Bundle\UserBundle\Context\UserContext;
use PimEnterprise\Bundle\WorkflowBundle\Event\ProductDraftEvents;
use PimEnterprise\Bundle\WorkflowBundle\Model\ProductDraftInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Send a notification to the reviewer when a proposal is approved
 *
 * @author Clement Gautier <clement.gautier@akeneo.com>
 */
class ApproveNotificationSubscriber implements EventSubscriberInterface
{
    /** @var NotificationManager */
    protected $notifier;

    /** @var UserContext */
    protected $user;

    /**
     * @param NotificationManager $notifier
     * @param UserContext         $userContext
     */
    public function __construct(NotificationManager $notifier, UserContext $userContext)
    {
        $this->notifier    = $notifier;
        $this->userContext = $userContext;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            ProductDraftEvents::POST_APPROVE => ['send', 10],
        ];
    }

    /**
     * @param GenericEvent $event
     */
    public function send(GenericEvent $event)
    {
        $productDraft = $event->getSubject();

        if (!is_object($productDraft) || !$productDraft instanceof ProductDraftInterface) {
            return;
        }

        $user = $this->userContext->getUser();

        if (null === $user) {
            return;
        }

        $options = [
            'route'         => 'pim_enrich_product_edit',
            'routeParams'   => ['id' => $productDraft->getProduct()->getId()],
            'messageParams' => [
                '%product%' => $productDraft->getProduct()->getIdentifier()->getData(),
                '%owner%'   => sprintf('%s %s', $user->getFirstName(), $user->getLastName()),
            ],
            'context'       => [
                'actionType'       => 'pimee_workflow_product_draft_notification_approve',
                'showReportButton' => false,
            ]
        ];

        if ($event->hasArgument('comment')) {
            $options['comment'] = $event->getArgument('comment');
        }

        $this->notifier->notify(
            [$productDraft->getAuthor()],
            'pimee_workflow.product_draft.notification.approve',
            'success',
            $options
        );
    }
}
