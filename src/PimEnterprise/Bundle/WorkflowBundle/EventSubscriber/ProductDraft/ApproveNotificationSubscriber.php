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

use PimEnterprise\Bundle\WorkflowBundle\Event\ProductDraftEvents;
use PimEnterprise\Bundle\WorkflowBundle\Model\ProductDraftInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Send a notification to the reviewer when a proposal is approved
 *
 * @author Clement Gautier <clement.gautier@akeneo.com>
 */
class ApproveNotificationSubscriber extends AbstractProposalStateNotificationSubscriber
    implements EventSubscriberInterface
{
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
     * {@inheritdoc}
     */
    public function send(GenericEvent $event)
    {
        $productDraft = $event->getSubject();

        if (!is_object($productDraft) ||
            !$productDraft instanceof ProductDraftInterface ||
            !$this->authorWantToBeNotified($productDraft)
        ) {
            return;
        }

        $user = $this->userContext->getUser();

        if (null === $user) {
            return;
        }

        $message = 'pimee_workflow.product_draft.notification.approve';

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

        if ($event->hasArgument('message')) {
            $message = $event->getArgument('message');
        }

        if ($event->hasArgument('messageParams')) {
            $options['messageParams'] = array_merge($options['messageParams'], $event->getArgument('messageParams'));
        }

        if ($event->hasArgument('actionType')) {
            $options['context']['actionType'] = $event->getArgument('actionType');
        }

        $this->notifier->notify([$productDraft->getAuthor()], $message, 'success', $options);
    }
}
