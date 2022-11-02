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

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Event\EntityWithValuesDraftEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Send a notification to the reviewer when a proposal is refused
 *
 * @author Clement Gautier <clement.gautier@akeneo.com>
 */
class RefuseNotificationSubscriber extends AbstractProposalStateNotificationSubscriber implements EventSubscriberInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            EntityWithValuesDraftEvents::POST_REFUSE         => ['sendNotificationForRefusal', 10],
            EntityWithValuesDraftEvents::POST_PARTIAL_REFUSE => ['sendNotificationForPartialRefusal', 10]
        ];
    }

    /**
     * @param GenericEvent $event
     */
    public function sendNotificationForPartialRefusal(GenericEvent $event)
    {
        if (!$this->isEventValid($event)) {
            return;
        }

        $messageInfos = $this->buildNotificationMessageInfos($event, 'partial_reject');
        $this->send($event, $messageInfos);
    }

    /**
     * @param GenericEvent $event
     */
    public function sendNotificationForRefusal(GenericEvent $event)
    {
        if (!$this->isEventValid($event)) {
            return;
        }

        $type = $event->getArgument('isPartial') ? 'partial_reject' : 'refuse';
        $messageInfos = $this->buildNotificationMessageInfos($event, $type);

        $this->send($event, $messageInfos);
    }

    /**
     * {@inheritdoc}
     */
    protected function send(GenericEvent $event, array $messageInfos)
    {
        $entityDraft = $event->getSubject();
        $user = $this->userContext->getUser();

        if (null === $user || !$this->authorWantToBeNotified($entityDraft)) {
            return;
        }

        $message = $messageInfos['message'] ?? 'pimee_workflow.product_draft.notification.refuse';
        $route = $entityDraft->getEntityWithValue() instanceof ProductModelInterface ?
            'pim_enrich_product_model_edit' :
            'pim_enrich_product_edit';

        $notification = $this->notificationFactory->create();
        $notification
            ->setType('error')
            ->setMessage($message)
            ->setRoute($route)
            ->setRouteParams(
                $entityDraft->getEntityWithValue() instanceof ProductInterface
                ? ['uuid' => $entityDraft->getEntityWithValue()->getUuid()->toString()]
                : ['id' => $entityDraft->getEntityWithValue()->getId()]
            );

        $options = [
            'messageParams' => [
                '%product%' => $entityDraft->getEntityWithValue()->getLabel($this->userContext->getCurrentLocaleCode()),
                '%owner%'   => sprintf('%s %s', $user->getFirstName(), $user->getLastName()),
            ],
            'context'       => [
                'actionType'       => 'pimee_workflow_product_draft_notification_refuse',
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

        $this->notifier->notify($notification, [$entityDraft->getAuthor()]);
    }
}
