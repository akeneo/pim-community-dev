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
use Akeneo\Pim\WorkOrganization\Workflow\Component\Event\EntityWithValuesDraftEvents;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\ProductModelDraft;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Send a notification to the reviewer when a proposal is removed
 *
 * @author Adrien Pétremann <adrien.petremann@akeneo.com>
 */
class RemoveNotificationSubscriber extends AbstractProposalStateNotificationSubscriber implements EventSubscriberInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            EntityWithValuesDraftEvents::POST_REMOVE => ['sendNotificationForRemoval', 10],
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
        $entityDraft = $event->getSubject();
        $user = $this->userContext->getUser();

        if (null === $user || !$this->authorWantToBeNotified($entityDraft)) {
            return;
        }

        $message = isset($messageInfos['message'])
            ? $messageInfos['message']
            : 'pimee_workflow.product_draft.notification.remove';

        $notification = $this->notificationFactory->create();
        $notification
            ->setType('error')
            ->setMessage($message)
            ->setRoute($entityDraft instanceof ProductModelDraft ? 'pim_enrich_product_model_edit' : 'pim_enrich_product_edit')
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

        $this->notifier->notify($notification, [$entityDraft->getAuthor()]);
    }
}
