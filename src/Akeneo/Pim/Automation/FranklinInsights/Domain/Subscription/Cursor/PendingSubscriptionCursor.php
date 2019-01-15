<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Cursor;

use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Model\ProductSubscription;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Repository\ProductSubscriptionRepositoryInterface;

/**
 * @author Romain Monceau <romain@akeneo.com>
 */
class PendingSubscriptionCursor implements \Iterator
{
    /** @var ProductSubscriptionRepositoryInterface */
    private $subscriptionRepository;

    /** @var int */
    private $limit;

    /** @var ProductSubscription[] */
    private $pendingSubscriptions = [];

    /**
     * @param ProductSubscriptionRepositoryInterface $subscriptionRepository
     */
    public function __construct(
        ProductSubscriptionRepositoryInterface $subscriptionRepository,
        int $limit
    ) {
        $this->subscriptionRepository = $subscriptionRepository;
        $this->limit = $limit;
    }

    /**
     * {@inheritdoc}
     */
    public function current()
    {
        if (empty($this->pendingSubscriptions)) {
            $this->rewind();
        }

        return current($this->pendingSubscriptions);
    }

    /**
     * {@inheritdoc}
     */
    public function next(): void
    {
        if (false === next($this->pendingSubscriptions)) {
            $this->pendingSubscriptions = $this->subscriptionRepository->findPendingSubscriptions(
                $this->limit,
                end($this->pendingSubscriptions) ? end($this->pendingSubscriptions)->getSubscriptionId() : null
            );
            reset($this->pendingSubscriptions);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function key()
    {
        if (empty($this->pendingSubscriptions)) {
            $this->rewind();
        }

        return key($this->pendingSubscriptions);
    }

    /**
     * {@inheritdoc}
     */
    public function valid()
    {
        if (null === $this->pendingSubscriptions) {
            $this->rewind();
        }

        return !empty($this->pendingSubscriptions);
    }

    /**
     * {@inheritdoc}
     */
    public function rewind(): void
    {
        $this->pendingSubscriptions = $this->subscriptionRepository->findPendingSubscriptions($this->limit, null);
    }
}
