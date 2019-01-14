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

namespace Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Persistence\Cursor;

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

    /** @var string */
    private $searchAfter;

    /** @var ProductSubscription[] */
    private $pendingSubscriptions;

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
        if (null === $this->pendingSubscriptions) {
            $this->rewind();
        }

        return current($this->pendingSubscriptions);
    }

    /**
     * Move forward to next element.
     *
     * @see https://php.net/manual/en/iterator.next.php
     * @since 5.0.0
     */
    public function next(): void
    {
        if (false === next($this->pendingSubscriptions)) {
            $this->pendingSubscriptions = $this->subscriptionRepository->findPendingSubscriptions(
                $this->limit,
                end($this->pendingSubscriptions)->getSubscriptionId()
            );
            reset($this->pendingSubscriptions); // TODO: Not sure it is necessary
        }
    }

    /**
     * Return the key of the current element.
     *
     * @see https://php.net/manual/en/iterator.key.php
     *
     * @return mixed scalar on success, or null on failure
     *
     * @since 5.0.0
     */
    public function key()
    {
        if (null === $this->pendingSubscriptions) {
            $this->rewind();
        }

        return key($this->pendingSubscriptions);
    }

    /**
     * Checks if current position is valid.
     *
     * @see https://php.net/manual/en/iterator.valid.php
     *
     * @return bool the return value will be casted to boolean and then evaluated.
     *              Returns true on success or false on failure
     *
     * @since 5.0.0
     */
    public function valid()
    {
        if (null === $this->pendingSubscriptions) {
            $this->rewind();
        }

        return !empty($this->pendingSubscriptions);
    }

    /**
     * Rewind the Iterator to the first element.
     *
     * @see https://php.net/manual/en/iterator.rewind.php
     * @since 5.0.0
     */
    public function rewind(): void
    {
        $this->pendingSubscriptions = $this->subscriptionRepository->findPendingSubscriptions(25, null);
        $this->searchAfter = end($this->pendingSubscriptions)->getSubscriptionId();
    }
}
