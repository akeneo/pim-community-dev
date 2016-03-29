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

use PimEnterprise\Component\Workflow\Event\ProductDraftEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Send a notification to the reviewer when a proposal is removed
 *
 * @author Adrien Pétremann <adrien.petremann@akeneo.com>
 */
class RemoveNotificationSubscriber extends AbstractProposalStateNotificationSubscriber
    implements EventSubscriberInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            ProductDraftEvents::POST_REMOVE => ['sendNotificationForRemoval', 10],
        ];
    }

    /**
     * @param GenericEvent $event
     */
    public function sendNotificationForRemoval(GenericEvent $event)
    {
        if (!$this->isEventValid($event)) {
            return;
        }

        $type = $event->getArgument('isPartial') ? 'partial_remove' : 'remove';
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

        $message = isset($messageInfos['message'])
            ? $messageInfos['message']
            : 'pimee_workflow.product_draft.notification.remove';

        $notification = $this->notificationFactory->create();
        $notification
            ->setType('error')
            ->setMessage($message)
            ->setRoute('pim_enrich_product_edit')
            ->setRouteParams(['id' => $productDraft->getProduct()->getId()]);

        $options = [
            'messageParams' => [
                '%product%' => $productDraft->getProduct()->getLabel($this->userContext->getCurrentLocaleCode()),
                '%owner%'   => sprintf('%s %s', $user->getFirstName(), $user->getLastName()),
            ],
            'context' => [
                'actionType'       => 'pimee_workflow_product_draft_notification_remove',
                'showReportButton' => false,
            ]
        ];

        $options = array_replace_recursive($options, $messageInfos);
        $notification
            ->setMessageParams($options['messageParams'])
            ->setContext($options['context']);

        if ($event->hasArgument('comment')) {
            $notification->setComment($event->getArgument('comment'));
        }

        $this->notifier->notify($notification, [$productDraft->getAuthor()]);
    }
}
