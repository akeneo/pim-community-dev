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

use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\EntityWithValuesDraftInterface;
use Akeneo\Platform\Bundle\NotificationBundle\NotifierInterface;
use Akeneo\Tool\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\UserManagement\Bundle\Context\UserContext;
use Akeneo\UserManagement\Component\Repository\UserRepositoryInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Send a notification to the reviewer when a proposal state changes
 *
 * @author Willy Mesnage <willy.mesnage@akeneo.com>
 */
abstract class AbstractProposalStateNotificationSubscriber
{
    const NOTIFICATION_MAX_ATTRIBUTES = 3;

    /** @var NotifierInterface */
    protected $notifier;

    /** @var UserContext */
    protected $userContext;

    /** @var UserRepositoryInterface */
    protected $userRepository;

    /** @var AttributeRepositoryInterface */
    protected $attributeRepository;

    /** @var SimpleFactoryInterface */
    protected $notificationFactory;

    /**
     * @param NotifierInterface            $notifier
     * @param UserContext                  $userContext
     * @param UserRepositoryInterface      $userRepository
     * @param AttributeRepositoryInterface $attributeRepository
     * @param SimpleFactoryInterface       $notificationFactory
     */
    public function __construct(
        NotifierInterface $notifier,
        UserContext $userContext,
        UserRepositoryInterface $userRepository,
        AttributeRepositoryInterface $attributeRepository,
        SimpleFactoryInterface $notificationFactory
    ) {
        $this->notifier = $notifier;
        $this->userContext = $userContext;
        $this->userRepository = $userRepository;
        $this->attributeRepository = $attributeRepository;
        $this->notificationFactory = $notificationFactory;
    }

    /**
     * Send a notification to the reviewer when a proposal state changes
     *
     * @param GenericEvent $event
     * @param array        $messageInfos
     */
    abstract protected function send(GenericEvent $event, array $messageInfos);

    /**
     * @param EntityWithValuesDraftInterface $productDraft
     *
     * @throws \LogicException
     *
     * @return bool
     */
    protected function authorWantToBeNotified(EntityWithValuesDraftInterface $productDraft)
    {
        $author = $this->userRepository->findOneByIdentifier($productDraft->getAuthor());
        if (null === $author) {
            // Product draft has been imported
            return false;
        }

        return $author->getProperty('proposals_state_notifications');
    }

    /**
     * @param GenericEvent $event
     *
     * @return bool
     */
    protected function isEventValid(GenericEvent $event)
    {
        $productDraft = $event->getSubject();

        if (!is_object($productDraft) || !$productDraft instanceof EntityWithValuesDraftInterface) {
            return false;
        }

        $updatedValues = $event->hasArgument('updatedValues') ? $event->getArgument('updatedValues') : [];

        return !empty($updatedValues);
    }

    /**
     * @param GenericEvent $event
     * @param string       $type
     *
     * @return array
     */
    protected function buildNotificationMessageInfos(GenericEvent $event, $type)
    {
        $updatedValues = $event->getArgument('updatedValues');
        $isPartialAction = 0 === strpos($type, 'partial');

        $messageInfos = [
            'message' => sprintf('pimee_workflow.product_draft.notification.%s', $type),
            'context' => [
                'actionType' => sprintf('pimee_workflow_product_draft_notification_%s', $type)
            ]
        ];

        if (!$isPartialAction) {
            return $messageInfos;
        }

        if (count($updatedValues) > self::NOTIFICATION_MAX_ATTRIBUTES) {
            $messageInfos['message'] = sprintf('pimee_workflow.product_draft.notification.%s_number', $type);
            $messageInfos['messageParams'] = ['%attributes_count%' => count($updatedValues)];

            return $messageInfos;
        }

        $attributeLabels = array_map(function ($attributeCode) {
            $attribute = $this->attributeRepository->findOneByIdentifier($attributeCode);
            $attribute->setLocale($this->userContext->getCurrentLocaleCode());

            return $attribute->getLabel();
        }, array_keys($updatedValues));

        $messageInfos['message'] = sprintf('pimee_workflow.product_draft.notification.%s', $type);
        $messageInfos['messageParams'] = ['%attributes%' => implode(', ', $attributeLabels)];

        return $messageInfos;
    }
}
