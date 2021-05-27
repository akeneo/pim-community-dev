<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\WorkOrganization\Workflow\Bundle\EventSubscriber\ProductDraft;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Event\EntityWithValuesDraftEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Send a notification to the reviewer when a proposal is approved
 *
 * @author Clement Gautier <clement.gautier@akeneo.com>
 */
class ApproveNotificationSubscriber extends AbstractProposalStateNotificationSubscriber implements EventSubscriberInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            EntityWithValuesDraftEvents::POST_APPROVE         => ['sendNotificationForApproval', 10],
            EntityWithValuesDraftEvents::POST_PARTIAL_APPROVE => ['sendNotificationForPartialApproval', 10]
        ];
    }

    /**
     * @param GenericEvent $event
     */
    public function sendNotificationForPartialApproval(GenericEvent $event)
    {
        if (!$this->isEventValid($event)) {
            return;
        }

        $messageInfos = $this->buildNotificationMessageInfos($event, 'partial_approve');
        $this->send($event, $messageInfos);
    }

    /**
     * @param GenericEvent $event
     */
    public function sendNotificationForApproval(GenericEvent $event)
    {
        if (!$this->isEventValid($event)) {
            return;
        }

        $type = $event->getArgument('isPartial') ? 'partial_approve' : 'approve';
        $messageInfos = $this->buildNotificationMessageInfos($event, $type);

        $this->send($event, $messageInfos);
    }

    /**
     * {@inheritdoc}
     */
    protected function send(GenericEvent $event, array $messageInfos)
    {
        $productDraft = $event->getSubject();
        $user = $this->userContext->getUser();

        if (null === $user || !$this->authorWantToBeNotified($productDraft)) {
            return;
        }

        $message = $messageInfos['message'] ?? 'pimee_workflow.product_draft.notification.approve';
        $route = $productDraft->getEntityWithValue() instanceof ProductModelInterface ?
            'pim_enrich_product_model_edit' :
            'pim_enrich_product_edit';

        $notification = $this->notificationFactory->create();
        $notification
            ->setType('success')
            ->setMessage($message)
            ->setRoute($route)
            ->setRouteParams(['id' => $productDraft->getEntityWithValue()->getId()]);

        $options = [
            'messageParams' => [
                '%product%' => $productDraft->getEntityWithValue()->getLabel($this->userContext->getCurrentLocaleCode()),
                '%owner%'   => sprintf('%s %s', $user->getFirstName(), $user->getLastName()),
            ],
            'context'       => [
                'actionType'       => 'pimee_workflow_product_draft_notification_approve',
                'showReportButton' => false,
            ]
        ];

        $options = array_replace_recursive($options, $messageInfos);

        if ($event->hasArgument('comment')) {
            $notification->setComment($event->getArgument('comment'));
        }

        if ($event->hasArgument('message')) {
            $notification->setMessage($event->getArgument('message'));
        }

        if ($event->hasArgument('messageParams')) {
            $options['messageParams'] = array_merge($options['messageParams'], $event->getArgument('messageParams'));
        }

        if ($event->hasArgument('actionType')) {
            $options['context']['actionType'] = $event->getArgument('actionType');
        }

        $notification
            ->setMessageParams($options['messageParams'])
            ->setContext($options['context']);

        $this->notifier->notify($notification, [$productDraft->getAuthor()]);
    }
}
