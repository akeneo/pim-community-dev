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
 * Send a notification to the reviewer when a proposal is refused
 *
 * @author Clement Gautier <clement.gautier@akeneo.com>
 */
class RefuseNotificationSubscriber extends AbstractProposalStateNotificationSubscriber
    implements EventSubscriberInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            ProductDraftEvents::POST_REFUSE         => ['sendNotificationForRefusal', 10],
            ProductDraftEvents::POST_PARTIAL_REFUSE => ['sendNotificationForPartialRefusal', 10]
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

        $event = $this->buildNotificationMessage($event, 'partial_reject');
        $this->send($event);
    }

    /**
     * @param GenericEvent $event
     */
    public function sendNotificationForRefusal(GenericEvent $event)
    {
        if (!$this->isEventValid($event)) {
            return;
        }

        $draftChanges = $event->getSubject()->getChangesToReview();
        $type = empty($draftChanges['values']) ? 'refuse' : 'partial_reject';
        $event = $this->buildNotificationMessage($event, $type);

        $this->send($event);
    }

   /**
    * {@inheritdoc}
    */
    protected function send(GenericEvent $event)
    {
        $productDraft = $event->getSubject();

        if (null === $user = $this->userContext->getUser()) {
            return;
        }

        if (!$this->authorWantToBeNotified($productDraft)) {
            return;
        }

        $message = 'pimee_workflow.product_draft.notification.refuse';

        $options = [
            'route'         => 'pim_enrich_product_edit',
            'routeParams'   => ['id' => $productDraft->getProduct()->getId()],
            'messageParams' => [
                '%product%' => $productDraft->getProduct()->getLabel($this->userContext->getCurrentLocaleCode()),
                '%owner%'   => sprintf('%s %s', $user->getFirstName(), $user->getLastName()),
            ],
            'context'       => [
                'actionType'       => 'pimee_workflow_product_draft_notification_refuse',
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

        $this->notifier->notify([$productDraft->getAuthor()], $message, 'error', $options);
    }
}
