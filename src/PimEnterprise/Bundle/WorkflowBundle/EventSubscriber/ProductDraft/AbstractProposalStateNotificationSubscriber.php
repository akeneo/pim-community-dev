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
use PimEnterprise\Bundle\WorkflowBundle\Model\ProductDraftInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Send a notification to the reviewer when a proposal state changes
 *
 * @author Willy Mesnage <willy.mesnage@akeneo.com>
 */
abstract class AbstractProposalStateNotificationSubscriber
{
    /** @var NotificationManager */
    protected $notifier;

    /** @var UserContext */
    protected $userContext;

    /** @var UserRepositoryInterface */
    protected $userRepository;

    /**
     * @param NotificationManager     $notifier
     * @param UserContext             $userContext
     * @param UserRepositoryInterface $userRepository
     */
    public function __construct(
        NotificationManager $notifier,
        UserContext $userContext,
        UserRepositoryInterface $userRepository
    ) {
        $this->notifier       = $notifier;
        $this->userContext    = $userContext;
        $this->userRepository = $userRepository;
    }

    /**
     * Send a notification to the reviewer when a proposal state changes
     *
     * @param GenericEvent $event
     */
    abstract public function send(GenericEvent $event);

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
}
