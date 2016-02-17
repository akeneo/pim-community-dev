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
use Pim\Bundle\UserBundle\Entity\Repository\UserRepositoryInterface;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
use PimEnterprise\Bundle\WorkflowBundle\Model\ProductDraftInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Send a notification to the reviewer when a proposal state changes
 *
 * @author Willy Mesnage <willy.mesnage@akeneo.com>
 */
abstract class AbstractProposalStateNotificationSubscriber
{
    const NOTIFICATION_MAX_ATTRIBUTES = 3;

    /** @var NotificationManager */
    protected $notifier;

    /** @var UserContext */
    protected $userContext;

    /** @var UserRepositoryInterface */
    protected $userRepository;

    /** @var AttributeRepositoryInterface */
    protected $attributeRepository;

    /**
     * @param NotificationManager          $notifier
     * @param UserContext                  $userContext
     * @param UserRepositoryInterface      $userRepository
     * @param AttributeRepositoryInterface $attributeRepository
     */
    public function __construct(
        NotificationManager $notifier,
        UserContext $userContext,
        UserRepositoryInterface $userRepository,
        AttributeRepositoryInterface $attributeRepository
    ) {
        $this->notifier            = $notifier;
        $this->userContext         = $userContext;
        $this->userRepository      = $userRepository;
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * Send a notification to the reviewer when a proposal state changes
     *
     * @param GenericEvent $event
     */
    abstract protected function send(GenericEvent $event);

    /**
     * @param ProductDraftInterface $productDraft
     *
     * @throws \LogicException
     *
     * @return bool
     */
    protected function authorWantToBeNotified(ProductDraftInterface $productDraft)
    {
        $author = $this->userRepository->findOneByIdentifier($productDraft->getAuthor());
        if (null === $author) {
            // Product draft has been imported
            return false;
        }

        return $author->hasProposalsStateNotification();
    }

    /**
     * @param GenericEvent $event
     *
     * @return bool
     */
    protected function isEventValid(GenericEvent $event)
    {
        $productDraft = $event->getSubject();

        if (!is_object($productDraft) || !$productDraft instanceof ProductDraftInterface) {
            return false;
        }

        $updatedValues = $event->hasArgument('updatedValues') ? $event->getArgument('updatedValues') : [];

        return !empty($updatedValues);
    }

    /**
     * @param GenericEvent $event
     * @param string       $type
     *
     * @return GenericEvent
     */
    protected function buildNotificationMessage(GenericEvent $event, $type)
    {
        $updatedValues = $event->getArgument('updatedValues');

        $event->setArgument('actionType', sprintf('pimee_workflow_product_draft_notification_%s', $type));

        if (count($updatedValues) > self::NOTIFICATION_MAX_ATTRIBUTES) {
            $event->setArgument('message', sprintf('pimee_workflow.product_draft.notification.%s_number', $type));
            $event->setArgument('messageParams', ['%attributes_count%' => count($updatedValues)]);
        } else {
            $attributeLabels = array_map(function ($attributeCode) {
                $attribute = $this->attributeRepository->findOneByIdentifier($attributeCode);
                $attribute->setLocale($this->userContext->getCurrentLocaleCode());

                return $attribute->getLabel();
            }, array_keys($updatedValues));

            $event->setArgument('message', sprintf('pimee_workflow.product_draft.notification.%s', $type));
            $event->setArgument('messageParams', ['%attributes%' => implode(', ', $attributeLabels)]);
        }

        return $event;
    }
}
